<?php

use Workerman\Worker;

require_once  __DIR__ . '/../vendor/autoload.php';


$clientId = isset($_GET['cli_id']) && !empty($_GET['cli_id']) ? $_GET['cli_id'] : '';
$operatorId = isset($_GET['operator_id']) && !empty($_GET['operator_id']) ? $_GET['operator_id'] : '';




$wsWorker = new Worker('websocket://0.0.0.0:2346');

$wsWorker->count = 4;

$wsWorker->onConnect = function ($connection) {
    echo "New connection \n";
};

$wsWorker->onMessage = function ($connection, $data) use ($wsWorker) {
    foreach($wsWorker->connections as $clientConnection) {
        $clientConnection->send($data);
    }
};


$wsWorker->onClose = function ($connection) {
    echo "Connection closed\n";
};

Worker::runAll();


?>
