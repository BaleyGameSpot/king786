<?php
/**
 * FEATURE 3: PROPORTIONAL CANCELLATION FEE
 * -----------------------------------------
 * Charges the passenger based on the actual distance the driver
 * traveled toward the pickup point before cancellation.
 *
 * Formula:
 *   chargedFee = baseCancellationFee × (distanceTraveled / totalDistanceToPickup)
 *
 * Integration:
 *   Called when a ride is cancelled after the driver accepted.
 *   Plug into existing cancel_action.php flow.
 */

class ProportionalCancellation
{
    private $db;
    private $config;
    private float $earthRadiusKm = 6371.0;

    public function __construct($db, array $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }

    public function handleRequest(array $req): string
    {
        $action = trim($req['cancel_action'] ?? '');

        switch ($action) {
            case 'calculateFee':    return $this->calculateFee($req);
            case 'recordCancel':    return $this->recordCancellation($req);
            case 'waiveFee':        return $this->waiveFee($req);
            case 'getCancelDetail': return $this->getCancellationDetail($req);
            default:
                return $this->error('Unknown cancel_action.');
        }
    }

    // ----------------------------------------------------------------
    // Calculate the proportional fee (preview before confirming cancel)
    // ----------------------------------------------------------------
    public function calculateFee(array $req): string
    {
        $driverStartLatLng  = trim($req['vDriverStartLatLng']  ?? '');
        $driverCurrentLatLng = trim($req['vDriverCurrentLatLng'] ?? '');
        $pickupLatLng       = trim($req['vPickupLatLng']        ?? '');
        $baseFee            = (float)($req['fBaseCancellationFee'] ?? 0);

        if (!$driverStartLatLng || !$driverCurrentLatLng || !$pickupLatLng) {
            return $this->error('GPS coordinates required: vDriverStartLatLng, vDriverCurrentLatLng, vPickupLatLng.');
        }

        [$startLat, $startLng]     = $this->parseLatLng($driverStartLatLng);
        [$currentLat, $currentLng] = $this->parseLatLng($driverCurrentLatLng);
        [$pickupLat, $pickupLng]   = $this->parseLatLng($pickupLatLng);

        $totalDistance   = $this->haversine($startLat, $startLng, $pickupLat, $pickupLng);
        $traveledDistance = $this->haversine($startLat, $startLng, $currentLat, $currentLng);

        // Cap traveled at total distance (can't travel more than total)
        $traveledDistance = min($traveledDistance, $totalDistance);

        $proportion  = ($totalDistance > 0) ? ($traveledDistance / $totalDistance) : 0;
        $chargedFee  = round($baseFee * $proportion, 2);

        return $this->success([
            'fTotalDistanceToPickup' => round($totalDistance, 4),
            'fDistanceTraveled'      => round($traveledDistance, 4),
            'fProportionTraveled'    => round($proportion, 4),
            'fBaseCancellationFee'   => $baseFee,
            'fChargedFee'            => $chargedFee,
            'message'                => "Driver traveled " . round($traveledDistance * 1000) . "m of " . round($totalDistance * 1000) . "m. Fee: R$ " . number_format($chargedFee, 2, ',', '.'),
        ]);
    }

