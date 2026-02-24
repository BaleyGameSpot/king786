<?php
/**
 * Proportional Cancellation Fee Settings
 * Fee is calculated based on distance already traveled by driver toward pickup point.
 */
include_once('../common.php');

if (!$userObj->hasPermission('manage-cancel-reasons')) {
    $userObj->redirect();
}

$script = 'Proportional_Cancellation_Fee';
$msg = '';

// Handle form save
$action = isset($_POST['action']) ? $_POST['action'] : '';
if ($action == 'save') {
    $iCancelReasonId = intval($_POST['iCancelReasonId']);
    $eProportionalFee = in_array($_POST['eProportionalFee'], ['Yes','No']) ? $_POST['eProportionalFee'] : 'No';
    $fProportionalFeeRate = floatval($_POST['fProportionalFeeRate']);
    $fMinProportionalFee = floatval($_POST['fMinProportionalFee']);
    $fMaxProportionalFee = floatval($_POST['fMaxProportionalFee']);

    if ($iCancelReasonId > 0) {
        $obj->sql_query("UPDATE `cancel_reason` SET
            `eProportionalFee` = '$eProportionalFee',
            `fProportionalFeeRate` = '$fProportionalFeeRate',
            `fMinProportionalFee` = '$fMinProportionalFee',
            `fMaxProportionalFee` = '$fMaxProportionalFee'
            WHERE `iCancelReasonId` = '$iCancelReasonId'");
        $msg = 'success';
    }
}

// Handle toggle proportional fee for a reason
$toggleId = isset($_GET['toggleId']) ? intval($_GET['iCancelReasonId']) : 0;
if ($toggleId > 0 && isset($_GET['eProportionalFee'])) {
    $val = $_GET['eProportionalFee'] == 'Yes' ? 'No' : 'Yes';
    $obj->sql_query("UPDATE `cancel_reason` SET `eProportionalFee`='$val' WHERE `iCancelReasonId`='$toggleId'");
    header('Location: proportional_cancellation_settings.php');
    exit;
}

// Load all cancel reasons
$sql = "SELECT cr.*, vCancelReason FROM cancel_reason cr
        ORDER BY cr.iSortId ASC";
$cancelReasons = $obj->MySQLSelect($sql);

$pageTitle = 'Taxa de Cancelamento Proporcional';
include_once('header.php');
?>
<div class="page-header">
    <h4><?php echo $pageTitle; ?></h4>
    <small>Configurar taxa de cancelamento baseada na dist&acirc;ncia percorrida pelo motorista at&eacute; o ponto de embarque.</small>
</div>

<?php if ($msg == 'success'): ?>
<div class="alert alert-success">Configura&ccedil;&otilde;es salvas com sucesso.</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <strong>Como funciona:</strong> Quando um passageiro cancela a corrida ap&oacute;s o motorista j&aacute; ter se deslocado,
            uma taxa proporcional &eacute; calculada com base na dist&acirc;ncia percorrida (km) &times; a tarifa configurada.
            A taxa &eacute; automaticamente creditada na carteira digital do motorista.
        </div>

        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Motivo de Cancelamento</th>
                    <th>Taxa Proporcional</th>
                    <th>R$/km</th>
                    <th>M&iacute;nimo (R$)</th>
                    <th>M&aacute;ximo (R$)</th>
                    <th>A&ccedil;&otilde;es</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($cancelReasons)): ?>
                <?php foreach ($cancelReasons as $reason): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reason['vCancelReason'] ?? ''); ?></td>
                    <td>
                        <span class="badge badge-<?php echo ($reason['eProportionalFee'] ?? 'No') == 'Yes' ? 'success' : 'secondary'; ?>">
                            <?php echo ($reason['eProportionalFee'] ?? 'No') == 'Yes' ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </td>
                    <td>R$ <?php echo number_format($reason['fProportionalFeeRate'] ?? 0, 2, ',', '.'); ?></td>
                    <td>R$ <?php echo number_format($reason['fMinProportionalFee'] ?? 0, 2, ',', '.'); ?></td>
                    <td>R$ <?php echo number_format($reason['fMaxProportionalFee'] ?? 0, 2, ',', '.'); ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal"
                            data-id="<?php echo $reason['iCancelReasonId']; ?>"
                            data-fee="<?php echo $reason['eProportionalFee'] ?? 'No'; ?>"
                            data-rate="<?php echo $reason['fProportionalFeeRate'] ?? 0; ?>"
                            data-min="<?php echo $reason['fMinProportionalFee'] ?? 0; ?>"
                            data-max="<?php echo $reason['fMaxProportionalFee'] ?? 0; ?>">
                            Editar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Nenhum motivo de cancelamento encontrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Taxa Proporcional</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="iCancelReasonId" id="modalId">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Taxa Proporcional</label>
                        <select name="eProportionalFee" id="modalFee" class="form-control">
                            <option value="Yes">Ativo</option>
                            <option value="No">Inativo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tarifa por km (R$)</label>
                        <input type="number" step="0.01" min="0" name="fProportionalFeeRate" id="modalRate" class="form-control">
                        <small class="text-muted">Valor cobrado por cada km percorrido pelo motorista at&eacute; o embarque.</small>
                    </div>
                    <div class="form-group">
                        <label>Taxa M&iacute;nima (R$)</label>
                        <input type="number" step="0.01" min="0" name="fMinProportionalFee" id="modalMin" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Taxa M&aacute;xima (R$) <small>(0 = sem limite)</small></label>
                        <input type="number" step="0.01" min="0" name="fMaxProportionalFee" id="modalMax" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#editModal').on('show.bs.modal', function(e) {
    var btn = $(e.relatedTarget);
    $('#modalId').val(btn.data('id'));
    $('#modalFee').val(btn.data('fee'));
    $('#modalRate').val(btn.data('rate'));
    $('#modalMin').val(btn.data('min'));
    $('#modalMax').val(btn.data('max'));
});
</script>

<?php include_once('footer.php'); ?>
