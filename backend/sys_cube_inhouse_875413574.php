<?php
session_start();

// CONFIGURAÇÃO DO LOGIN
$usuario_permitido = 'admin';
$senha_permitida = '1234';

// CHECAGEM DE LOGIN
if (!isset($_SESSION['logado'])) {
    if (isset($_POST['usuario']) && isset($_POST['senha'])) {
        if ($_POST['usuario'] === $usuario_permitido && $_POST['senha'] === $senha_permitida) {
            $_SESSION['logado'] = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $erro = "Usuário ou senha inválidos!";
        }
    }

    // FORMULÁRIO DE LOGIN
    echo '<form method="POST" style="max-width:300px;margin:100px auto;font-family:sans-serif;">
            <h2>Login Painel IP</h2>
            <input type="text" name="usuario" placeholder="Usuário" required style="width:100%;padding:8px;margin-bottom:10px;"><br>
            <input type="password" name="senha" placeholder="Senha" required style="width:100%;padding:8px;margin-bottom:10px;"><br>
            <button type="submit" style="padding:10px 20px;">Entrar</button>
            '.(isset($erro) ? '<p style="color:red;">'.$erro.'</p>' : '').'
          </form>';
    exit;
}

// SE CHEGOU AQUI, ESTÁ LOGADO — CONTINUA O SCRIPT ORIGINAL
include 'common.php';

if (isset($_POST['ip_address'])) {
    $ip_address = $_POST['ip_address'];
    $VisitorIpApcKey = md5(str_replace(".", "_", $ip_address));

    if ($_POST['eStatus'] == "Allow") {
        $setSetupCacheData = $oCache->setData($VisitorIpApcKey, "Yes");
        echo "Ip address allowed.";
    } elseif ($_POST['eStatus'] == "Delete") {
        $setSetupCacheData = $oCache->delData($VisitorIpApcKey);
        echo "Ip address deleted.";
    } else {
        $setSetupCacheData = $oCache->setData($VisitorIpApcKey, "No");
        echo "Ip address disallowed.";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KingX</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        body {font-family: Arial, Helvetica, sans-serif;}
        .container {margin: 0 auto; max-width: 600px; padding: 100px;}
        input[type=text], select {width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; margin-top: 6px; margin-bottom: 16px;}
        button[type=button] {background-color: #04AA6D; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer;}
        button[type=button]:hover {background-color: #45a049;}
        h1 {text-align: center; margin: 0 0 50px 0;}
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage IP Address Access</h1>
        <label><strong>Enter IP Address: </strong></label>
        <input type="text" name="ip_address" id="ip_address" value="<?= get_client_ip() ?>">
        <select name="eStatus" id="eStatus">
            <option value="Allow">Allow</option>
            <option value="Disallow">Disallow</option>
            <option value="Delete">Delete</option>
        </select>
        <button type="button" id="submitForm">Submit</button>
        <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" style="margin-top:30px;">
            <button type="submit" name="logout" value="1" style="background:#d9534f;">Sair</button>
        </form>
    </div>

    <script>
        $('#submitForm').click(function() {
            var ipaddress_test = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
            var ipaddress = $("#ip_address").val();
            if (!ipaddress_test.test(ipaddress)) {
                alert("Ipaddress is invalid");
                return false;
            }
            $.ajax({
                type: 'POST',
                url: '<?= basename($_SERVER['PHP_SELF']) ?>',
                data: {ip_address: $('#ip_address').val(), eStatus: $('#eStatus').val()},
                success: function (response) {
                    alert(response);
                },
                error: function () {
                    alert("Erro na requisição.");
                }
            });
        });
    </script>
</body>
</html>

<?php
// LOGOUT
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
