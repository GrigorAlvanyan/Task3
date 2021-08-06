<?php

define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . '/../helpers.php';
require_once ROOT_DIR . '/telnet/TelnetClient.php';
require_once ROOT_DIR . '/telnet/Client.php';
require_once ROOT_DIR . '/app/functions.php';
$configs = include ROOT_DIR . '/../config.php';


if(isset($_GET['eoc_ip']) && !empty($_GET['eoc_ip'])) {
    $eoc_ip = $_GET['eoc_ip'];
} else {
    die("Invalid IP address");
}

$clientOld = telnetConnection($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);
$clientNew = new \PhpTelnet\Client($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);

if (isset($_GET['restart']) && $_GET['restart']) {
//    $client = new \PhpTelnet\Client($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);

    $clientNew->connect();
    $su = $clientNew->execute('su');
    $su = $clientNew->execute($configs['telnet_params']['super_user_password']);
//

//    $uci = $clientNew->execute( 'uci show network.wan1.ifname');
//    $eth0 = substr($uci[1],strpos($uci[1], 'eth0'));
//    $eth0 = 'luci-bwc -i'.' '.$eth0;
////
////
//    $uci = $clientNew->execute($eth0);
//    $uciLines = linesRemove($uci);
//    $uci = json_encode($uciLines);
//    dd($uci);die;

//    $uciLines = isset($uciLines) && !empty($uciLines) ? $uciLines : [];
//    $reboot = $clientNew->execute( 'reboot');
//     die;
    $clientNew->disconnect('');
}

//
//if (isset($_GET['getTraffic']) && $_GET['getTraffic']) {
//
//
//    $clientNew->connect();
//    $su = $clientNew->execute('su');
//    $su = $clientNew->execute($configs['telnet_params']['super_user_password']);
//
//    $uci = $clientNew->execute('uci show network.wan1.ifname');
//    $eth0Val = substr($uci[1], strpos($uci[1], 'eth0'));
//    $eth0 = 'luci-bwc -i' . ' ' . $eth0Val;
//
//
//    $uci = $clientNew->execute($eth0);
//    $uciLines = linesRemove($uci);
//    $uci = isset($uci) && !empty($uci) ? $uci : [];
//    $k = $uciLines;
//    $k = json_encode(array_values($uciLines));
//    $k = str_replace('"', '', $k);
//    $k = str_replace(',,', ',', $k);
//
//    echo $k;
//
//}

$command = 'iwinfo wlan0 assoclist';
$cmdResult = $clientOld->exec($command);
$cmdResults = linesRemove($cmdResult);

$associatedTable = getAssociatedStations($cmdResults);
$associatedLines = isset($associatedTable) && !empty($associatedTable) ? $associatedTable : [];

$command = 'iwinfo';
$iwinfoResult = $clientOld->exec($command);
$iwinfoResults = linesRemove($iwinfoResult);
$wireless = getWireless($iwinfoResults);
$wireless = isset($wireless) && !empty($wireless) ? $wireless : [];


$command = 'cat /tmp/dhcp.leases';
$dhcpResult = $clientOld->exec($command);
$dhcpResults = linesRemove($dhcpResult);
$dhcpResultArr = getDhcpLeases($dhcpResults);
$dhcpResultArr = isset($dhcpResultArr) && !empty($dhcpResultArr) ?  $dhcpResultArr : [];


$nameOfMacAddress = nameOfMacAddress($associatedLines, $dhcpResultArr);
$nameOfMacAddress = isset($nameOfMacAddress) && !empty($nameOfMacAddress) ? $nameOfMacAddress : [];


$command = 'uptime';
$uptimeResult = $clientOld->exec($command);
$uptimeResult = linesRemove($uptimeResult);
$uptimeResultLine = getUptime($uptimeResult);
$uptimeResultLine = isset($uptimeResultLine) && !empty($uptimeResultLine) ? $uptimeResultLine : [];


$command = 'date';
$dateResult = $clientOld->exec($command);
$dateResults = linesRemove($dateResult);
$localTimeResultLine = getLocalTime($dateResults);
$localTimeResultLine = isset($localTimeResultLine) && !empty($localTimeResultLine) ? $localTimeResultLine : [];


$qualitySignal = getQualitySignal($wireless);
$qualitySignal = isset($qualitySignal) && !empty($qualitySignal) ? $qualitySignal : '';

$command = 'getinfo -fw';
$firmwareVersion = $clientOld->exec($command);
$firmwareVersion = getFirmwareVersion($firmwareVersion);
$firmwareVersion = isset($firmwareVersion) && !empty($firmwareVersion) ? $firmwareVersion : '';

$command = 'cat /tmp/sysinfo/model';
$model = $clientOld->exec($command);
$model = linesRemove($model);
$modelResult = getModel($model);
$modelResult = isset($modelResult) && !empty($modelResult) ? $modelResult : '';

//$command = 'getinfo -sn';
//$model = $telnet->exec($command);
////dd($model);die;
$clientOld->disconnect();

//
$clientNew->connect();
$su = $clientNew->execute('su');
$su = $clientNew->execute($configs['telnet_params']['super_user_password']);

$command = 'ifstatus wan1';
$network = $clientNew->execute($command);
$network = linesRemove($network);
$network = json_decode(implode('', $network), 1);
$networks = getNetWork($network);
$networks = isset($networks) && !empty($networks) ? $networks : [];

?>

<?php include ROOT_DIR . '/views/tables.php'?>
