<?php

define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . '/telnet/TelnetClient.php';
require_once ROOT_DIR . '/app/functions.php';
$configs = include ROOT_DIR . '/../config.php';

if(isset($_GET['eoc_ip']) && !empty($_GET['eoc_ip'])) {
    $eoc_ip = $_GET['eoc_ip'];
} else {
    die("Invalid IP address");
}

$telnet = telnetConnection($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);

if (isset($_GET['restart']) && $_GET['restart']) {
    $command = 'reboot';
    $rebootResult = $telnet->exec($command);
}


$command = 'iwinfo wlan0 assoclist';
$cmdResult = $telnet->exec($command);
$cmdResults = linesRemove($cmdResult);
$associatedLines = getAssociatedStations($cmdResults);
$associatedLines = isset($associatedLines) && !empty($associatedLines) ? $associatedLines : [];

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
//$nameOfMacAddress = nameOfMacAddress($dhcpResultArr);
$nameOfMacAddress = isset($nameOfMacAddress) && !empty($nameOfMacAddress) ? $nameOfMacAddress : [];

$command = 'uptime';
$uptimeResult = $telnet->exec($command);
$uptimeResult = linesRemove($uptimeResult);
$uptimeResultLine = getUptime($uptimeResult);


$command = 'date';
$dateResult = $telnet->exec($command);
$dateResults = linesRemove($dateResult);

//todo needs refactoring
function getUptime($uptimeResult)
{
//    $upditeValues = [];
//    $uptimeResultvalues = explode(', ',$uptimeResult[1]);
//    $dayValue = explode(' ', ltrim($uptimeResultvalues[0]));
//
//    unset($dayValue[0]);
//    unset($dayValue[1]);
//    dd($dayValue);die;
//    $dayValue[3] = 'd';
//    $dayValue = implode('',$dayValue);
//    $upditeValues['day'] = $dayValue;
//    if(strpos($uptimeResultvalues[1], ':')){
//        $hourMinut = explode(':', $uptimeResultvalues[1]);
//        $hourMinut[0] = $hourMinut[0] . 'h';
//        $hourMinut[1] = $hourMinut[1] . 'm';
//        $dateValue = implode(' ', $hourMinut);
//    } else {
//        $hourMinut = explode(' ', $uptimeResultvalues[1]);
//        $hourMinut[0] = '0h'.' '.$hourMinut[0];
//        $hourMinut[1] = 'm';
//        $dateValue = implode('', $hourMinut);
//
//    }
//    $dayValue =  $dayValue .' '.$dateValue ;
//
//    return $dayValue;
}





$telnet->disconnect();
?>

<?php include ROOT_DIR . '/views/tables.php'?>
