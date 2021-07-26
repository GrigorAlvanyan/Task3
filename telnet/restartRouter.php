<?php

require_once 'app/functions.php';
$configs = include '../config.php';

if(isset($_GET['eoc_ip']) && !empty($_GET['eoc_ip'])) {
    if($configs['db_params']['host'] == 'localhost') {
        $eoc_ip = $_GET['eoc_ip'];
    } else {
        $eoc_ip = str_replace(';','<br>',trim(filter($row[15]),';'));
    }
} else {
    die("Invalid IP address");
}

$telnet = telnetConnection($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);

$command = 'iwinfo wlan0 assoclist';
$cmdResult = $telnet->exec($command);
$cmdResults = linesRemove($cmdResult);
$associatedLines = getAssociatedStations($cmdResults);
$associatedLines = isset($associatedLines) && !empty($associatedLines) ? $associatedLines : [];