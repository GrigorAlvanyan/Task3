<?php
define('ROOT_DIR', __DIR__);

use Workerman\Worker;

require_once  ROOT_DIR . '/vendor/autoload.php';



if(isset($_GET['client_id']) && !empty($_GET['client_id']) && isset($_GET['op_id']) && !empty($_GET['op_id'])) {
    $phoneNumber = $_GET['client_id'];
    $operatorNumber = $_GET['client_id'];
}

$phoneNumber = $_GET['client_id'];

$ws_worker = new Worker("websocket://0.0.0.0:12345");

$ws_worker->count = 4;

$ws_worker->onConnect = function($connection)
{
    $connection->onWebSocketConnect = function($connection)
    {
        echo "New connection\n";
    };
};

$ws_worker->onMessage = function($connection, $data) use ($phoneNumber)
{
    $connection->send($phoneNumber);
};

$ws_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();

?>








