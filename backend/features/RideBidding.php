<?php
/**
 * FEATURE 1: RIDE BIDDING AUCTION (InDrive-style)
 * ------------------------------------------------
 * Price negotiation between driver and passenger.
 * Supports multiple counter-offer rounds, expiry timers,
 * auto-rejection after max rounds, and real-time push notifications.
 *
 * Usage (called from webservice_shark.php or include_webservice_shark.php):
 *   require_once 'features/RideBidding.php';
 *   $bidding = new RideBidding($obj, $tconfig);
 *   echo $bidding->handleRequest($_REQUEST);
 */

class RideBidding
{
    private $db;
    private $config;
    private int $defaultMaxRounds   = 3;
    private int $defaultExpiryMins  = 5; // each offer expires in 5 minutes

    public function __construct($db, array $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }

    // ----------------------------------------------------------------
    // Main dispatcher – maps 'type' param to handler method
    // ----------------------------------------------------------------
    public function handleRequest(array $req): string
    {
        $action = trim($req['bid_action'] ?? '');

        switch ($action) {
            case 'passengerInitiate':  return $this->passengerInitiateBid($req);
            case 'driverCounter':      return $this->driverCounter($req);
            case 'passengerCounter':   return $this->passengerCounter($req);
            case 'acceptBid':          return $this->acceptBid($req);
            case 'rejectBid':          return $this->rejectBid($req);
            case 'getBidStatus':       return $this->getBidStatus($req);
            case 'getActiveBids':      return $this->getActiveBids($req);
            default:
                return $this->error('Unknown bid_action: ' . htmlspecialchars($action));
        }
    }

    // ----------------------------------------------------------------
    // 1. Passenger starts the auction with an initial offer price
    // ----------------------------------------------------------------
    private function passengerInitiateBid(array $req): string
    {
        $requestId = (int)($req['iRequestId']     ?? 0);
        $userId    = (int)($req['iUserId']         ?? 0);
        $driverId  = (int)($req['iDriverId']       ?? 0);
        $offer     = (float)($req['fPassengerOffer'] ?? 0);

        if (!$requestId || !$userId || !$offer) {
            return $this->error('iRequestId, iUserId and fPassengerOffer are required.');
        }

        // Prevent duplicate active negotiations
        $existing = $this->db->MySQLSelect(
            "SELECT iNegotiationId FROM ride_bid_negotiations
             WHERE iRequestId = $requestId AND eStatus IN ('Pending','DriverCountered','PassengerCountered')
             LIMIT 1"
        );
        if (!empty($existing)) {
            return $this->error('An active negotiation already exists for this request.');
        }

        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->defaultExpiryMins} minutes"));
        $now       = date('Y-m-d H:i:s');

        // Create negotiation record
        $this->db->sql_query(
            "INSERT INTO ride_bid_negotiations
                (iRequestId, iDriverId, iUserId, fPassengerOffer, fDriverCounter, eStatus, iMaxRounds, dExpiresAt, dCreatedAt)
             VALUES
                ($requestId, $driverId, $userId, $offer, 0, 'Pending', {$this->defaultMaxRounds}, '$expiresAt', '$now')"
        );
        $negotiationId = $this->db->MySQLLastInsertID();

        // Log round
        $this->logRound($negotiationId, 'Passenger', $offer, 'Initial offer');

        // Update driver_request bid mode
        $this->db->sql_query(
            "UPDATE driver_request
             SET eBidMode='Auction', fPassengerBidOffer=$offer, fTaxiBidAmount=$offer
             WHERE iRequestId=$requestId"
        );

        // Notify driver via push
        $this->pushNotifyDriver($driverId, [
            'title' => 'New Price Offer!',
            'body'  => "Passenger offered R$ " . number_format($offer, 2, ',', '.') . " for the ride.",
            'data'  => ['type' => 'BidOffer', 'negotiationId' => $negotiationId, 'requestId' => $requestId],
        ]);

