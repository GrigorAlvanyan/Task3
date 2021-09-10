
<?php
error_reporting(E_ALL);
define('ROOT_DIR', __DIR__);



$host    = "127.0.0.11";
$port    = 25003;
$message = "Hello Server";
//echo "Message To server :".$message;
// create socket

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Could not create socket\n");

$result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");
// send string to server
socket_write($socket, $message, strlen($message)) or die("Could not send data to server\n");
// get server response
//$result = socket_read ($socket, 1024) or die("Could not read server response\n");
//$serverResult = json_decode($result, 1);
//    $accept = socket_accept($socket);


//do {
    $result = socket_read ($socket, 1024) or die("Could not read server response\n");
//    $result = socket_read ($socket, 1024);
    $serverResult = json_decode($result, 1);

//    $accept = socket_accept($socket);
//    $read   = socket_read($accept, 1024) or die("Could not read input\n");


//} while (true);

echo '<pre>';
print_r($serverResult);
echo '</pre>';

$clientId = '';
if (isset($serverResult['clid']) && !empty($serverResult['clid'])) {
    $clientId = $serverResult['clid'];
}

//echo "Reply From Server  : <br>";
//echo '<pre>';
//print_r(json_decode($result, 1));
// close socket
//socket_close($socket);

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
                    Incoming call: <a  id="phone" href="http://localhost/task_3/index.php?<?=$clientId?>"><?=$clientId?></a>, Time: 18:50
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






