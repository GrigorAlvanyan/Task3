<?php

define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . '/../helpers.php';

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







//    $command = 'su';
//    $su = $telnet->su('q12kl79g');

//    $telnet->connect();
//    $telnet->setPrompt('$');
//    $su = $telnet->su('su', 'q12kl79g');


//    var_dump($su);die;
//    $cmdResult = $telnet->exec($command);
//    die;



//    $command = '"echo q12kl79g | /usr/bin/su -S reboot';
//
//    $suResult = $telnet->exec($command);

//    $telnet->setPrompt('Password: ');
//    $suResult = $telnet->exec('q12kl79g');

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
dd($uptimeResult);die;
$uptimeResult = linesRemove($uptimeResult);
$uptimeResultLine = getUptime($uptimeResult);
$uptimeResultLine = isset($uptimeResultLine) && !empty($uptimeResultLine) ? $uptimeResultLine : [];
//dd($uptimeResultLine);die;
//dd($uptimeResultLine);die;

$command = 'date';
$dateResult = $telnet->exec($command);
$dateResults = linesRemove($dateResult);
//dd($dateResults);die;
$localTimeResultLine = getLocalTime($dateResults);
$localTimeResultLine = isset($localTimeResultLine) && !empty($localTimeResultLine) ? $localTimeResultLine : [];

function getLocalTime($dateResults) {
    $line = explode(' ',$dateResults[1]);
    $lineSize = count($line);
    $lineSize -= 2;
    unset($line[$lineSize]);
    $line = implode(' ', $line);
    return $line;
}


" Thu Jul 29 15:26:40 AMT 2021";
" Thu Jul 29 15:27:20 2021";



$telnet->disconnect();
?>

<?php include ROOT_DIR . '/views/tables.php'?>
