<?php
/**
 * No-Show Fee Management
 * Admin panel to review and approve/reject no-show fee requests.
 * Requires driver GPS confirmation at destination + admin approval.
 */
include_once('../common.php');

if (!$userObj->hasPermission('manage-cancel-reasons')) {
    $userObj->redirect();
}

$script = 'No_Show_Fee';

// Handle approve / reject
$isAjax = isset($_POST['isAjax']) && $_POST['isAjax'] == 'Yes';
if ($isAjax) {
    $iNoShowId = intval($_POST['iNoShowId'] ?? 0);
    $eAction   = in_array($_POST['eAction'], ['Approved','Rejected']) ? $_POST['eAction'] : '';
    $vAdminNote = htmlspecialchars($_POST['vAdminNote'] ?? '');
    $iAdminId   = intval($_SESSION['sess_iAdminUserId'] ?? 0);

    if ($iNoShowId > 0 && $eAction != '') {
        $obj->sql_query("UPDATE no_show_fee_requests SET
            eStatus = '$eAction',
            vAdminNote = '$vAdminNote',
            iApprovedBy = '$iAdminId',
            dApprovalDate = NOW()
            WHERE iNoShowId = '$iNoShowId' AND eStatus = 'Pending'");

        if ($eAction == 'Approved') {
            // Get fee data and driver
            $nsData = $obj->MySQLSelect("SELECT * FROM no_show_fee_requests WHERE iNoShowId='$iNoShowId'");
            if (!empty($nsData)) {
                $ns = $nsData[0];
                $fFee = floatval($ns['fFeeAmount']);
                $iDriverId = intval($ns['iDriverId']);
                $iTripId = intval($ns['iTripId']);

                if ($fFee > 0 && $iDriverId > 0) {
                    $walletNote = "Taxa de no-show aprovada - Corrida #$iTripId";
                    $WALLET_OBJ->AddWalletAmountDriver($iDriverId, $fFee, $walletNote, $iTripId);

                    // Mark fee as paid
                    $obj->sql_query("UPDATE no_show_fee_requests SET eFeePaid='Yes' WHERE iNoShowId='$iNoShowId'");

                    // Log auto-transfer
                    $obj->sql_query("INSERT INTO automated_fine_transfers
                        (iTripId, iNoShowId, iDriverId, fAmount, eFineType, eStatus, vNotes, dTransferDate)
                        VALUES ('$iTripId', '$iNoShowId', '$iDriverId', '$fFee', 'NoShowFee', 'Completed', '$walletNote', NOW())");
                }
            }
        }
        echo json_encode(['status' => 'success', 'eAction' => $eAction]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid data']);
    }
    exit;
}

// Settings save
$settingsAction = isset($_POST['settingsAction']) ? $_POST['settingsAction'] : '';
if ($settingsAction == 'save') {
    $fNoShowFeeAmount = floatval($_POST['fNoShowFeeAmount'] ?? 0);
    $fMaxGps = floatval($_POST['fNoShowMaxGpsDistanceMeters'] ?? 100);
    $eEnabled = in_array($_POST['eNoShowFeeEnabled'], ['Yes','No']) ? $_POST['eNoShowFeeEnabled'] : 'No';
    $obj->sql_query("UPDATE setup_info SET
        fNoShowFeeAmount = '$fNoShowFeeAmount',
        fNoShowMaxGpsDistanceMeters = '$fMaxGps',
        eNoShowFeeEnabled = '$eEnabled'
        LIMIT 1");
    $settingsSaved = true;
}

// Get settings
$setupData = $obj->MySQLSelect("SELECT fNoShowFeeAmount, fNoShowMaxGpsDistanceMeters, eNoShowFeeEnabled FROM setup_info LIMIT 1");
$settings = !empty($setupData) ? $setupData[0] : [];

// Pagination
$per_page = $DISPLAY_RECORD_NUMBER;
$ssql = '';

$statusFilter = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';
if ($statusFilter != '') $ssql .= " AND ns.eStatus = '$statusFilter'";

$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate   = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
if ($startDate != '') $ssql .= " AND DATE(ns.dRequestDate) >= '$startDate'";
if ($endDate != '')   $ssql .= " AND DATE(ns.dRequestDate) <= '$endDate'";

$totalData = $obj->MySQLSelect("SELECT COUNT(ns.iNoShowId) as Total FROM no_show_fee_requests ns WHERE 1=1 $ssql");
$total_results = intval($totalData[0]['Total'] ?? 0);
$total_pages = ceil($total_results / $per_page);
$page = max(1, intval($_GET['page'] ?? 1));
$start = ($page - 1) * $per_page;

$sql = "SELECT ns.*,
    CONCAT(rd.vName,' ',rd.vLastName) AS driverName, rd.vPhone AS driverPhone,
    CONCAT(ru.vName,' ',ru.vLastName) AS riderName, ru.vPhone AS riderPhone,
    tr.vRideNo
    FROM no_show_fee_requests ns
    LEFT JOIN register_driver rd ON ns.iDriverId = rd.iDriverId
    LEFT JOIN register_user ru ON ns.iUserId = ru.iUserId
    LEFT JOIN trips tr ON ns.iTripId = tr.iTripId
    WHERE 1=1 $ssql
    ORDER BY ns.iNoShowId DESC
    LIMIT $start, $per_page";
$noShowList = $obj->MySQLSelect($sql);

include_once('header.php');
?>
<div class="page-header">
    <h4>Taxa de No-Show</h4>
    <small>Gerenciar taxas aplicadas quando o passageiro n&atilde;o comparece ao embarque.</small>
</div>

<!-- Settings Panel -->
<div class="card mb-3">
    <div class="card-header"><strong>Configura&ccedil;&otilde;es de No-Show</strong></div>
    <div class="card-body">
        <?php if (!empty($settingsSaved)): ?><div class="alert alert-success">Configurações salvas.</div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="settingsAction" value="save">
            <div class="row">
                <div class="col-md-3">
                    <label>Taxa de No-Show Padr&atilde;o (R$)</label>
                    <input type="number" step="0.01" min="0" name="fNoShowFeeAmount"
                        value="<?php echo floatval($settings['fNoShowFeeAmount'] ?? 0); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Dist&acirc;ncia GPS M&aacute;x. do Embarque (metros)</label>
                    <input type="number" step="1" min="10" name="fNoShowMaxGpsDistanceMeters"
                        value="<?php echo floatval($settings['fNoShowMaxGpsDistanceMeters'] ?? 100); ?>" class="form-control">
                    <small class="text-muted">O motorista deve estar dentro desta dist&acirc;ncia do ponto de embarque.</small>
                </div>
                <div class="col-md-3">
                    <label>Taxa de No-Show Habilitada</label>
                    <select name="eNoShowFeeEnabled" class="form-control">
                        <option value="Yes" <?php echo ($settings['eNoShowFeeEnabled'] ?? '') == 'Yes' ? 'selected' : ''; ?>>Sim</option>
                        <option value="No" <?php echo ($settings['eNoShowFeeEnabled'] ?? 'No') != 'Yes' ? 'selected' : ''; ?>>N&atilde;o</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Salvar Configura&ccedil;&otilde;es</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-inline">
            <select name="eStatus" class="form-control mr-2">
                <option value="">-- Todos Status --</option>
                <option value="Pending" <?php echo $statusFilter=='Pending'?'selected':''; ?>>Pendente</option>
                <option value="Approved" <?php echo $statusFilter=='Approved'?'selected':''; ?>>Aprovado</option>
                <option value="Rejected" <?php echo $statusFilter=='Rejected'?'selected':''; ?>>Rejeitado</option>
            </select>
            <input type="date" name="startDate" value="<?php echo $startDate; ?>" class="form-control mr-2" placeholder="De">
            <input type="date" name="endDate" value="<?php echo $endDate; ?>" class="form-control mr-2" placeholder="At&eacute;">
            <button type="submit" class="btn btn-primary mr-2">Filtrar</button>
            <a href="no_show_fee.php" class="btn btn-secondary">Limpar</a>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-hover table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Corrida</th>
                    <th>Motorista</th>
                    <th>Passageiro</th>
                    <th>Taxa (R$)</th>
                    <th>GPS Dist. (m)</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>A&ccedil;&atilde;o</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($noShowList)): ?>
                <?php foreach ($noShowList as $ns): ?>
                <tr>
                    <td><?php echo $ns['iNoShowId']; ?></td>
                    <td><?php echo htmlspecialchars($ns['vRideNo'] ?? $ns['iTripId']); ?></td>
                    <td><?php echo htmlspecialchars($ns['driverName'] ?? ''); ?><br><small><?php echo $ns['driverPhone'] ?? ''; ?></small></td>
                    <td><?php echo htmlspecialchars($ns['riderName'] ?? ''); ?><br><small><?php echo $ns['riderPhone'] ?? ''; ?></small></td>
                    <td>R$ <?php echo number_format($ns['fFeeAmount'], 2, ',', '.'); ?></td>
                    <td><?php echo number_format($ns['fGpsDistanceFromPickup'], 0); ?>m</td>
                    <td>
                        <span class="badge badge-<?php echo ['Pending'=>'warning','Approved'=>'success','Rejected'=>'danger'][$ns['eStatus']] ?? 'secondary'; ?>">
                            <?php echo ['Pending'=>'Pendente','Approved'=>'Aprovado','Rejected'=>'Rejeitado'][$ns['eStatus']] ?? $ns['eStatus']; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($ns['dRequestDate'])); ?></td>
                    <td>
                        <?php if ($ns['eStatus'] == 'Pending'): ?>
                        <button class="btn btn-xs btn-success btnApprove"
                            data-id="<?php echo $ns['iNoShowId']; ?>">Aprovar</button>
                        <button class="btn btn-xs btn-danger btnReject"
                            data-id="<?php echo $ns['iNoShowId']; ?>">Rejeitar</button>
                        <?php else: ?>
                        <small class="text-muted"><?php echo htmlspecialchars($ns['vAdminNote']); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center">Nenhum registro encontrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $p; ?>&eStatus=<?php echo $statusFilter; ?>&startDate=<?php echo $startDate; ?>&endDate=<?php echo $endDate; ?>"><?php echo $p; ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).on('click', '.btnApprove', function() {
    var id = $(this).data('id');
    var note = prompt('Nota de aprovação (opcional):') || '';
    $.post('no_show_fee.php', {isAjax:'Yes', iNoShowId: id, eAction:'Approved', vAdminNote: note}, function(r) {
        if (r.status == 'success') { location.reload(); }
    }, 'json');
});
$(document).on('click', '.btnReject', function() {
    var id = $(this).data('id');
    var note = prompt('Motivo da rejeição:') || '';
    if (!note) return alert('Informe o motivo da rejeição.');
    $.post('no_show_fee.php', {isAjax:'Yes', iNoShowId: id, eAction:'Rejected', vAdminNote: note}, function(r) {
        if (r.status == 'success') { location.reload(); }
    }, 'json');
});
</script>

<?php include_once('footer.php'); ?>
