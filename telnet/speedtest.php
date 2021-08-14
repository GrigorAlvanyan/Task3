<?php

define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . '/../helpers.php';
require_once ROOT_DIR . '/telnet/TelnetClient.php';
require_once ROOT_DIR . '/app/functions.php';
$configs = include ROOT_DIR . '/../config.php';
$invalidIp = $configs['error_messages']['invalid_ip_address'];

$eoc_ip = isset($_GET['eoc_ip']) && !empty($_GET['eoc_ip']) ? $_GET['eoc_ip'] : '';
$ip = validateIp($eoc_ip);
if ($ip === true) {

    $clientOld = telnetConnection($eoc_ip, $configs['telnet_params']['port'], $configs['telnet_params']['username'], $configs['telnet_params']['password']);

    $command = 'cd /tmp';
    $speedTest = $clientOld->exec($command);

    $command = 'wget http://212.183.159.230/20MB.zip';
    $speedTest = $clientOld->exec($command);
    $cmdResults = linesRemove($speedTest);
    dd($cmdResults);

    $command = 'rm 20MB.zip';
    $speedTest = $clientOld->exec($command);


} else {
    echo $invalidIp;
}
