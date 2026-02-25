<?php
/**
 * FEATURES WEBSERVICE ROUTER
 * ---------------------------
 * Central dispatcher for all new feature API endpoints.
 *
 * Integration: Add this require to include_webservice_shark.php:
 *
 *   // ---- New Features ----
 *   if (in_array($type, [
 *       'rideBidding','bookForOthers','cancelFee','noShow',
 *       'lostFound','facialRecognition','notification',
 *       'receipt','franchise','pagarme','efiBilling','penalty'
 *   ])) {
 *       require_once 'features/features_webservice.php';
 *       exit;
 *   }
 *
 * All feature classes are lazy-loaded per request type.
 * Config is passed from the global $tconfig / $obj variables.
 */

// Guard: only include from webservice context
if (!isset($obj) || !isset($tconfig)) {
    die(json_encode(['status' => 'error', 'message' => 'Direct access not allowed.']));
}

$featuresDir = __DIR__ . '/';

// ---------------------------------------------------------------
// Map type → feature class file and class name
// ---------------------------------------------------------------
$featureMap = [
    'rideBidding'       => ['file' => 'RideBidding.php',        'class' => 'RideBidding'],
    'bookForOthers'     => ['file' => 'BookForOthers.php',      'class' => 'BookForOthers'],
    'cancelFee'         => ['file' => 'ProportionalCancellation.php', 'class' => 'ProportionalCancellation'],
    'noShow'            => ['file' => 'NoShowVerification.php', 'class' => 'NoShowVerification'],
    'lostFound'         => ['file' => 'LostAndFound.php',       'class' => 'LostAndFound'],
    'facialRecognition' => ['file' => 'FacialRecognition.php',  'class' => 'FacialRecognition'],
    'notification'      => ['file' => 'SmartNotifications.php', 'class' => 'SmartNotifications'],
    'receipt'           => ['file' => 'InAppReceipts.php',      'class' => 'InAppReceipts'],
    'franchise'         => ['file' => 'FranchiseManagement.php','class' => 'FranchiseManagement'],
    'pagarme'           => ['file' => 'PagarmePayment.php',     'class' => 'PagarmePayment'],
    'efiBilling'        => ['file' => 'EfiBilling.php',         'class' => 'EfiBilling'],
    'penalty'           => ['file' => 'PenaltyTransfer.php',    'class' => 'PenaltyTransfer'],
];

$requestType = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';

// ---------------------------------------------------------------
// Authentication check (all feature endpoints require auth,
// except franchiseUser login)
// ---------------------------------------------------------------
$publicActions = ['franchise:loginFranchiseUser'];
$currentAction = $requestType . ':' . (trim($_REQUEST[$requestType . '_action'] ?? $_REQUEST['bid_action'] ?? $_REQUEST['franchise_action'] ?? $_REQUEST['penalty_action'] ?? '') );

$isPublic = in_array($currentAction, $publicActions);

if (!$isPublic) {
    // Basic auth token check (uses existing platform pattern)
    $authToken = $_REQUEST['vAuthToken'] ?? $_REQUEST['AuthToken'] ?? '';
    if (empty($authToken)) {
        echo json_encode(['status' => 'error', 'message' => 'Authentication required.', 'code' => 401]);
        exit;
    }
}

// ---------------------------------------------------------------
// Route to correct feature
// ---------------------------------------------------------------
if (isset($featureMap[$requestType])) {
    $featureDef = $featureMap[$requestType];
    $classFile  = $featuresDir . $featureDef['file'];
    $className  = $featureDef['class'];

    if (!file_exists($classFile)) {
        echo json_encode(['status' => 'error', 'message' => "Feature module not found: {$featureDef['file']}"]);
        exit;
    }

    require_once $classFile;

    if (!class_exists($className)) {
        echo json_encode(['status' => 'error', 'message' => "Feature class not found: $className"]);
        exit;
    }

    $instance = new $className($obj, $tconfig);
    $response = $instance->handleRequest($_REQUEST);

    // Set JSON content type
    header('Content-Type: application/json; charset=UTF-8');
    echo $response;
    exit;
}

// ---------------------------------------------------------------
// Unknown type
// ---------------------------------------------------------------
echo json_encode([
    'status'  => 'error',
    'message' => "Unknown type: $requestType. Available: " . implode(', ', array_keys($featureMap)),
]);
exit;
