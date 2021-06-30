<?php
error_reporting(E_ALL);
require_once 'helpers.php';
$configs = include 'config.php';

$severityStatuses = $configs['severityStatuses'];
$errorsMessage = $configs['error_messages'];
$idataValues = $configs['idata_values'];


$nodeName = '';
$severityValue = [];
//$filteredNames = ['NodeUpTime', 'TV Laser', 'Temperature'];
$filteredNames = [];


$connection = mysqli_connect($configs['db_params']['host'], $configs['db_params']['user'], $configs['db_params']['password'], $configs['db_params']['db_name']);

if (!$connection) {
    echo "Error: Unable to connect to MySQL. Please check your DB connection parameters.\n";
    exit;
}
if (isset($_GET['name']) && !empty($_GET['name'])) {
    $nodeName = $_GET['name'];
} else {
    $nodeName = $personalinfo[2];
}

function getStatus($idataValues, $itemValue)
{
    if (!is_numeric($itemValue)) {
        return $itemValue;;
    }

    foreach ($idataValues as $key => $value) {
        if ($key == 'minor' && $itemValue >= $value['minus_max'] && $itemValue <= $value['minus_min']) {
            return $key;
        } else {
            if (isset($value['max'])) {
                if ($itemValue >= $value['min'] && $itemValue <= $value['max']) {
                    return $key;
                }
            } else {
                if ($itemValue >= $value['min']) {
                    return $key;
                }
            }
        }
    }
    return $itemValue;
}

function getResult($connection, $nodeName, $filteredNames, $severityStatuses, $severityValue, $errorsMessage)
    {
        $returnData = [
            'object_properties' => [],
            'alarm_events' => [],
            'idata' => [],
            'severityValue' => []
        ];

        $date = new DateTime();
        $today = $date->format('d-m-Y');

        if ($stmt = $connection->prepare("SELECT `name`,`object_id`,`status` FROM object_properties WHERE name=?")) {
            $stmt->bind_param("s", $nodeName);
            $stmt->execute();
            $result = $stmt->get_result();
            $objectProperties = $result->fetch_assoc();

            if (!isset($objectProperties)) {
                $returnData['object_properties']['name'] = "(Name: $nodeName not found)";
                return $returnData;
            }
            $stmt->close();
        } else {
            return ['error'  => $nodeName . $errorsMessage['node_name'] . __LINE__];
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
            $severityMax = (!empty($resultStatus)) ? $resultStatus : [];

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
            $severityName = (isset($severityMax[$item]) && isset($severityMax[$item]['severity_name'])) ? $severityMax[$item]['severity_name'] : 'normal';

            $severityValue = [
                'max' => $max,
                'item' => $item,
                'severityName' => $severityName
            ];

            foreach ($resultStatus as &$severity) {

                $eventTime = $severity['event_timestamp'];
                $eventTimeExplode = explode(' ', trim($eventTime));

                if (isset($eventTimeExplode[1]) && $eventTimeExplode[1] == $today) {
                    $severity['event_timestamp'] = $eventTimeExplode[0];
                }

                $severity['severity_name'] = $severityStatuses[$severity['severity']];

            }
        } else {
            return ['error' => $errorsMessage['status'] . __LINE__];
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
            return ['error' => $errorsMessage['items'] . __LINE__];
        }

        $itemIds = [];

        foreach ($resultItems as $resultItem) {

            $itemIds[] = $resultItem['item_id'];

        }

        $idataTable = 'idata_' . $objectProperties['object_id'];
        $tableExist = $connection->query("SHOW TABLES LIKE '".$idataTable."'");
        $tableExist = $tableExist->num_rows;

        if (!$tableExist) {
            return ['error' => $idataTable . $errorsMessage['table'] . __LINE__];
        }

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
                return ['error' => $errorsMessage['values']. __LINE__ , '_mysql_error' => $connection->error];
            }

        }
        return ['object_properties' => $objectProperties, 'alarm_events' => $resultStatus, 'idata' => $resultValuesData, 'severityValue'=> $severityValue];
    }

    $results = getResult($connection, $nodeName, $filteredNames, $severityStatuses, $severityValue, $errorsMessage);


if (isset($results['error'])) {
    echo $results['error'];die;
} else {
//    dd($results);
}

$connection->close();

$status = isset($results['severityValue']) && isset($results['severityValue']['max']) ? $results['severityValue']['max'] : '';
$severity = isset($results['severityValue']) && isset($results['severityValue']['severityName']) ? $results['severityValue']['severityName'] :'';
$name = isset($results['object_properties']) && isset($results['object_properties']['name']) ? $results['object_properties']['name'] : '';

        echo "</table><table class='table_1'> 
		<tr>
		    <th colspan=2><b>Узел</b></th>			
		</tr>
		
		<tr>
			<td class='even_th' style='width:110px'>Имя:</td>
			<td colspan=2>".$name."</td>
		</tr>
		<tr>
			<td class='even_th'>Статус:</td>
			<td>".$severity."</td>
			<td style='padding:0;width:15px;text-align:center'><img width=16 src='img/err_".$severity.".png' alt='".$severity."' title='{$severity}'></td>
		</tr>";

    if(isset($results['alarm_events']))
    {
        foreach($results['alarm_events'] as $value)

        {
            echo  "<tr>
					    <td class='even_th'><b>Тревога</b>:</td>
						<td>".$value['message']." ".$value['event_timestamp']."</td>
						<td style='padding:0;width:15px;text-align:center'><img width=16 src='img/err_".$value['severity_name'].".png' alt='{$value['severity_name']}' title='{$severity}'></td>
					</tr>";
        }
    }
    if(isset($results['idata']))
    {
        foreach($results['idata'] as $value)
        {
            $valImg = '';
            $val = getStatus($idataValues, $value['idata_value']);
            $val = isset($val) ? $val : 'N/A';
            if($val == 'minor' || $val == 'major' || $val == 'normal' || $val == 'critical'){
                $valImg = $val;
            }



            echo "<tr>
						<td class='even_th' >".$value['description']."</td>
						<td>".$value['idata_value']."</td>
						<td style='padding:0;width:15px;text-align:center'><img width=16 src='img/err_".$valImg.".png' alt='{$valImg}' title='{$valImg}'></td>
					</tr>";
        }
    }
						
    echo "</table>";
	
?>