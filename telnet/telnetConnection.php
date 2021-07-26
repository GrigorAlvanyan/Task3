<?php

require_once(__DIR__ . '/telnet/TelnetClient.php');
$configs = include '../../config.php';


use TelnetClient\TelnetClient;

if(isset($_GET['eoc_ip']) && !empty($_GET['eoc_ip'])) {
    if($configs['db_params']['host'] == 'localhost') {
        $eoc_ip = $_GET['eoc_ip'];
    } else {
        $eoc_ip = str_replace(';','<br>',trim(filter($row[15]),';'));
    }
} else {
    die("Invalid IP address");
}

$telnet = new TelnetClient($eoc_ip, $configs['telnet_params']['port']);
$telnet->connect();
$telnet->setPrompt('$');
$telnet->login($configs['telnet_params']['username'], $configs['telnet_params']['password']);