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

//$telnet = telnetConnection($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);
$telnet = new \PhpTelnet\Client($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);
$telnet->connect();

if (isset($_GET['restart']) && $_GET['restart']) {
//    $html=$client->execute('iwinfo wlan0 assoclist');
    $su=$telnet->execute('su');
//    $html=$client->execute("su");
    $su=$telnet->execute($configs['telnet_params']['super_user_password']);
    $su=$telnet->execute("ls /");
dd($su);

//    $command = "cat /tmp/dhcp.leases";
//    $cmdResult = $telnet->exec($command);
//    dd($html);
//    dd($cmdResult);die;
////    dd($html);
//    $client->disconnect('');
//
//    die;

}


$command = 'iwinfo wlan0 assoclist';
$cmdResult = $telnet->execute($command);
$cmdResults = linesRemove($cmdResult);
$associatedTable = getAssociatedStations($cmdResults);
$associatedLines = isset($associatedTable) && !empty($associatedTable) ? $associatedTable : [];
//dd($associatedLines);die;

//$signal = getSignal($associatedLines);

$command = 'iwinfo';
$iwinfoResult = $telnet->execute($command);
$iwinfoResults = linesRemove($iwinfoResult);
$wireless = getWireless($iwinfoResults);
$wireless = isset($wireless) && !empty($wireless) ? $wireless : [];

$command = 'cat /tmp/dhcp.leases';
$dhcpResult = $telnet->execute($command);
$dhcpResults = linesRemove($dhcpResult);
$dhcpResultArr = getDhcpLeases($dhcpResults);
$dhcpResultArr = isset($dhcpResultArr) && !empty($dhcpResultArr) ?  $dhcpResultArr : [];

$nameOfMacAddress = nameOfMacAddress($associatedLines, $dhcpResultArr);
$nameOfMacAddress = isset($nameOfMacAddress) && !empty($nameOfMacAddress) ? $nameOfMacAddress : [];

$command = 'uptime';
$uptimeResult = $telnet->execute($command);
$uptimeResult = linesRemove($uptimeResult);
$uptimeResultLine = getUptime($uptimeResult);
$uptimeResultLine = isset($uptimeResultLine) && !empty($uptimeResultLine) ? $uptimeResultLine : [];

$command = 'date';
$dateResult = $telnet->execute($command);
$dateResults = linesRemove($dateResult);
$localTimeResultLine = getLocalTime($dateResults);
$localTimeResultLine = isset($localTimeResultLine) && !empty($localTimeResultLine) ? $localTimeResultLine : [];


" Thu Jul 29 15:26:40 AMT 2021";
" Thu Jul 29 15:27:20 2021";



$telnet->disconnect('');
?>

<?php include ROOT_DIR . '/views/tables.php'?>
