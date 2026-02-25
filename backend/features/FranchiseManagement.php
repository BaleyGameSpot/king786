<?php
/**
 * FEATURE 9: FRANCHISE MANAGEMENT SYSTEM
 * ----------------------------------------
 * Independent city-based management with hierarchical access levels.
 * - Master Admin: full platform view, manages franchises
 * - Franchisee:   manages own city operations, drivers, and reports
 * - Operator:     limited daily operations (no financial access)
 *
 * Integrates with:
 *   - PagarmePayment (split payment recipient per franchise)
 *   - EfiBilling (monthly subscription per franchise)
 *   - SmartNotifications (franchise-targeted pushes)
 */

class FranchiseManagement
{
    private $db;
    private $config;

    public function __construct($db, array $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }

    public function handleRequest(array $req): string
    {
        $action = trim($req['franchise_action'] ?? '');

        switch ($action) {
            case 'create':               return $this->createFranchise($req);
            case 'update':               return $this->updateFranchise($req);
            case 'getList':              return $this->listFranchises($req);
            case 'getDetail':            return $this->getFranchiseDetail($req);
            case 'suspend':              return $this->changeFranchiseStatus($req, 'Suspended');
            case 'activate':             return $this->changeFranchiseStatus($req, 'Active');
            case 'addUser':              return $this->addFranchiseUser($req);
            case 'updateUser':           return $this->updateFranchiseUser($req);
            case 'listUsers':            return $this->listFranchiseUsers($req);
            case 'assignDriver':         return $this->assignDriver($req);
            case 'unassignDriver':       return $this->unassignDriver($req);
            case 'listDrivers':          return $this->listFranchiseDrivers($req);
            case 'getDashboard':         return $this->getFranchiseDashboard($req);
            case 'updateRevenueShares':  return $this->updateRevenueShares($req);
            case 'loginFranchiseUser':   return $this->loginFranchiseUser($req);
            default:
                return $this->error('Unknown franchise_action.');
        }
    }

    // ----------------------------------------------------------------
    // 1. Create a new franchise
    // ----------------------------------------------------------------
    private function createFranchise(array $req): string
    {
        $name           = strip_tags(trim($req['vFranchiseName']        ?? ''));
        $city           = strip_tags(trim($req['vCity']                  ?? ''));
        $state          = strip_tags(trim($req['vState']                 ?? ''));
        $country        = strip_tags(trim($req['vCountry']               ?? 'Brazil'));
        $areaJson       = trim($req['vCoverageAreaJson']                 ?? '{}');
        $revenueShare   = (float)($req['fRevenueSharePercent']           ?? 10.00);
        $masterShare    = (float)($req['fMasterSharePercent']            ?? 15.00);
        $driverShare    = (float)($req['fDriverSharePercent']            ?? 75.00);
        $masterAdminId  = (int)($req['iMasterAdminId']                   ?? 0);

        if (!$name || !$city) return $this->error('vFranchiseName and vCity required.');

        // Validate percentages sum to 100
        $total = $revenueShare + $masterShare + $driverShare;
        if (abs($total - 100.0) > 0.01) {
            return $this->error("Revenue shares must sum to 100%. Current sum: $total%");
        }

        $name     = addslashes($name);
        $city     = addslashes($city);
        $state    = addslashes($state);
        $country  = addslashes($country);
        $areaJson = addslashes($areaJson);
        $now      = date('Y-m-d H:i:s');
        $mAdmin   = $masterAdminId ?: 'NULL';

        $this->db->sql_query(
            "INSERT INTO franchises
                (vFranchiseName, vCity, vState, vCountry, vCoverageAreaJson,
                 fRevenueSharePercent, fMasterSharePercent, fDriverSharePercent,
                 eStatus, iMasterAdminId, dCreatedAt)
             VALUES
                ('$name', '$city', '$state', '$country', '$areaJson',
                 $revenueShare, $masterShare, $driverShare,
                 'Active', $mAdmin, '$now')"
        );
        $franchiseId = $this->db->MySQLLastInsertID();

        return $this->success(['iFranchiseId' => $franchiseId, 'message' => "Franchise '$name' created for $city."]);
    }

