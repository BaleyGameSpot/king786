<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexão direta
$host = 'localhost';
$user = 'ridey_superapp';
$pass = 'T3^y[r~}ukrM';
$dbname = 'ridey_app';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$log = "";
$tables = [];
$tablesResult = $conn->query("SHOW TABLES");
while ($tableRow = $tablesResult->fetch_array()) {
    $table = $tableRow[0];
    $columnsResult = $conn->query("SHOW COLUMNS FROM `$table`");
    $hasPrimary = false;
    $targetColumn = "";
    $isAutoIncrement = false;

    while ($col = $columnsResult->fetch_assoc()) {
        if ($col['Key'] == 'PRI') {
            $hasPrimary = true;
            if (strpos($col['Extra'], 'auto_increment') !== false) {
                $isAutoIncrement = true;
            }
        }
        if (preg_match('/^(i.*Id|.*Id)$/i', $col['Field'])) {
            $targetColumn = $col['Field'];
        }
    }

    $tables[] = [
        'name' => $table,
        'targetColumn' => $targetColumn,
        'hasPrimary' => $hasPrimary,
        'isAutoIncrement' => $isAutoIncrement
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['selected'] ?? [];
    $action = $_POST['action'] ?? '';

    foreach ($tables as $t) {
        if (in_array($t['name'], $selected)) {
            if ($action === 'pk') {
                if ($t['hasPrimary']) {
                    $log .= "⚠ Tabela {$t['name']} já tem PRIMARY KEY → pulado<br>";
                } elseif ($t['targetColumn']) {
                    $sql = "ALTER TABLE `{$t['name']}` ADD PRIMARY KEY (`{$t['targetColumn']}`)";
                    if ($conn->query($sql)) {
                        $log .= "✅ PRIMARY KEY adicionado em {$t['name']}<br>";
                    } else {
                        $log .= "❌ Erro PK em {$t['name']}: {$conn->error}<br>";
                    }
                } else {
                    $log .= "❌ Nenhuma coluna alvo encontrada em {$t['name']} para PRIMARY KEY<br>";
                }
            }

            if ($action === 'ai') {
                if ($t['isAutoIncrement']) {
                    $log .= "⚠ Tabela {$t['name']} já tem AUTO_INCREMENT → pulado<br>";
                } elseif (!$t['hasPrimary']) {
                    $log .= "❌ Não pode adicionar AUTO_INCREMENT em {$t['name']} sem PRIMARY KEY<br>";
                } elseif ($t['targetColumn']) {
                    // Checar se a coluna é a PRIMARY KEY
                    $primaryKeyResult = $conn->query("SHOW INDEX FROM `{$t['name']}` WHERE Key_name = 'PRIMARY'");
                    $primaryKeyCol = '';
                    if ($primaryKeyRow = $primaryKeyResult->fetch_assoc()) {
                        $primaryKeyCol = $primaryKeyRow['Column_name'];
                    }

                    if ($primaryKeyCol !== $t['targetColumn']) {
                        $log .= "❌ Coluna {$t['targetColumn']} não é a PRIMARY KEY em {$t['name']}, não pode aplicar AUTO_INCREMENT<br>";
                    } else {
                        // Checar se a coluna é INT
                        $colTypeResult = $conn->query("SHOW FIELDS FROM `{$t['name']}` WHERE Field = '{$t['targetColumn']}'");
                        $colInfo = $colTypeResult->fetch_assoc();
                        if (stripos($colInfo['Type'], 'int') === false) {
                            $log .= "❌ Coluna {$t['targetColumn']} em {$t['name']} não é INT, não pode aplicar AUTO_INCREMENT<br>";
                        } else {
                            $sql = "ALTER TABLE `{$t['name']}` MODIFY `{$t['targetColumn']}` INT NOT NULL AUTO_INCREMENT";
                            if ($conn->query($sql)) {
                                $log .= "✅ AUTO_INCREMENT adicionado em {$t['name']}<br>";
                            } else {
                                $log .= "❌ Erro AI em {$t['name']}: {$conn->error}<br>";
                            }
                        }
                    }
                } else {
                    $log .= "❌ Nenhuma coluna alvo encontrada em {$t['name']} para AUTO_INCREMENT<br>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gerenciador de Permissões DB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">🔧 Gerenciador de PRIMARY KEY & AUTO_INCREMENT</h1>

    <?php if ($log): ?>
        <div class="mb-3">
            <button class="btn btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#logPanel">
                📄 Mostrar/Ocultar Log
            </button>
            <div class="collapse mt-2" id="logPanel">
                <div class="alert alert-light" style="max-height: 300px; overflow-y: auto;">
                    <strong>Log:</strong><br><?= $log ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST">
        <table class="table table-bordered bg-white">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>Tabela</th>
                    <th>Coluna alvo</th>
                    <th>PRIMARY KEY</th>
                    <th>AUTO_INCREMENT</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $t): ?>
                    <tr>
                        <td><input type="checkbox" name="selected[]" value="<?= $t['name'] ?>"></td>
                        <td><?= $t['name'] ?></td>
                        <td><?= $t['targetColumn'] ?: '<span class="text-danger">Não encontrada</span>' ?></td>
                        <td><?= $t['hasPrimary'] ? '✅' : '❌' ?></td>
                        <td><?= $t['isAutoIncrement'] ? '✅' : '❌' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="mb-3">
            <button type="submit" name="action" value="pk" class="btn btn-primary">🔨 Ajustar PRIMARY KEY Selecionadas</button>
            <button type="submit" name="action" value="ai" class="btn btn-warning">⚙️ Ajustar AUTO_INCREMENT Selecionadas</button>
            <button type="button" class="btn btn-success" onclick="selectAllAndSubmit('pk')">⚡ Ajustar PRIMARY KEY Todas</button>
            <button type="button" class="btn btn-success" onclick="selectAllAndSubmit('ai')">⚡ Ajustar AUTO_INCREMENT Todas</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('checkAll').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="selected[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
function selectAllAndSubmit(action) {
    document.querySelectorAll('input[type="checkbox"][name="selected[]"]').forEach(cb => cb.checked = true);
    const form = document.forms[0];
    const hiddenAction = document.createElement('input');
    hiddenAction.type = 'hidden';
    hiddenAction.name = 'action';
    hiddenAction.value = action;
    form.appendChild(hiddenAction);
    form.submit();
}
</script>
</body>
</html>
