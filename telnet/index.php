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

$telnet = telnetConnection($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);


if (isset($_GET['restart']) && $_GET['restart']) {
    $client = new \PhpTelnet\Client($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);

    $client->connect();
    $su = $client->execute('su');
    $su = $client->execute($configs['telnet_params']['super_user_password']);
//    $su = $client->execute("ls /");
//    dd($su);

    $uci = $client->execute( 'uci show network.wan1.ifname');
    $eth0 = substr($uci[1],strpos($uci[1], 'eth0'));
    $eth0 = 'luci-bwc -i'.' '.$eth0;

//    $reboot = $client->execute( 'reboot');
    $uci = $client->execute($eth0);
    $uciLines = linesRemove($uci);
    $uciLines = isset($uciLines) && !empty($uciLines) ? $uciLines : [];


    $client->disconnect('');
}

$command = 'iwinfo wlan0 assoclist';
$cmdResult = $telnet->exec($command);
$cmdResults = linesRemove($cmdResult);
$associatedTable = getAssociatedStations($cmdResults);
$associatedLines = isset($associatedTable) && !empty($associatedTable) ? $associatedTable : [];


$command = 'iwinfo';
$iwinfoResult = $telnet->exec($command);
$iwinfoResults = linesRemove($iwinfoResult);
$wireless = getWireless($iwinfoResults);
$wireless = isset($wireless) && !empty($wireless) ? $wireless : [];


$command = 'cat /tmp/dhcp.leases';
$dhcpResult = $telnet->exec($command);
$dhcpResults = linesRemove($dhcpResult);
$dhcpResultArr = getDhcpLeases($dhcpResults);
$dhcpResultArr = isset($dhcpResultArr) && !empty($dhcpResultArr) ?  $dhcpResultArr : [];


$nameOfMacAddress = nameOfMacAddress($associatedLines, $dhcpResultArr);
$nameOfMacAddress = isset($nameOfMacAddress) && !empty($nameOfMacAddress) ? $nameOfMacAddress : [];


$command = 'uptime';
$uptimeResult = $telnet->exec($command);
$uptimeResult = linesRemove($uptimeResult);
$uptimeResultLine = getUptime($uptimeResult);
$uptimeResultLine = isset($uptimeResultLine) && !empty($uptimeResultLine) ? $uptimeResultLine : [];


$command = 'date';
$dateResult = $telnet->exec($command);
$dateResults = linesRemove($dateResult);
$localTimeResultLine = getLocalTime($dateResults);
$localTimeResultLine = isset($localTimeResultLine) && !empty($localTimeResultLine) ? $localTimeResultLine : [];


$signal = getSignal($associatedLines);


$telnet->disconnect();

?>

<?php include ROOT_DIR . '/views/tables.php'?>
