<?php

function dd($res)
{
    echo '<pre>';
    print_r($res);
    echo '</pre>';
};

require_once 'app/functions.php';
require_once(__DIR__ . '/telnet/TelnetClient.php');
$configs = include '../config.php';

use TelnetClient\TelnetClient;

if(isset($_GET['eoc_ip']) && !empty($_GET['eoc_ip'])) {
    if($configs['db_params']['host'] == 'localhost') {
        $eoc_ip = $_GET['eoc_ip'];
    } else {
        $eoc_ip = str_replace(';','<br>',trim(filter($row[15]),';'));
    }
} else {
    // return error
}

$telnet = new TelnetClient($eoc_ip, $configs['telnet_params']['port']);
$telnet->connect();
$telnet->setPrompt('$'); //setRegexPrompt() to use a regex
$telnet->login($configs['telnet_params']['username'], $configs['telnet_params']['password']);


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
//dd($dateResults);die;
//$dateResultLine = getDate2($dateResults);
//dd
//dd($dateResultLine);die;


//function getDate2($dateLine)
//{
//    $dateValues = explode(' ', $dateLine[1]);
//    dd(count($dateValues));
////    die;
////    unset(count($dateValues) - 2);
//    $dateValues = impode('',$dateValues);
//    echo count($dateValues);
//    dd($dateValues);
//}

function getUptime($uptimeResult)
{
//dd($uptimeResult);die;
    $upditeValues = [];
    $uptimeResultvalues = explode(', ',$uptimeResult[1]);
    $dayValue = explode(' ', ltrim($uptimeResultvalues[0]));
    unset($dayValue[0]);
    unset($dayValue[1]);
    $dayValue[3] = 'd';
    $dayValue = implode('',$dayValue);
    $upditeValues['day'] = $dayValue;
    if(strpos($uptimeResultvalues[1], ':')){
        $hourMinut = explode(':', $uptimeResultvalues[1]);
        $hourMinut[0] = $hourMinut[0] . 'h';
        $hourMinut[1] = $hourMinut[1] . 'm';
        $dateValue = implode(' ', $hourMinut);
    } else {
        $hourMinut = explode(' ', $uptimeResultvalues[1]);
        $hourMinut[0] = '0h'.' '.$hourMinut[0];
        $hourMinut[1] = 'm';
        $dateValue = implode('', $hourMinut);

    }
    $dayValue =  $dayValue .' '.$dateValue ;
//    dd($dayValue);die;

    return $dayValue;
}




//dd($uptimeResultLine);die;


$telnet->disconnect();
?>

<?php include 'views/tables.php'?>
