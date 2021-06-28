<?php
error_reporting(E_ALL);
//session_start();

function dd($res)
{
    echo '<pre>';
    print_r($res);
    echo '</pre>';
}

$url = $_SERVER['HTTP_HOST'];
if($url == 'localhost'){
    $host = 'localhost';
    $user = 'root';
    $password = '';

}else{
    $host = '10.1.1.41';
    $user = 'root';
    $password = '1234';
}

$dbname = 'netxms';
$nodeName = '';

//$filteredNames = ['NodeUpTime', 'TV Laser', 'Temperature'];
$filteredNames = [];
$severityStatuses = [
    '0' => 'normal',
    '1' => 'warning',
    '2' => 'minor',
    '3' => 'major',
    '4' => 'critical'
];

$connection = mysqli_connect($host, $user, $password, $dbname);
//dd($connection);die;

if (!$connection) {
    echo "Error: Unable to connect to MySQL. Please check your DB connection parameters.\n";
    exit;
}

if (isset($_GET['name']) && !empty($_GET['name'])) {

    $nodeName = $_GET['name'];
    $_SESSION['name'] = $nodeName;
}
else{
	$nodeName = $personalinfo[2];
}

function getResult($connection, $nodeName, $filteredNames, $severityStatuses)
    {
        $date = new DateTime();
        $today = $date->format('d-m-Y');

        if ($stmt = $connection->prepare("SELECT name,object_id,status FROM object_properties WHERE name=?")) {
            $stmt->bind_param("s", $nodeName);
            $stmt->execute();
            $result = $stmt->get_result();
            $objectProperties = $result->fetch_assoc();

            $stmt->close();
        } else {
            return ['error' => $nodeName . ' not found'];
        }

        $sqlStatus = "SELECT  DATE_FORMAT(FROM_UNIXTIME(MAX(`event_timestamp`)), '%H:%i:%s %e-%m-%Y') as event_timestamp,
                    `event_name`, 
                    `event_code`,
                    `severity`,
                    `message` 
        FROM alarm_events 
        WHERE source_object_id = {$objectProperties['object_id']}
        GROUP BY severity";

        if ($resultStatus = $connection->query($sqlStatus)) {
            $resultStatus = $resultStatus->fetch_all(MYSQLI_ASSOC);

            foreach ($resultStatus as &$severity) {

                $eventTime = $severity['event_timestamp'];
                $eventTimeExplode = explode(' ', trim($eventTime));

                if (isset($eventTimeExplode[1]) && $eventTimeExplode[1] == $today) {
                    $severity['event_timestamp'] = $eventTimeExplode[0];
                }

                $severity['severity_name'] = $severityStatuses[$severity['severity']];

            }
        } else {
//            return ['error' => 'Result status not found'];
        }

        $objectId = $objectProperties['object_id'];

        if (count($filteredNames) !== 0) {
            $filteredNames = "'" . implode("','", $filteredNames) . "'";
            $sqlItem = "SELECT * FROM items WHERE node_id = $objectId AND `description` IN ($filteredNames)";
        } else {
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

        $idataTable = 'idata_' . $objectProperties['object_id'];


        $resultValuesData = [];
        foreach ($itemIds as $itemId) {


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
                if ($result) {
                    $eventTime = $result['idata_timestamp'];
                    $eventTimeExplode = explode(' ', trim($eventTime));

                    if (isset($eventTimeExplode[1]) && $eventTimeExplode[1] == $today) {
                        $result['idata_timestamp'] = $eventTimeExplode[0];
                    }
                    $resultValuesData[$result['item_id']] = $result;
                }

            } else {
                return ['error' => 'Result values not found', '_mysql_error' => $connection->error];
            }

        }
        return ['object_properties' => $objectProperties, 'alarm_events' => $resultStatus, 'idata' => $resultValuesData];
    }

    $results = getResult($connection, $nodeName, $filteredNames, $severityStatuses);

    if (isset($results['error'])) {
        echo $results['error'];
    } else {
        //dd($results);
    }


$connection->close();


$severityMax = $results['alarm_events'] ?? [];
$max = 0;
$item = 0;
for ($i = 0; $i < count($severityMax); $i++) {

    if ($severityMax[$i]['severity'] > $max) {
        $max = $severityMax[$i]['severity'];
        $item = $i;
    } else {
        continue;
    }
}

$severityName = isset($severityMax[$item]) && isset($severityMax[$item]['severity_name']) ? $severityMax[$item]['severity_name'] : 'normal';


// 733 - Врем. доступ
				// 255 - Трафик
				// 256 - Ограничение
				// 257 - Тек. скорость
				// 258 - Суточный лимит
				// 259 - Месячный лимит
				// 260 - За день
				// 261 - За месяц
				// 262 - За всё время
				// 263 - Турбо
        echo "</table><table class='table_1'> 
		<tr>
		    <th colspan=2><b>Узел</b></th>			
		</tr>
		<tr>
			<td class='even_th' style='width:110px'>Имя:</td>
			<td colspan=2>".$nodeName."</td>
		</tr>
		<tr>
			<td class='even_th'>Статус:</td>
			<td>".$severityStatuses[$max]."</td>
			<td style='padding:0;width:15px;text-align:center'><img width=16 src='img/err_".$severityName.".png' alt='".$severityName."'></td>
		</tr>";

    if(isset($results['alarm_events']))
    {
        foreach($results['alarm_events'] as $value)

        {
            echo  "<tr>
					    <td class='even_th'><b>Тревога</b>:</td>
						<td>".$value['message']." ".$value['event_timestamp']."</td>
						<td style='padding:0;width:15px;text-align:center'><img width=16 src='img/err_".$value['severity_name'].".png' alt='".$value['severity_name']."'></td>
					</tr>";
        }
    }
    if(isset($results['idata']))
    {
        foreach($results['idata'] as $value)
        {
            echo "<tr>
						<td class='even_th' >".$value['description']."</td>
						<td>".$value['idata_value']."</td>
						<td style='padding:0;width:15px;text-align:center'></td>
					</tr>";
        }
    }
						
    echo "</table>";
	
?>