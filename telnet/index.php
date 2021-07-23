<?php

require_once 'app/functions.php';
require_once(__DIR__ . '/telnet/TelnetClient.php');

use TelnetClient\TelnetClient;

$telnet = new TelnetClient('****', 111);
$telnet->connect();
$telnet->setPrompt('$'); //setRegexPrompt() to use a regex
$telnet->login('****', '****');


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

$telnet->disconnect();
?>

<?php include 'views/tables.php'?>