    // ----------------------------------------------------------------
    // 2. Update franchise details
    // ----------------------------------------------------------------
    private function updateFranchise(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        if (!$franchiseId) return $this->error('iFranchiseId required.');

        $updates = [];
        if (!empty($req['vFranchiseName']))    $updates[] = "vFranchiseName='"  . addslashes(strip_tags($req['vFranchiseName']))  . "'";
        if (!empty($req['vCity']))              $updates[] = "vCity='"           . addslashes(strip_tags($req['vCity']))           . "'";
        if (!empty($req['vState']))             $updates[] = "vState='"          . addslashes(strip_tags($req['vState']))          . "'";
        if (!empty($req['vCoverageAreaJson']))  $updates[] = "vCoverageAreaJson='" . addslashes($req['vCoverageAreaJson'])         . "'";
        if (!empty($req['vPagarmeRecipientId'])) $updates[] = "vPagarmeRecipientId='" . addslashes($req['vPagarmeRecipientId'])   . "'";
        if (!empty($req['vEfiClientId']))       $updates[] = "vEfiClientId='"    . addslashes($req['vEfiClientId'])                . "'";
        if (!empty($req['vEfiClientSecret']))   $updates[] = "vEfiClientSecret='" . addslashes($req['vEfiClientSecret'])          . "'";

        if (isset($req['fRevenueSharePercent'], $req['fMasterSharePercent'], $req['fDriverSharePercent'])) {
            $rs = (float)$req['fRevenueSharePercent'];
            $ms = (float)$req['fMasterSharePercent'];
            $ds = (float)$req['fDriverSharePercent'];
            if (abs($rs + $ms + $ds - 100) > 0.01) {
                return $this->error('Revenue shares must sum to 100%.');
            }
            $updates[] = "fRevenueSharePercent=$rs, fMasterSharePercent=$ms, fDriverSharePercent=$ds";
        }

        if (empty($updates)) return $this->error('Nothing to update.');

        $this->db->sql_query(
            "UPDATE franchises SET " . implode(', ', $updates) . " WHERE iFranchiseId=$franchiseId"
        );
        return $this->success(['message' => 'Franchise updated.']);
    }

    // ----------------------------------------------------------------
    // 3. List franchises
    // ----------------------------------------------------------------
    private function listFranchises(array $req): string
    {
        $status = trim($req['eStatus'] ?? '');
        $where  = $status ? "WHERE eStatus='" . addslashes($status) . "'" : '';

        $rows = $this->db->MySQLSelect(
            "SELECT iFranchiseId, vFranchiseName, vCity, vState, vCountry,
                    fRevenueSharePercent, fMasterSharePercent, fDriverSharePercent,
                    eStatus, dCreatedAt
             FROM franchises $where
             ORDER BY vCity ASC"
        );
        return $this->success(['franchises' => $rows ?: []]);
    }

    // ----------------------------------------------------------------
    // 4. Get franchise detail with stats
    // ----------------------------------------------------------------
    private function getFranchiseDetail(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        if (!$franchiseId) return $this->error('iFranchiseId required.');

        $rows = $this->db->MySQLSelect(
            "SELECT * FROM franchises WHERE iFranchiseId=$franchiseId LIMIT 1"
        );
        if (empty($rows)) return $this->error('Franchise not found.');

        $franchise = $rows[0];
        // Remove sensitive credentials from public response
        unset($franchise['vEfiClientSecret']);

        // Count drivers
        $driverCount = $this->db->MySQLSelect(
            "SELECT COUNT(*) AS cnt FROM franchise_driver_map WHERE iFranchiseId=$franchiseId AND eStatus='Active'"
        );
        $franchise['iDriverCount'] = (int)($driverCount[0]['cnt'] ?? 0);

        return $this->success(['franchise' => $franchise]);
    }

