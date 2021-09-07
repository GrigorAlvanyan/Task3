<?php

define('ROOT_DIR', __DIR__);

use Workerman\Worker;

require_once  ROOT_DIR . '/../vendor/autoload.php';



$clientId = isset($_GET['cli_id']) && !empty($_GET['cli_id']) ? $_GET['cli_id'] : "{ROOT_DIR}Incoming call: 095399337 , Time: 18:50";
$operatorId = isset($_GET['operator_id']) && !empty($_GET['operator_id']) ? $_GET['operator_id'] : '';

$clientId [] = ROOT_DIR . 'Incoming call: 095399337 , Time: 18:50';

if (!empty($clientId)) {
    $info['client_id'] = $clientId;
    if (!empty($operatorId)) {
        $info['operator_id'] = $operatorId;
    }
}


$ws_worker = new Worker("websocket://0.0.0.0:2346");

$ws_worker->count = 4;

$ws_worker->onConnect = function($connection)
{
    $connection->onWebSocketConnect = function($connection)
    {
        echo "New connection\n";
    };
};

$ws_worker->onMessage = function($connection, $data) use ($clientId)
{
    $connection->send($clientId);
};

$ws_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();

?>