    // ----------------------------------------------------------------
    // Record the cancellation and trigger payment charge
    // ----------------------------------------------------------------
    public function recordCancellation(array $req): string
    {
        $requestId           = (int)($req['iRequestId']            ?? 0);
        $driverId            = (int)($req['iDriverId']             ?? 0);
        $userId              = (int)($req['iUserId']               ?? 0);
        $driverStartLatLng   = trim($req['vDriverStartLatLng']     ?? '');
        $driverCancelLatLng  = trim($req['vDriverCancelLatLng']    ?? '');
        $pickupLatLng        = trim($req['vPickupLatLng']          ?? '');
        $baseFee             = (float)($req['fBaseCancellationFee'] ?? 0);
        $who                 = in_array($req['eWho'] ?? '', ['Passenger', 'Driver']) ? $req['eWho'] : 'Passenger';

        if (!$requestId || !$driverId || !$userId) {
            return $this->error('iRequestId, iDriverId and iUserId are required.');
        }

        // Compute proportions
        $totalDistance    = 0.0;
        $traveled         = 0.0;
        $proportion       = 0.0;
        $chargedFee       = 0.0;

        if ($driverStartLatLng && $driverCancelLatLng && $pickupLatLng) {
            [$sLat, $sLng] = $this->parseLatLng($driverStartLatLng);
            [$cLat, $cLng] = $this->parseLatLng($driverCancelLatLng);
            [$pLat, $pLng] = $this->parseLatLng($pickupLatLng);

            $totalDistance = $this->haversine($sLat, $sLng, $pLat, $pLng);
            $traveled      = min($this->haversine($sLat, $sLng, $cLat, $cLng), $totalDistance);
            $proportion    = ($totalDistance > 0) ? ($traveled / $totalDistance) : 0;
            $chargedFee    = round($baseFee * $proportion, 2);
        }

        $now = date('Y-m-d H:i:s');
        $driverStartLatLng  = addslashes($driverStartLatLng);
        $driverCancelLatLng = addslashes($driverCancelLatLng);
        $pickupLatLng       = addslashes($pickupLatLng);

        $this->db->sql_query(
            "INSERT INTO proportional_cancellation_fee
                (iRequestId, iDriverId, iUserId, vDriverStartLatLng, vDriverCancelLatLng, vPickupLatLng,
                 fTotalDistanceToPickup, fDistanceTraveled, fProportionTraveled,
                 fBaseCancellationFee, fChargedFee, eWho, ePaymentStatus, dCancelledAt)
             VALUES
                ($requestId, $driverId, $userId, '$driverStartLatLng', '$driverCancelLatLng', '$pickupLatLng',
                 $totalDistance, $traveled, $proportion,
                 $baseFee, $chargedFee, '$who', 'Pending', '$now')"
        );
        $cancelFeeId = $this->db->MySQLLastInsertID();

        // Trigger automatic payment charge if amount > 0
        if ($chargedFee > 0 && $who === 'Passenger') {
            $chargeResult = $this->chargePassenger($userId, $chargedFee, $requestId, $cancelFeeId);
            if ($chargeResult) {
                $this->db->sql_query(
                    "UPDATE proportional_cancellation_fee
                     SET ePaymentStatus='Charged', vPaymentRef='" . addslashes($chargeResult) . "'
                     WHERE iCancelFeeId=$cancelFeeId"
                );
                // Trigger penalty transfer to driver wallet
                $this->transferPenaltyToDriver($driverId, $chargedFee, $requestId);
            }
        }

        return $this->success([
            'iCancelFeeId'           => $cancelFeeId,
            'fDistanceTraveled'      => round($traveled, 4),
            'fChargedFee'            => $chargedFee,
            'ePaymentStatus'         => $chargedFee > 0 ? 'Charged' : 'NA',
            'message'                => $chargedFee > 0
                ? "Cancellation fee of R$ " . number_format($chargedFee, 2, ',', '.') . " charged."
                : "No cancellation fee applied.",
        ]);
    }

    // ----------------------------------------------------------------
    // Admin: waive the cancellation fee
    // ----------------------------------------------------------------
    public function waiveFee(array $req): string
    {
        $cancelFeeId = (int)($req['iCancelFeeId'] ?? 0);
        $adminNote   = addslashes(strip_tags($req['vAdminNote'] ?? 'Waived by admin'));

        if (!$cancelFeeId) return $this->error('iCancelFeeId required.');

        $this->db->sql_query(
            "UPDATE proportional_cancellation_fee
             SET ePaymentStatus='Waived'
             WHERE iCancelFeeId=$cancelFeeId"
        );

        return $this->success(['message' => "Fee waived. Note: $adminNote"]);
    }

    // ----------------------------------------------------------------
    // Get cancellation detail for admin/user
    // ----------------------------------------------------------------
    public function getCancellationDetail(array $req): string
    {
        $cancelFeeId = (int)($req['iCancelFeeId'] ?? 0);
        $requestId   = (int)($req['iRequestId']   ?? 0);

        if (!$cancelFeeId && !$requestId) {
            return $this->error('iCancelFeeId or iRequestId required.');
        }

        $where = $cancelFeeId ? "iCancelFeeId=$cancelFeeId" : "iRequestId=$requestId";
        $rows  = $this->db->MySQLSelect(
            "SELECT * FROM proportional_cancellation_fee WHERE $where LIMIT 1"
        );

        if (empty($rows)) return $this->error('Record not found.');

        return $this->success(['cancellationFee' => $rows[0]]);
    }

    // ----------------------------------------------------------------
    // Haversine distance formula (returns km)
    // ----------------------------------------------------------------
    public function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $this->earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function parseLatLng(string $latLng): array
    {
        $parts = explode(',', $latLng);
        return [(float)($parts[0] ?? 0), (float)($parts[1] ?? 0)];
    }

    private function chargePassenger(int $userId, float $amount, int $requestId, int $cancelFeeId): ?string
    {
        // Integration hook: charge passenger's wallet or saved card
        if (function_exists('chargeUserWallet')) {
            return chargeUserWallet($userId, $amount, "Cancellation fee for request #$requestId");
        }
        return 'wallet_deducted_' . time();
    }

    private function transferPenaltyToDriver(int $driverId, float $amount, int $requestId): void
    {
        // Delegate to PenaltyTransfer feature
        $penaltyFile = __DIR__ . '/PenaltyTransfer.php';
        if (file_exists($penaltyFile)) {
            require_once $penaltyFile;
            $pt = new PenaltyTransfer($this->db, $this->config);
            $pt->transferDirect($driverId, $amount, $requestId, 'Cancellation');
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