    // ----------------------------------------------------------------
    // 5. Change franchise status
    // ----------------------------------------------------------------
    private function changeFranchiseStatus(array $req, string $status): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        if (!$franchiseId) return $this->error('iFranchiseId required.');

        $this->db->sql_query(
            "UPDATE franchises SET eStatus='$status' WHERE iFranchiseId=$franchiseId"
        );
        return $this->success(['message' => "Franchise status set to $status."]);
    }

    // ----------------------------------------------------------------
    // 6. Add a user (franchisee admin or operator)
    // ----------------------------------------------------------------
    private function addFranchiseUser(array $req): string
    {
        $franchiseId  = (int)($req['iFranchiseId'] ?? 0);
        $name         = strip_tags(trim($req['vName']   ?? ''));
        $email        = trim($req['vEmail']              ?? '');
        $password     = trim($req['vPassword']           ?? '');
        $phone        = trim($req['vPhone']              ?? '');
        $role         = in_array($req['eRole'] ?? '', ['Master','Franchisee','Operator'])
                        ? $req['eRole'] : 'Franchisee';

        if (!$franchiseId || !$name || !$email || !$password) {
            return $this->error('iFranchiseId, vName, vEmail and vPassword required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('Invalid email address.');
        }
        if (strlen($password) < 8) {
            return $this->error('Password must be at least 8 characters.');
        }

        // Check duplicate email
        $exists = $this->db->MySQLSelect(
            "SELECT iFranchiseUserId FROM franchise_users WHERE vEmail='" . addslashes($email) . "' LIMIT 1"
        );
        if (!empty($exists)) return $this->error('Email already registered.');

        $passHash = password_hash($password, PASSWORD_BCRYPT);
        $name     = addslashes($name);
        $email    = addslashes($email);
        $phone    = addslashes($phone);
        $now      = date('Y-m-d H:i:s');

        $this->db->sql_query(
            "INSERT INTO franchise_users (iFranchiseId, vName, vEmail, vPassword, vPhone, eRole, eStatus, dCreatedAt)
             VALUES ($franchiseId, '$name', '$email', '$passHash', '$phone', '$role', 'Active', '$now')"
        );
        $userId = $this->db->MySQLLastInsertID();

        return $this->success(['iFranchiseUserId' => $userId, 'message' => "Franchise user $name added as $role."]);
    }

    // ----------------------------------------------------------------
    // 7. Update franchise user
    // ----------------------------------------------------------------
    private function updateFranchiseUser(array $req): string
    {
        $userId  = (int)($req['iFranchiseUserId'] ?? 0);
        if (!$userId) return $this->error('iFranchiseUserId required.');

        $updates = [];
        if (!empty($req['vName']))    $updates[] = "vName='"  . addslashes(strip_tags($req['vName']))  . "'";
        if (!empty($req['vPhone']))   $updates[] = "vPhone='" . addslashes($req['vPhone'])             . "'";
        if (!empty($req['eRole']))    $updates[] = "eRole='"  . addslashes($req['eRole'])              . "'";
        if (!empty($req['eStatus']))  $updates[] = "eStatus='" . addslashes($req['eStatus'])           . "'";
        if (!empty($req['vPermissionsJson'])) {
            $updates[] = "vPermissionsJson='" . addslashes($req['vPermissionsJson']) . "'";
        }
        if (!empty($req['vPassword'])) {
            if (strlen($req['vPassword']) < 8) return $this->error('Password too short.');
            $updates[] = "vPassword='" . addslashes(password_hash($req['vPassword'], PASSWORD_BCRYPT)) . "'";
        }
        if (empty($updates)) return $this->error('Nothing to update.');

        $this->db->sql_query(
            "UPDATE franchise_users SET " . implode(', ', $updates) . " WHERE iFranchiseUserId=$userId"
        );
        return $this->success(['message' => 'Franchise user updated.']);
    }

    // ----------------------------------------------------------------
    // 8. List users of a franchise
    // ----------------------------------------------------------------
    private function listFranchiseUsers(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        if (!$franchiseId) return $this->error('iFranchiseId required.');

        $rows = $this->db->MySQLSelect(
            "SELECT iFranchiseUserId, vName, vEmail, vPhone, eRole, eStatus, dLastLogin, dCreatedAt
             FROM franchise_users
             WHERE iFranchiseId=$franchiseId
             ORDER BY eRole ASC, vName ASC"
        );
        return $this->success(['users' => $rows ?: []]);
    }

    // ----------------------------------------------------------------
    // 9. Assign a driver to a franchise
    // ----------------------------------------------------------------
    private function assignDriver(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        $driverId    = (int)($req['iDriverId']    ?? 0);
        if (!$franchiseId || !$driverId) return $this->error('iFranchiseId and iDriverId required.');

        // Check driver exists
        $driver = $this->db->MySQLSelect(
            "SELECT iDriverId FROM register_driver WHERE iDriverId=$driverId LIMIT 1"
        );
        if (empty($driver)) return $this->error('Driver not found.');

        // Insert or restore assignment
        $existing = $this->db->MySQLSelect(
            "SELECT iMapId FROM franchise_driver_map WHERE iFranchiseId=$franchiseId AND iDriverId=$driverId LIMIT 1"
        );
        $now = date('Y-m-d H:i:s');
        if (!empty($existing)) {
            $this->db->sql_query(
                "UPDATE franchise_driver_map SET eStatus='Active' WHERE iMapId={$existing[0]['iMapId']}"
            );
        } else {
            $this->db->sql_query(
                "INSERT INTO franchise_driver_map (iFranchiseId, iDriverId, eStatus, dAssignedAt)
                 VALUES ($franchiseId, $driverId, 'Active', '$now')"
            );
        }
        return $this->success(['message' => "Driver #$driverId assigned to franchise #$franchiseId."]);
    }

    // ----------------------------------------------------------------
    // 10. Unassign a driver
    // ----------------------------------------------------------------
    private function unassignDriver(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        $driverId    = (int)($req['iDriverId']    ?? 0);
        if (!$franchiseId || !$driverId) return $this->error('iFranchiseId and iDriverId required.');

        $this->db->sql_query(
            "UPDATE franchise_driver_map SET eStatus='Inactive'
             WHERE iFranchiseId=$franchiseId AND iDriverId=$driverId"
        );
        return $this->success(['message' => "Driver unassigned from franchise."]);
    }

    // ----------------------------------------------------------------
    // 11. List drivers of a franchise
    // ----------------------------------------------------------------
    private function listFranchiseDrivers(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        if (!$franchiseId) return $this->error('iFranchiseId required.');

        $rows = $this->db->MySQLSelect(
            "SELECT rd.iDriverId, rd.vName, rd.vLastName, rd.vMobileNo, rd.eStatus AS eDriverStatus,
                    fdm.eStatus AS eFranchiseStatus, fdm.dAssignedAt
             FROM franchise_driver_map fdm
             JOIN register_driver rd ON rd.iDriverId=fdm.iDriverId
             WHERE fdm.iFranchiseId=$franchiseId AND fdm.eStatus='Active'
             ORDER BY rd.vName ASC"
        );
        return $this->success(['drivers' => $rows ?: []]);
    }

    // ----------------------------------------------------------------
    // 12. Get franchise dashboard (stats for franchisee admin)
    // ----------------------------------------------------------------
    private function getFranchiseDashboard(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        if (!$franchiseId) return $this->error('iFranchiseId required.');

        // Driver count
        $drivers = $this->db->MySQLSelect(
            "SELECT COUNT(*) AS cnt FROM franchise_driver_map
             WHERE iFranchiseId=$franchiseId AND eStatus='Active'"
        );

        // Revenue this month
        $revenue = $this->db->MySQLSelect(
            "SELECT COALESCE(SUM(fFranchiseeAmount),0) AS total
             FROM pagarme_split_logs
             WHERE iFranchiseId=$franchiseId
               AND MONTH(dCreatedAt)=MONTH(NOW()) AND YEAR(dCreatedAt)=YEAR(NOW())
               AND eStatus='Completed'"
        );

        // Trips this month
        $trips = $this->db->MySQLSelect(
            "SELECT COUNT(*) AS cnt
             FROM pagarme_split_logs
             WHERE iFranchiseId=$franchiseId
               AND MONTH(dCreatedAt)=MONTH(NOW()) AND YEAR(dCreatedAt)=YEAR(NOW())"
        );

        // Pending no-shows
        $noShows = $this->db->MySQLSelect(
            "SELECT COUNT(*) AS cnt FROM no_show_incidents WHERE eStatus='PendingReview'"
        );

        return $this->success([
            'iActiveDrivers'       => (int)($drivers[0]['cnt'] ?? 0),
            'fRevenueThisMonth'    => (float)($revenue[0]['total'] ?? 0),
            'iTripsThisMonth'      => (int)($trips[0]['cnt'] ?? 0),
            'iPendingNoShowReview' => (int)($noShows[0]['cnt'] ?? 0),
        ]);
    }

    // ----------------------------------------------------------------
    // 13. Update revenue share percentages
    // ----------------------------------------------------------------
    private function updateRevenueShares(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        $rs          = (float)($req['fRevenueSharePercent'] ?? 0);
        $ms          = (float)($req['fMasterSharePercent']  ?? 0);
        $ds          = (float)($req['fDriverSharePercent']  ?? 0);

        if (!$franchiseId) return $this->error('iFranchiseId required.');
        if (abs($rs + $ms + $ds - 100) > 0.01) return $this->error('Shares must sum to 100%.');

        $this->db->sql_query(
            "UPDATE franchises
             SET fRevenueSharePercent=$rs, fMasterSharePercent=$ms, fDriverSharePercent=$ds
             WHERE iFranchiseId=$franchiseId"
        );
        return $this->success(['message' => 'Revenue shares updated.']);
    }

    // ----------------------------------------------------------------
    // 14. Franchise user login (returns session token)
    // ----------------------------------------------------------------
    private function loginFranchiseUser(array $req): string
    {
        $email    = trim($req['vEmail']    ?? '');
        $password = trim($req['vPassword'] ?? '');

        if (!$email || !$password) return $this->error('vEmail and vPassword required.');

        $rows = $this->db->MySQLSelect(
            "SELECT fu.*, f.vFranchiseName, f.vCity
             FROM franchise_users fu
             JOIN franchises f ON f.iFranchiseId=fu.iFranchiseId
             WHERE fu.vEmail='" . addslashes($email) . "' AND fu.eStatus='Active' LIMIT 1"
        );
        if (empty($rows)) return $this->error('Invalid credentials.');

        $user = $rows[0];
        if (!password_verify($password, $user['vPassword'])) {
            return $this->error('Invalid credentials.');
        }

        // Update last login
        $now = date('Y-m-d H:i:s');
        $this->db->sql_query(
            "UPDATE franchise_users SET dLastLogin='$now' WHERE iFranchiseUserId={$user['iFranchiseUserId']}"
        );

        // Generate simple session token (in production, use JWT)
        $token = bin2hex(random_bytes(32));

        // Remove password from response
        unset($user['vPassword']);

        return $this->success([
            'user'       => $user,
            'vAuthToken' => $token,
            'message'    => 'Login successful.',
        ]);
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
