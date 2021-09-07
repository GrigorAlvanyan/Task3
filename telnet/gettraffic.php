 <?php

define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . '/../helpers.php';
require_once ROOT_DIR . '/telnet/Client.php';
require_once ROOT_DIR . '/app/functions.php';
$configs = include ROOT_DIR . '/../config.php';


$eoc_ip = isset($_GET['eoc_ip']) && !empty($_GET['eoc_ip']) ? $_GET['eoc_ip'] : '' ;

$clientNew = new \PhpTelnet\Client($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);



//todo need to change CLient.php (timeout)
$clientNew->connect();
$su = $clientNew->exec('su');
$su = $clientNew->exec($configs['telnet_params']['super_user_password']);

$uci = $clientNew->execute('uci show network.wan1.ifname');
$eth0Val = substr($uci[1], strpos($uci[1], 'eth0'));
$eth0 = 'luci-bwc -i' . ' ' . $eth0Val;

$uci = $clientNew->execute($eth0);

$uciLines = lineRemove($uci);
$uci = isset($uci) && !empty($uci) ? $uci : [];
$k = $uciLines;
$k = json_encode(array_values($uciLines));
$k = str_replace('"', '', $k);
$k = str_replace(',,', ',', $k);




echo $k;


