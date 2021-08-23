<?php

define('ROOT_DIR', __DIR__ );

require_once ROOT_DIR . '/../helpers.php';
require_once ROOT_DIR . '/telnet/TelnetClient.php';
require_once ROOT_DIR . '/telnet/Client.php';
require_once ROOT_DIR . '/app/functions.php';
$configs = include ROOT_DIR . '/../config.php';

$invalidIp = $configs['error_messages']['invalid_ip_address'];
$telnetUsername = $configs['telnet_params']['username'];
$superUserLogin = $configs['telnet_params']['super_user_login'];



$eoc_ip = isset($_GET['eoc_ip']) && !empty($_GET['eoc_ip']) ? $_GET['eoc_ip'] : '';

$ip = validateIp($eoc_ip);

if ($ip === true) {

    $clientOld = telnetConnection($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);
    $clientNew = new \PhpTelnet\Client($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);

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


    $wirelesSig = wirelesSignalSum($associatedLines);
    $wirelesSig = isset($wirelesSig) && !empty($wirelesSig) ? $wirelesSig : '';

    $command = 'cat /tmp/dhcp.leases';
    $dhcpResult = $clientOld->exec($command);
    $dhcpResults = linesRemove($dhcpResult);
    $dhcpResultArr = getDhcpLeases($dhcpResults);
    $dhcpResultArr = isset($dhcpResultArr) && !empty($dhcpResultArr) ? $dhcpResultArr : [];


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
    $firmwareVersion = getFirmwareVersion($firmwareVersion, $telnetUsername);

    $firmwareVersion = isset($firmwareVersion) && !empty($firmwareVersion) ? $firmwareVersion : '';

    $command = 'cat /tmp/sysinfo/model';
    $model = $clientOld->exec($command);
    $model = linesRemove($model);
    $modelResult = getModel($model);
    $modelResult = isset($modelResult) && !empty($modelResult) ? $modelResult : '';

    $clientOld->disconnect();

    $clientNew->connect();
    $su = $clientNew->execute('su',200000);
    $su = $clientNew->execute($configs['telnet_params']['super_user_password'],200000);

    $command = 'getinfo -hardware';
    $hardware = $clientNew->execute($command,200000);
    $hardwareVersion = getHardwareVersion($hardware, $superUserLogin);
    $hardwareVersion = isset($hardwareVersion) && !empty($hardwareVersion) ? $hardwareVersion : [];

    $command =  'getinfo -fwsw';
    $software = $clientNew->execute($command, 200000);
    $softwareVersion = getSoftwareVersion($software, $superUserLogin);
    $softwareVersion = isset($softwareVersion) && !empty($softwareVersion) ? $softwareVersion : [];


    $command =  'getinfo -sn';
    $serial = $clientNew->execute($command,200000);
    $serialNumber = getserialNumber($serial, $superUserLogin);
    $serialNumber = isset($serialNumber) && !empty($serialNumber) ? $serialNumber : [];


    $command = 'ifstatus wan1';
    $network = $clientNew->execute($command);
    $network = linesRemove($network);
    $network = json_decode(implode('', $network), 1);
    $networks = getNetWork($network);
    $networks = isset($networks) && !empty($networks) ? $networks : [];


    $command = 'swconfig dev switch0 show';
    $ports = $clientNew->execute($command,200000);
    $ports = linesRemove($ports);
    $portsInfoResult = portsInfo($ports);
    $portsInfoResult = isset($portsInfoResult) && !empty($portsInfoResult) ? $portsInfoResult : [];


    if (isset($_GET['restart']) && $_GET['restart']) {
        $reboot = $clientNew->execute( 'reboot');
    }

    include ROOT_DIR . '/views/tables.php';

} else {
    echo $invalidIp;
}