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


$command = 'iwinfo wlan0 assoclist';
$cmdResult = $telnet->execute($command);
$cmdResults = linesRemove($cmdResult);
$associatedTable = getAssociatedStations($cmdResults);
$associatedLines = isset($associatedTable) && !empty($associatedTable) ? $associatedTable : [];


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



$su = $telnet->execute('su');
$su = $telnet->execute($configs['telnet_params']['super_user_password']);
$command = 'ifstatus wan1';
$network = $telnet->execute($command);
$network = linesRemove($network);
$network = json_decode(implode('', $network), 1);
$networks = getNetWork($network);
$networks = isset($networks) && !empty($networks) ? $networks : [];




if (isset($_GET['restart']) && $_GET['restart']) {

//    $html=$client->execute('iwinfo wlan0 assoclist');
    $su = $telnet->execute('su');
    $su = $telnet->execute($configs['telnet_params']['super_user_password']);
//    $su=$telnet->execute("ls /");
//    $command = 'ifstatus wan1';
//    $network = $telnet->execute($command);
//    dd($network);
    $reboot = $telnet->execute( 'reboot');
echo '111';

//    $uci = $telnet->execute( 'uci show network.wan1.ifname');
//    $eth0 = substr($uci[1],strpos($uci[1], 'eth0'));
//    $eth0 = 'luci-bwc -i'.' '.$eth0;
//
//    $uci = $telnet->execute($eth0);
//    dd($uci);
//    die;
}
echo '222';;

$telnet->disconnect('');
?>

<?php include ROOT_DIR . '/views/tables.php'?>
