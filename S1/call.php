<?php



$clientId = isset($_GET['cli_id']) && !empty($_GET['cli_id']) ? $_GET['cli_id'] : '';
$operatorId = isset($_GET['operator_id']) && !empty($_GET['operator_id']) ? $_GET['operator_id'] : '';


echo($clientId);
echo '<br>';
echo($operatorId);



use Workerman\Worker;

require_once  __DIR__ . '/../vendor/autoload.php';

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


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<style>
    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
    }

    .unit {
        position: relative;
        width: 60px;
        height: 60px;
        border-radius: 100%;
        background-color: green;
        border: 3px solid black;
        top: 0;
        left: 0;
    }

</style>
<body id="wrapper">
<div class="unit" id="unit"></div>

<script src="app.js"></script>
</body>
</html>