        return $this->success([
            'negotiationId' => $negotiationId,
            'expiresAt'     => $expiresAt,
            'message'       => 'Bid initiated successfully.',
        ]);
    }

    // ----------------------------------------------------------------
    // 2. Driver counters with their own price
    // ----------------------------------------------------------------
    private function driverCounter(array $req): string
    {
        $negotiationId = (int)($req['iNegotiationId'] ?? 0);
        $driverId      = (int)($req['iDriverId']       ?? 0);
        $counter       = (float)($req['fDriverCounter'] ?? 0);

        if (!$negotiationId || !$driverId || !$counter) {
            return $this->error('iNegotiationId, iDriverId and fDriverCounter are required.');
        }

        $neg = $this->fetchNegotiation($negotiationId);
        if (!$neg) return $this->error('Negotiation not found.');
        if ($neg['iDriverId'] != $driverId) return $this->error('Not authorised for this negotiation.');
        if (!in_array($neg['eStatus'], ['Pending', 'PassengerCountered'])) {
            return $this->error("Cannot counter in status: {$neg['eStatus']}");
        }
        if ($this->isExpired($neg['dExpiresAt'])) {
            $this->expireNegotiation($negotiationId);
            return $this->error('Offer expired. Please start a new bid.');
        }
        if ($neg['iRoundCount'] >= $neg['iMaxRounds']) {
            $this->db->sql_query("UPDATE ride_bid_negotiations SET eStatus='Rejected' WHERE iNegotiationId=$negotiationId");
            return $this->error('Maximum negotiation rounds reached. Bid rejected.');
        }

        $newExpiry   = date('Y-m-d H:i:s', strtotime("+{$this->defaultExpiryMins} minutes"));
        $newRound    = $neg['iRoundCount'] + 1;

        $this->db->sql_query(
            "UPDATE ride_bid_negotiations
             SET fDriverCounter=$counter, eStatus='DriverCountered', iRoundCount=$newRound, dExpiresAt='$newExpiry'
             WHERE iNegotiationId=$negotiationId"
        );
        $this->logRound($negotiationId, 'Driver', $counter, 'Driver counter-offer');

        // Notify passenger
        $this->pushNotifyPassenger($neg['iUserId'], [
            'title' => 'Driver made a counter-offer!',
            'body'  => "Driver offered R$ " . number_format($counter, 2, ',', '.') . ". Accept or counter.",
            'data'  => ['type' => 'BidCounter', 'negotiationId' => $negotiationId],
        ]);

        return $this->success(['message' => 'Counter-offer sent.', 'newExpiry' => $newExpiry, 'round' => $newRound]);
    }

    // ----------------------------------------------------------------
    // 3. Passenger counters the driver's price
    // ----------------------------------------------------------------
    private function passengerCounter(array $req): string
    {
        $negotiationId  = (int)($req['iNegotiationId']    ?? 0);
        $userId         = (int)($req['iUserId']            ?? 0);
        $passengerPrice = (float)($req['fPassengerOffer']  ?? 0);

        if (!$negotiationId || !$userId || !$passengerPrice) {
            return $this->error('iNegotiationId, iUserId and fPassengerOffer are required.');
        }

        $neg = $this->fetchNegotiation($negotiationId);
        if (!$neg) return $this->error('Negotiation not found.');
        if ($neg['iUserId'] != $userId) return $this->error('Not authorised for this negotiation.');
        if ($neg['eStatus'] !== 'DriverCountered') {
            return $this->error("Cannot counter in status: {$neg['eStatus']}");
        }
        if ($this->isExpired($neg['dExpiresAt'])) {
            $this->expireNegotiation($negotiationId);
            return $this->error('Offer expired.');
        }
        if ($neg['iRoundCount'] >= $neg['iMaxRounds']) {
            $this->db->sql_query("UPDATE ride_bid_negotiations SET eStatus='Rejected' WHERE iNegotiationId=$negotiationId");
            return $this->error('Maximum rounds reached. Bid rejected.');
        }

        $newExpiry = date('Y-m-d H:i:s', strtotime("+{$this->defaultExpiryMins} minutes"));
        $newRound  = $neg['iRoundCount'] + 1;

        $this->db->sql_query(
            "UPDATE ride_bid_negotiations
             SET fPassengerOffer=$passengerPrice, eStatus='PassengerCountered', iRoundCount=$newRound, dExpiresAt='$newExpiry'
             WHERE iNegotiationId=$negotiationId"
        );
        $this->logRound($negotiationId, 'Passenger', $passengerPrice, 'Passenger counter-offer');

        $this->pushNotifyDriver($neg['iDriverId'], [
            'title' => 'Passenger countered!',
            'body'  => "Passenger now offers R$ " . number_format($passengerPrice, 2, ',', '.'),
            'data'  => ['type' => 'BidCounter', 'negotiationId' => $negotiationId],
        ]);

        return $this->success(['message' => 'Counter submitted.', 'newExpiry' => $newExpiry, 'round' => $newRound]);
    }

    // ----------------------------------------------------------------
    // 4. Accept the current offer (driver or passenger can accept)
    // ----------------------------------------------------------------
    private function acceptBid(array $req): string
    {
        $negotiationId = (int)($req['iNegotiationId'] ?? 0);
        $acceptedBy    = $req['eAcceptedBy'] ?? ''; // 'Passenger' or 'Driver'

        if (!$negotiationId || !in_array($acceptedBy, ['Passenger', 'Driver'])) {
            return $this->error('iNegotiationId and eAcceptedBy (Passenger|Driver) are required.');
        }

        $neg = $this->fetchNegotiation($negotiationId);
        if (!$neg) return $this->error('Negotiation not found.');
        if (!in_array($neg['eStatus'], ['Pending', 'DriverCountered', 'PassengerCountered'])) {
            return $this->error("Cannot accept in status: {$neg['eStatus']}");
        }
        if ($this->isExpired($neg['dExpiresAt'])) {
            $this->expireNegotiation($negotiationId);
            return $this->error('Offer expired.');
        }

        // Determine agreed price
        $finalPrice = ($acceptedBy === 'Passenger')
            ? $neg['fDriverCounter']   // passenger accepts driver's counter
            : $neg['fPassengerOffer']; // driver accepts passenger's offer

        if ($finalPrice <= 0) {
            // Fallback: take whichever is latest
            $finalPrice = max($neg['fPassengerOffer'], $neg['fDriverCounter']);
        }

        $this->db->sql_query(
            "UPDATE ride_bid_negotiations
             SET eStatus='Accepted', fFinalPrice=$finalPrice
             WHERE iNegotiationId=$negotiationId"
        );

        // Update driver_request with agreed price
        $this->db->sql_query(
            "UPDATE driver_request SET fTaxiBidAmount=$finalPrice
             WHERE iRequestId={$neg['iRequestId']}"
        );

        // Notify both parties
        $this->pushNotifyPassenger($neg['iUserId'], [
            'title' => 'Bid Accepted!',
            'body'  => "Your ride is confirmed at R$ " . number_format($finalPrice, 2, ',', '.'),
            'data'  => ['type' => 'BidAccepted', 'finalPrice' => $finalPrice, 'negotiationId' => $negotiationId],
        ]);
        $this->pushNotifyDriver($neg['iDriverId'], [
            'title' => 'Bid Accepted!',
            'body'  => "Ride confirmed for R$ " . number_format($finalPrice, 2, ',', '.') . ". Go pick up the passenger.",
            'data'  => ['type' => 'BidAccepted', 'finalPrice' => $finalPrice, 'negotiationId' => $negotiationId],
        ]);

        return $this->success([
            'message'    => 'Bid accepted.',
            'finalPrice' => $finalPrice,
            'requestId'  => $neg['iRequestId'],
        ]);
    }

    // ----------------------------------------------------------------
    // 5. Reject the bid
    // ----------------------------------------------------------------
    private function rejectBid(array $req): string
    {
        $negotiationId = (int)($req['iNegotiationId'] ?? 0);
        if (!$negotiationId) return $this->error('iNegotiationId required.');

        $neg = $this->fetchNegotiation($negotiationId);
        if (!$neg) return $this->error('Negotiation not found.');

        $this->db->sql_query(
            "UPDATE ride_bid_negotiations SET eStatus='Rejected' WHERE iNegotiationId=$negotiationId"
        );

        $this->pushNotifyPassenger($neg['iUserId'], [
            'title' => 'Bid Rejected',
            'body'  => 'The driver declined your offer. Try another driver.',
            'data'  => ['type' => 'BidRejected', 'negotiationId' => $negotiationId],
        ]);
        $this->pushNotifyDriver($neg['iDriverId'], [
            'title' => 'Bid Declined',
            'body'  => 'You declined the passenger offer.',
            'data'  => ['type' => 'BidRejected', 'negotiationId' => $negotiationId],
        ]);

        return $this->success(['message' => 'Bid rejected.']);
    }

    // ----------------------------------------------------------------
    // 6. Get current bid status (polling or push callback)
    // ----------------------------------------------------------------
    private function getBidStatus(array $req): string
    {
        $negotiationId = (int)($req['iNegotiationId'] ?? 0);
        if (!$negotiationId) return $this->error('iNegotiationId required.');

        $neg = $this->fetchNegotiation($negotiationId);
        if (!$neg) return $this->error('Negotiation not found.');

        $rounds = $this->db->MySQLSelect(
            "SELECT * FROM ride_bid_rounds WHERE iNegotiationId=$negotiationId ORDER BY iRoundId ASC"
        );

        return $this->success(['negotiation' => $neg, 'rounds' => $rounds]);
    }

    // ----------------------------------------------------------------
    // 7. Get all active bids for a driver or passenger
    // ----------------------------------------------------------------
    private function getActiveBids(array $req): string
    {
        $userId   = (int)($req['iUserId']   ?? 0);
        $driverId = (int)($req['iDriverId'] ?? 0);

        if ($userId) {
            $where = "iUserId = $userId";
        } elseif ($driverId) {
            $where = "iDriverId = $driverId";
        } else {
            return $this->error('iUserId or iDriverId required.');
        }

        $bids = $this->db->MySQLSelect(
            "SELECT * FROM ride_bid_negotiations WHERE $where
             AND eStatus IN ('Pending','DriverCountered','PassengerCountered')
             ORDER BY dCreatedAt DESC"
        );

        return $this->success(['bids' => $bids ?: []]);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------
    private function fetchNegotiation(int $id): ?array
    {
        $rows = $this->db->MySQLSelect(
            "SELECT * FROM ride_bid_negotiations WHERE iNegotiationId=$id LIMIT 1"
        );
        return $rows[0] ?? null;
    }

    private function logRound(int $negotiationId, string $offeredBy, float $amount, string $note): void
    {
        $now  = date('Y-m-d H:i:s');
        $note = addslashes($note);
        $this->db->sql_query(
            "INSERT INTO ride_bid_rounds (iNegotiationId, eOfferedBy, fAmount, vNote, dCreatedAt)
             VALUES ($negotiationId, '$offeredBy', $amount, '$note', '$now')"
        );
    }

    private function isExpired(string $expiresAt): bool
    {
        return strtotime($expiresAt) < time();
    }

    private function expireNegotiation(int $id): void
    {
        $this->db->sql_query(
            "UPDATE ride_bid_negotiations SET eStatus='Expired' WHERE iNegotiationId=$id"
        );
    }

    private function pushNotifyDriver(int $driverId, array $payload): void
    {
        $this->sendPushToUser($driverId, 'Driver', $payload);
    }

    private function pushNotifyPassenger(int $userId, array $payload): void
    {
        $this->sendPushToUser($userId, 'Passenger', $payload);
    }

    private function sendPushToUser(int $id, string $type, array $payload): void
    {
        // Integration point: call existing push notification system
        // The platform uses Firebase FCM (see cron_notification_email.php pattern)
        if (function_exists('sendPushNotification')) {
            sendPushNotification($id, $type, $payload['title'], $payload['body'], $payload['data'] ?? []);
        }
    }

    private function success(array $data): string
    {
        return json_encode(array_merge(['status' => 'success'], $data));
    }

    private function error(string $message): string
    {
        return json_encode(['status' => 'error', 'message' => $message]);
    }
}
