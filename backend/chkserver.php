<?php
session_start();

// CONFIGURAÇÃO DE LOGIN
$usuario = 'admin';
$senha = '1234';

// CHECAGEM DE LOGIN
if (!isset($_SESSION['logado'])) {
    if (isset($_POST['usuario']) && isset($_POST['senha'])) {
        if ($_POST['usuario'] === $usuario && $_POST['senha'] === $senha) {
            $_SESSION['logado'] = true;
            header('Location: '.$_SERVER['PHP_SELF']);
            exit;
        } else {
            $erro = "Usuário ou senha incorretos!";
        }
    }

    // FORMULÁRIO DE LOGIN
    echo '<form method="POST">
            <h2>Login do Painel</h2>
            <input type="text" name="usuario" placeholder="Usuário" required><br>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <button type="submit">Entrar</button>
            '.(isset($erro) ? '<p style="color:red;">'.$erro.'</p>' : '').'
          </form>';
    exit;
}

// INICIO DO BUFFER PARA CAPTURAR A SAÍDA
ob_start();

// SCRIPT ORIGINAL AQUI
echo "DOCUMENT_ROOT: ".$_SERVER['DOCUMENT_ROOT']."\n";
echo 'PHP version: '. phpversion()."\n";

// CHECK SERVERS
chkServer('gateway.sandbox.push.apple.com',2195);
chkServer('smtp.mailgun.org',465);
chkServer('smtp.mailgun.org',587);

// DISK SPACE
$free = disk_free_space("/");
$total = disk_total_space("/");
$percent = ($free/$total) * 100;
echo "\nTotal Space GB: ".isa_bytes_to_gb($total);
echo "\nFree Space GB: ".isa_bytes_to_gb($free);

// PROCESSOR
$ncpu = 1;
if(is_file('/proc/cpuinfo')) {
    $cpuinfo = file_get_contents('/proc/cpuinfo');
    preg_match_all('/^processor/m', $cpuinfo, $matches);
    $ncpu = count($matches[0]);
}
echo "\nNumber of Processors: ".$ncpu;

// RAM
$fh = fopen('/proc/meminfo','r');
$mem = 0;
while ($line = fgets($fh)) {
    $pieces = array();
    if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
      $mem = $pieces[1];
      break;
    }
}
fclose($fh);
$mem=$mem/(1024*1024);
$mem=round($mem,0);
echo "\nRAM: $mem GB";

// CURL & SSL
$curl_version = curl_version();
echo "\ncurl version: " . $curl_version["version"];
echo "\nSSL version: " . $curl_version["ssl_version"];

// EXTENSIONS
$exts = ['ionCube Loader', 'mbstring', 'curl', 'mysql', 'mysqli'];
foreach ($exts as $ext) {
    echo "\nExtension $ext: " . (extension_loaded($ext) ? 'INSTALLED' : 'NOT INSTALLED');
}

// CONFIGS
echo "\nallow_url_fopen: " . (ini_get('allow_url_fopen') ? 'ON' : 'OFF');
echo "\nshort_open_tag: " . (ini_get('short_open_tag') ? 'ON' : 'OFF');

// CAPTURA E SALVA NO LOG
$output = ob_get_clean();
$logfile = __DIR__.'/server_check_log.txt';
file_put_contents($logfile, date('[Y-m-d H:i:s] ').$output."\n\n", FILE_APPEND);

// MOSTRA LINK PARA DOWNLOAD DO LOG
echo "<h3>Check concluído!</h3>";
echo "<p>Resultados salvos em <a href='server_check_log.txt' target='_blank'>server_check_log.txt</a></p>";

// FUNÇÕES AUXILIARES
function isa_bytes_to_gb($bytes, $decimal_places = 1){
    return number_format($bytes / 1073741824, $decimal_places);
}

function chkServer($host, $port){
    $hostip = @gethostbyname($host);
    if ($hostip == $host) {
        echo "\n$host: DOWN";
    } else {
        $x = @fsockopen($hostip, $port, $errno, $errstr, 5);
        echo "\n$host:$port is " . ($x ? 'OPEN' : 'CLOSED');
        if ($x) { @fclose($x); }
    }
}
?>
