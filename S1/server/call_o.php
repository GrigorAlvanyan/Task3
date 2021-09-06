<?php

use Workerman\Worker;

require_once  __DIR__ . '/../vendor/autoload.php';

$info = [];

$clientId = isset($_GET['cli_id']) && !empty($_GET['cli_id']) ? $_GET['cli_id'] : '';
$operatorId = isset($_GET['operator_id']) && !empty($_GET['operator_id']) ? $_GET['operator_id'] : '';


if (!empty($clientId)) {
    $info['client_id'] = $clientId;
    if (!empty($operatorId)) {
        $info['operator_id'] = $operatorId;
    }
}
/*
echo '<pre>';
print_r($info);
echo '</pre>';*/


$wsWorker = new Worker('websocket://0.0.0.0:2346');

$wsWorker->count = 4;

$wsWorker->onConnect = function ($connection) {
    echo "New connection \n";
};

//$wsWorker->onMessage = function ($connection, $data) use ($wsWorker) {
//    foreach($wsWorker->connections as $clientConnection) {
////        echo '<pre>';
////        print_r($clientConnection);
//        $clientConnection->send($data);
//    }
//};


$wsWorker->onMessage = function($connection, $data)
{
    var_dump($_GET, $_POST, $_COOKIE, $_SESSION, $_SERVER);
    // Send hello $data
    $connection->send("hello $data");
};



//echo '<pre>';
//print_r($wsWorker->onMessage);
//echo '</pre>';

$wsWorker->onClose = function ($connection) {
    echo "Connection closed\n";
};

Worker::runAll();


?>
