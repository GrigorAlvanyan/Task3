<?php

session_start();
$name = '';

function dd ($res){
    echo '<pre>';
    print_r($res);
    echo '</pre>';
}

$host = '10.1.1.41';
$user = 'root';
$password = '1234';
$dbname = 'netxms';
//$filteredNames = ['NodeUpTime', 'TV Laser', 'Temperature'];
$filteredNames = [];
$severityStatuses = [
    '0' => 'normal',
    '1' => 'warning',
    '2' => 'minor',
    '3' => 'major',
    '4'=> 'critical'
];

$connection =  mysqli_connect($host, $user, $password, $dbname);

if (!$connection) {
    echo "Error: Unable to connect to MySQL. Please check your DB connection parameters.\n";
    exit;
}

if(isset($_GET['name']) && !empty($_GET['name'])) {

    $name = $_GET['name'];
    $_SESSION['name'] = $name;

    function getResult($connection, $name, $filteredNames, $severityStatuses) {
        $date = new DateTime();
        $today = $date->format('d-m-Y');

        if ($stmt = $connection->prepare("SELECT name,object_id,status FROM object_properties WHERE name=?")) {
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();
            $objectProperties = $result->fetch_assoc();

            $stmt->close();
        } else {
            return ['error' =>  $name . ' not found'];
        }

        $sqlStatus = "SELECT  DATE_FORMAT(FROM_UNIXTIME(MAX(`event_timestamp`)), '%H:%i:%s %e-%m-%Y') as event_timestamp, `event_name`, `event_code`,`severity`,`message` FROM alarm_events 
        WHERE source_object_id = {$objectProperties['object_id']} GROUP BY severity";


        if ($resultStatus = $connection->query($sqlStatus)) {
            $resultStatus = $resultStatus->fetch_all(MYSQLI_ASSOC);

            foreach ($resultStatus as &$severity){

                $eventTime = $severity['event_timestamp'];
                $eventTimeExplode = explode(' ', trim($eventTime));

                if(isset($eventTimeExplode[1]) && $eventTimeExplode[1] == $today){
                    $severity['event_timestamp'] = $eventTimeExplode[0];
                }

                $severity['severity_name'] =$severityStatuses[$severity['severity'] ];

            }
        } else {
            return ['error' => 'Result status not found'];
        }

        $objectId = $objectProperties['object_id'];

        if(count($filteredNames) !== 0){
            $filteredNames = "'" . implode("','", $filteredNames) . "'";
            $sqlItem = "SELECT * FROM items WHERE node_id = $objectId AND `description` IN ($filteredNames)";
        }else {
            $sqlItem = "SELECT * FROM items WHERE node_id = $objectId ";
        }

        if ($resultItems = $connection->query($sqlItem)) {
            $resultItems = $resultItems->fetch_all(MYSQLI_ASSOC);
        } else {
            return ['error' => 'Result items not found'];
        }

        $itemIds = [];

        foreach ($resultItems as $resultItem) {

            $itemIds[] = $resultItem['item_id'];
        }

        $idataTable = 'idata_'.$objectProperties['object_id'];

       // $itemIds = implode(',', $itemIds);


        $resultValuesData = [];
foreach ($itemIds as $itemId){


    $sqlValue = "SELECT {$idataTable}.item_id, 
                        DATE_FORMAT(FROM_UNIXTIME(`idata_timestamp`), '%H:%i:%s %e-%m-%Y') as idata_timestamp, 
                        `idata_value`, 
                        `raw_value`,
                        `template_id`, 
                        `template_item_id`, 
                        `guid`, 
                        `name` , 
                        `description`
        FROM {$idataTable} 
        INNER JOIN items ON {$idataTable}.item_id = items.item_id 
        WHERE {$idataTable}.item_id ={$itemId} 
        ORDER BY {$idataTable}.idata_timestamp DESC
        LIMIT 1 ";

    if ($resultValues = $connection->query($sqlValue)) {

            $result = $resultValues->fetch_assoc();
            if($result){
                $eventTime = $result['idata_timestamp'];
                $eventTimeExplode = explode(' ', trim($eventTime));

                if(isset($eventTimeExplode[1]) && $eventTimeExplode[1] == $today){
                    $result['idata_timestamp'] = $eventTimeExplode[0];
                }
                $resultValuesData[$result['item_id']] = $result;
            }

    } else {
        return ['error' => 'Result values not found' , '_mysql_error'=> $connection->error];
    }

}

        return ['object_properties'=>$objectProperties,'alarm_events'=>$resultStatus,'idata'=>$resultValuesData];
    }

    $results = getResult($connection, $name, $filteredNames, $severityStatuses);

    if (isset($results['error'])) {
        echo $results['error'];
    } else {
        dd($results);
    }

}

$connection->close();

?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link rel="stylesheet"  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <script  src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <title>Document</title>
</head>
<body>

    <section class="jumbotron text-center">
        <div class="container">
            <h1 class="jumbotron-heading">Search byName</h1>

            <div class="col-md-12">
                <form class="form-row row justify-content-md-center" action="" method="GET">
                    <div class="col-lg-6">
                        <input type="text" name="name" placeholder="Search" class="form-control mr-sm-2">
                    </div>
                    <div class="col-lg-2">
                        <button class="btn btn-outline-success w-100" type="submit">Search</button>
                    </div>
                </form>
            </div>

    <div class="row">
    <div class="col-lg-12">
    <?php if(isset($resultStatus) && count($resultStatus) > 0) : ?>
        <table class="table">
    <thead>
    <tr>
      <th scope="col">name</th>
      <th scope="col">id</th>
      <th scope="col">event_timestamp</th>
      <th scope="col">idata_value</th>
      <th scope="col">severity</th>

      <!-- <th scope="col">Expression</th> -->

    </tr>
    </thead>
    <tbody>

    <tr>

      <td><?= $resultArr['name']; ?></td>
      <td><?= $resultArr['id']; ?></td>
      <td><?= $resultArr['event_timestamp']; ?></td>
      <td><?= $resultArr['idata_value']; ?></td>
      <td><?= $resultArr['severity']; ?></td>
      <!-- <td><a href="<?php echo '?id='.$resultArr['id']?>" type="button" class="btn btn-primary" id="">click here</a></td> -->

    </tr>


  </tbody>
</table>
<?php endif; ?>
    </div>
    </div>
    </div>
    </section>

</body>
</html>

