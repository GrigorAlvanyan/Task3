<?php

define('ROOT_DIR', __DIR__);


require_once ROOT_DIR . '/helpers.php';
$configs = getConfigs();


//
//$connection = mysqli_connect($configs['db_params']['host'], $configs['db_params']['user'], $configs['db_params']['password'], $configs['db_params']['db_name']);
//
//if (!$connection) {
//    echo "Error: Unable to connect to MySQL. Please check your DB connection parameters.\n";
//    exit;
//}


//$connection->query("DROP TABLE IF EXISTS date");
//
//$sql = "CREATE TABLE IF NOT EXISTS date(
//`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
//`timestamp` INT NOT NULL,
//`call_id` INT NOT NULL,
//`input` INT NOT NULL,
//`client_id` INT NOT NULL,
//`operator_id` INT NOT NULL,
//`bill_sec` INT NOT NULL,
//`start_time` TIMESTAMP NULL,
//`finish_time` TIMESTAMP NULL,
//`end_time` TIMESTAMP NULL,
//`state` varchar(255) NOT NULL
//)";

//$createTable = $connection->query($sql);
$logsPath = 'files/';
$logName = '26-08-21.log';
$log = $logsPath . $logName;

$file = file_put_contents(date('26-08-21').".log",
    date('H:i:s d.m.y')." ".file_get_contents("php://input")."GET ->".
    print_r($_GET,1)." POST ->". print_r($_POST,1)."\n",FILE_APPEND);
dd($file);die;
$file = file($log);

function validateDate($date, $format = 'd.m.y')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

$otherLines = '';
foreach ($file as $key => $line) {
    $lineArr = explode(' ', $line);
    if (isset($lineArr[1]) && validateDate($lineArr[1])) {

        $logDate = $lineArr[1];
        $json = json_decode($lineArr[2], 1);

        if (isset($json['state']) && $json['state'] == 'start') {

            $state = $json['state'];
            $uid = $json['uid'];
            $uid = explode('.', $uid);
            $timestamp = $uid[0];
            $lid = $json['lid'];
            $lid = explode('.', $lid);
            $callId = $lid[0];
            $input = $json['input'];
            $clid = $json['clid'];
            $operatorId = $json['op'];
            $billsec = $json['billsec'];
            $startTime = date('Y-m-d H:i:s');

            $sql = "INSERT INTO date(`timestamp`, `call_id`, `input`, `client_id`,`operator_id`,
                    `bill_sec`, `start_time`, `state`) VALUES
                    ('" . $timestamp . "','" . $callId . "','" . $input . "','" . $clid . "','" . $operatorId . "','" . $billsec . "',
                    '" . $startTime . "','" . $state . "')";
            $connection->query($sql);
        } elseif (isset($json['state']) && $json['state'] == 'end') {

            $states = $json['state'];
            $uids = $json['uid'];
            $uids = explode('.', $uids);
            $timestamps = $uids[0];
            $lids = $json['lid'];
            $lids = explode('.', $lids);
            $callIds = $lids[0];
            $inputs = $json['input'];
            $clids = $json['clid'];
            $operatorIds = $json['op'];
            $billsecs = $json['billsec'];
            $endTimes = date('Y-m-d H:i:s');

            $sql = "UPDATE date SET `timestamp`='$timestamps',`call_id`='$callIds',`input`='$inputs',`client_id`='$clids',
                                `operator_id`='$operatorIds',`bill_sec`='$billsecs',`end_time`='$endTimes',`state`='$states' WHERE  `client_id` = $clids";
            $connection->query($sql);

        }
    } else {
        if (strpos($line, 'call') !== false) {
            $callValue = explode('=>', $line);

            if (isset($callValue[1]) && trim($callValue[1]) == 'finish') {
                if (isset($file[$key+1])) {

                    $lidLine = explode('=>', $file[$key+1]);
                    $lidValue = isset($lidLine[1]) ? trim($lidLine[1]) : null;
                    $lidValue = explode('.',$lidValue);
                    $finishTime = date('Y-m-d H:i:s');
                    $sql = "UPDATE date SET `finish_time`='$finishTime' WHERE  `call_id` = $lidValue[0]";
                    $connection->query($sql);
                }
             }

        }
    }
}

