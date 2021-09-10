<?php
error_reporting(E_ALL);
define('ROOT_DIR', __DIR__);

$input = json_decode(file_get_contents("php://input"), true);

$clientId = '';
if (isset($input['clid']) && !empty($input['clid'])) {
    $clientId = $input['clid'];
}

echo $clientId;

//$clientId = '095399337';




//echo $clientId;
//
//$ws_worker = new Worker("websocket://0.0.0.0:12345");
//
//$ws_worker->count = 4;
//
//$ws_worker->onConnect = function($connection)
//{
//    $connection->onWebSocketConnect = function($connection)
//    {
//        echo "New connection\n";
//    };
//};
//
//$ws_worker->onMessage = function($connection, $data) use ($clientId)
//{
//    $connection->send($clientId);
//};
//
//$ws_worker->onClose = function($connection)
//{
//    echo "Connection closed\n";
//};
//
//Worker::runAll();

?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <title>Document</title>
</head>
<body>



<?php if(!empty($clientId)): ?>
    <div class="modal show" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <a  id="phone" ><?=$clientId?></a>
                </div>
            </div>
        </div>
    </div>
    <script>
        $('#exampleModal').modal('show');
    </script>
<?php endif; ?>

<script src="script.js"></script>
</body>
</html>