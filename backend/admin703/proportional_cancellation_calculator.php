<?php
/**
 * Proportional Cancellation Fee Calculator
 * Called via AJAX when a trip is cancelled to compute and apply fee.
 * Also handles auto-transfer to driver wallet.
 */
include_once('../common.php');

header('Content-Type: application/json');

$iTripId = intval($_POST['iTripId'] ?? 0);
$iCancelReasonId = intval($_POST['iCancelReasonId'] ?? 0);
$fDriverDistanceKm = floatval($_POST['fDriverDistanceKm'] ?? 0);

if ($iTripId <= 0 || $iCancelReasonId <= 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid parameters']);
    exit;
}

// Get cancel reason proportional fee config
$reasonData = $obj->MySQLSelect("SELECT * FROM cancel_reason WHERE iCancelReasonId='$iCancelReasonId' AND eProportionalFee='Yes'");
if (empty($reasonData)) {
    echo json_encode(['status' => 'no_fee', 'fFeeAmount' => 0]);
    exit;
}
$reason = $reasonData[0];

// Get trip and driver info
$tripData = $obj->MySQLSelect("SELECT tr.*, rd.iDriverId FROM trips tr
    LEFT JOIN register_driver rd ON tr.iDriverId = rd.iDriverId
    WHERE tr.iTripId = '$iTripId'");

if (empty($tripData)) {
    echo json_encode(['status' => 'error', 'msg' => 'Trip not found']);
    exit;
}
$trip = $tripData[0];
$iDriverId = intval($trip['iDriverId']);

// Calculate fee
$fRate = floatval($reason['fProportionalFeeRate']);
$fMin = floatval($reason['fMinProportionalFee']);
$fMax = floatval($reason['fMaxProportionalFee']);

$fFeeAmount = $fDriverDistanceKm * $fRate;

// Apply min/max bounds
if ($fMin > 0 && $fFeeAmount < $fMin) {
    $fFeeAmount = $fMin;
}
if ($fMax > 0 && $fFeeAmount > $fMax) {
    $fFeeAmount = $fMax;
}
$fFeeAmount = round($fFeeAmount, 2);

// Update trip record with distance and fee
$obj->sql_query("UPDATE trips SET
    fDriverDistanceToPickup = '$fDriverDistanceKm',
    fProportionalCancelFee = '$fFeeAmount'
    WHERE iTripId = '$iTripId'");

// Auto-transfer to driver wallet if amount > 0
if ($fFeeAmount > 0 && $iDriverId > 0) {
    // Credit driver wallet
    $walletNote = "Taxa de cancelamento proporcional - Corrida #$iTripId ({$fDriverDistanceKm}km)";
    $WALLET_OBJ->AddWalletAmountDriver($iDriverId, $fFeeAmount, $walletNote, $iTripId);

    // Log the auto transfer
    $obj->sql_query("INSERT INTO automated_fine_transfers
        (iTripId, iDriverId, fAmount, eFineType, eStatus, vNotes, dTransferDate)
        VALUES ('$iTripId', '$iDriverId', '$fFeeAmount', 'ProportionalFee', 'Completed', '$walletNote', NOW())");

    // Mark as transferred
    $obj->sql_query("UPDATE trips SET eProportionalFeeTransferred='Yes' WHERE iTripId='$iTripId'");
}

echo json_encode([
    'status'        => 'success',
    'fFeeAmount'    => $fFeeAmount,
    'fDistanceKm'   => $fDriverDistanceKm,
    'fRatePerKm'    => $fRate,
    'bTransferred'  => ($fFeeAmount > 0 && $iDriverId > 0)
]);
exit;
