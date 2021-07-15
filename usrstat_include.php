<?php
error_reporting(E_ALL);
require_once 'helpers.php';
$configs = include 'config.php';

$severityStatuses = $configs['severityStatuses'];
$errorsMessage = $configs['error_messages'];
$configIdataRanges = isset($configs['idata_ranges']) ? $configs['idata_ranges'] : [];
$filteredNames = isset($configs['filteredNames']) ? $configs['filteredNames'] : [];
$configTdataRanges = isset($configs['tdata_ranges']) ? $configs['tdata_ranges'] : [];


$nodeName = '';
$severityValue = [];

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


function getMaccAddress($connection, $eoc_mac, $objectProp)
{
    $macAddressTables = [];

    $tdata = 'tdata_' . $objectProp['object_id'];
    $sql = "SELECT DISTINCT item_id FROM {$tdata} ";
    if ($tableIdValues = $connection->query($sql)) {
        $tableIdValues = $tableIdValues->fetch_all(MYSQLI_ASSOC);
        foreach ($tableIdValues as $resultItem) {

            $sqlFin = "SELECT `item_id`, `tdata_timestamp`, `tdata_value`          
                    FROM {$tdata} 
                    WHERE item_id = {$resultItem['item_id']} 
                    ORDER BY `tdata_timestamp` DESC  LIMIT 1";

            if ($resultValues = $connection->query($sqlFin)) {
                $resultValues = $resultValues->fetch_all(MYSQLI_ASSOC);

                foreach ($resultValues as $value) {

                    $htmlTable = @zlib_decode(substr(base64_decode("{$value['tdata_value']}"), 4));
                    if (empty($htmlTable)) {

                        $htmlTable = @zlib_decode(substr(base64_decode("{$value['tdata_value']}"), 5));
                        if (empty($htmlTable)) {
                            return 'error offset';
                        }
                    }

                    $doc = new DOMDocument();
                    libxml_use_internal_errors(true);
                    @$doc->loadHTML($htmlTable);
                    libxml_clear_errors();
                    $doc->preserveWhiteSpace = false;
                    $table = $doc->getElementsByTagName('table');
                    $tableName = $table->item(0)->getAttribute("name");
                    $column = $doc->getElementsByTagName('column');
                    $columnCount = $column->length;

                    if ($columnCount > 2) {
                        $columnArr = [];
                        for ($i = 2; $i < $columnCount; $i++) {
                            $columnArr[] = $column->item($i)->getAttribute("name");
                        }
                        $col = $columnArr;
                    }
                    $rows = $table->item(0)->getElementsByTagName('tr');
                    foreach ($rows as $row) {
                        $cols = $row->getElementsByTagName('td');
                        if ($cols->item(1)->nodeValue == $eoc_mac) {

                            $macAddressTable = [];
                            $mac = '';
                            $mac = 'MAC:' . $cols->item(1)->nodeValue . ' ';

                            $item = 0;
                            $tablesName = [
                                'id' => $resultItem['item_id'],
                                'Table_Name' => $tableName,
                                'MAC' => $mac,
                            ];
                            for ($i = 2; $i < $columnCount; $i++) {

                                $val = $cols->item($i)->nodeValue . ' ';
                                $tablesName["{$col[$item]}"] = $val;
                            }
                            $macAddressTable = $tablesName;
                            $macAddressTables[] = $macAddressTable;
                        }
                    }
                }
            } else {
                return 'Result Table not found';
            }
        }
        return $macAddressTables;
    } else {
        return 'Result Table not found';
    }
}

function getMacAddressValues($configTdataRanges, $displayNameValue, $displayName, $tableName)
{
    $dispName = '';
    if (!is_numeric($displayNameValue)) {
        return $displayNameValue;
    }
    foreach ($configTdataRanges as $rangeTabName => $values) {
        if ($tableName == $rangeTabName) {
            foreach ($values as $key => $value) {
                if ($displayName == $key) {
                    foreach ($value as $statuses => $status) {
                        foreach ($status as $value) {
                            if ($displayNameValue >= $value['min'] && $displayNameValue <= $value['max']) {
                                $dispName = $key . ':' . $statuses;
                                return $dispName;
                            }
                        }
                    }
                }
            }
        }
    }
}

function getObjectProperties($connection, $nodeName, $errorsMessage)
{
    $returnData = [
        'object_properties' => [],
        'alarm_events' => [],
        'idata' => [],
        'severityValue' => []
    ];
    if ($stmt = $connection->prepare("SELECT `name`,`object_id`,`status` FROM object_properties WHERE name=?")) {
        $stmt->bind_param("s", $nodeName);
        $stmt->execute();
        $result = $stmt->get_result();
        $objectProperties = $result->fetch_assoc();

        if (!isset($objectProperties)) {
            $returnData['object_properties']['name'] = "(Name: $nodeName not found)";
            return ['returnData' => $returnData];
        }

        $stmt->close();
    } else {
        return null;
    }
    return $objectProperties;
}





function getSeverityValues($resultStatus, $severityStatuses)
{
    $severityName = '';
    $severityMax = (!empty($resultStatus)) ? $resultStatus : [];

    $max = 0;
    for ($i = 0; $i < count($severityMax); $i++) {

        if ($severityMax[$i]['severity'] > $max) {
            $max = $severityMax[$i]['severity'];
        }
    }

    foreach ($severityStatuses as $key => $value) {
        if ($max == $key) {
            $severityName = $value;
        }
    }

    return $severityValue = [
        'max' => $max,
        'severityName' => $severityName
    ];

}


function getSeverity($connection, $objectProperties, $severityStatuses, $today, $errorsMessage)
{
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
        $severityValue = getSeverityValues($resultStatus, $severityStatuses);

        foreach ($resultStatus as &$severity) {

            $eventTime = $severity['event_timestamp'];
            $eventTimeExplode = explode(' ', trim($eventTime));

            if (isset($eventTimeExplode[1]) && $eventTimeExplode[1] == $today) {
                $severity['event_timestamp'] = $eventTimeExplode[0];
            }

            $severity['severity_name'] = $severityStatuses[$severity['severity']];

        }
        return ['severityValue' => $severityValue, 'resultStatus' => $resultStatus];
    } else {
        return null;
    }
}

function getIdataName($connection, $objectProperties, $filteredNames, $errorsMessage)
{
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
        return null;
    }

    $itemIds = [];

    foreach ($resultItems as $resultItem) {
        $itemIds[] = $resultItem['item_id'];
    }

    $idataTable = 'idata_' . $objectProperties['object_id'];
    return ['idatatTable' => $idataTable, 'itemIds' => $itemIds];
}


function checkIsTableExist($idataTable, $connection, $errorsMessage)
{
    $tableExist = $connection->query("SHOW TABLES LIKE '" . $idataTable . "'");
    $tableExist = $tableExist->num_rows;
    if (!$tableExist) {
        return null;
    }
    return $tableExist;
}

function getIdata($connection, $itemIds, $today, $idataTable, $errorsMessage)
{
    $resultValuesData = [];
    foreach ($itemIds as $itemId) {

        $sqlValue = "SELECT {$idataTable}.item_id, 
                        DATE_FORMAT(FROM_UNIXTIME(`idata_timestamp`), '%H:%i:%s %e-%m-%Y') as idata_timestamp, 
                        `idata_value`, 
                        `raw_value`,
                        `template_id`, 
                        `template_item_id`, 
                        `guid`, 
                        `name`, 
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
            return null;
        }
    }
    return $resultValuesData;
}

function getStatuses($configIdataRanges, $itemValue, $description)
{
    if (!is_numeric($itemValue)) {
        return $itemValue;
    }


    foreach ($configIdataRanges as $rangeDescription => $values) {
        if ($description == $rangeDescription) {
            foreach ($values as $key => $value) {
                foreach ($value as $item) {
                    if ($itemValue >= $item['min'] && $itemValue <= $item['max']) {
                        return $key;
                    }
                }
            }
        }
    }
}





function getResult($connection, $nodeName, $filteredNames, $severityStatuses, $severityValue, $errorsMessage)
{

    $date = new DateTime();
    $today = $date->format('d-m-Y');

    $objectProperties = getObjectProperties($connection, $nodeName, $errorsMessage);
    if ($objectProperties == null) {
        return ['error' => $nodeName . $errorsMessage['node_name'] . __LINE__];
    }
    elseif (isset($objectProperties['returnData'])) {
        return $objectProperties['returnData'];
    }

    $getSeverityVal = getSeverity($connection, $objectProperties, $severityStatuses, $today, $errorsMessage);
    $severityValue = $getSeverityVal['severityValue'];
    $resultStatus = $getSeverityVal['resultStatus'];

    if ($getSeverityVal == null) {
        return ['error' => $errorsMessage['status'] . __LINE__];
    }

    $idataTableValues = getIdataName($connection, $objectProperties, $filteredNames, $errorsMessage);
    if ($idataTableValues == null) {
        return ['error' => $errorsMessage['items'] . __LINE__];
    }

    $idataTable = $idataTableValues['idatatTable'];
    $itemIds = $idataTableValues['itemIds'];

    $tableExist = checkIsTableExist($idataTable, $connection, $errorsMessage);

    if ($tableExist == null) {
        return ['error' => $idataTable . $errorsMessage['table'] . __LINE__];
    }

    $resultValuesData = getIdata($connection, $itemIds, $today, $idataTable, $errorsMessage);

    if ($resultValuesData == null) {
        return ['error' => $errorsMessage['values'] . __LINE__, '_mysql_error' => $connection->error];
    }

    return ['object_properties' => $objectProperties, 'alarm_events' => $resultStatus, 'idata' => $resultValuesData, 'severityValue' => $severityValue];
}

$results = getResult($connection, $nodeName, $filteredNames, $severityStatuses, $severityValue, $errorsMessage);
if (isset($results['error'])) {
    echo $results['error'];
    die;
} else {
//    dd($results);
}


$objectProp = getObjectProperties($connection, $nodeName, $errorsMessage);
if (!empty($objectProp)) {
    //$eoc_ip = str_replace(';','<br>',trim(filter($row[15]),';'));
    if (isset($_GET['mac']) && !empty($_GET['mac'])) {
        $macName = $_GET['mac'];
        if(strlen(implode('', explode(':', trim($macName)))) == 12){
            $eoc_mac = implode('', explode(':', trim($macName)));
            $macAddressesValue = getMaccAddress($connection,$eoc_mac, $objectProp);
            $errorOffset = '';
            if($macAddressesValue == 'error offset'){
                $errorOffset = $macAddressesValue;
            }
        }
    }else {
        if (isset($personalinfo[43])) {
            if (strpos($personalinfo[43], "375828706861857")) {
                $eoc_tmp_array = explode("**", substr($personalinfo[43], strpos($personalinfo[43], "7375828706861857"), 38));
                $eoc_mac = $eoc_tmp_array[2];
                $macAddressesValue = getMaccAddress($connection, $eoc_mac, $objectProp);
                $errorOffset = '';
                if($macAddressesValue == 'error offset'){
                    $errorOffset = $macAddressesValue;
                }
            }
        }
    }
} else {
    echo 'not found';
    die;
}

$connection->close();

$status = isset($results['severityValue']) && isset($results['severityValue']['max']) ? $results['severityValue']['max'] : '';
$severity = isset($results['severityValue']) && isset($results['severityValue']['severityName']) ? $results['severityValue']['severityName'] : '';
$name = isset($results['object_properties']) && isset($results['object_properties']['name']) ? $results['object_properties']['name'] : '';

$statuses = [];

function configIdataRanges($configRanges, $arrValues)
{
    foreach ($configRanges as $rangeNames => $values) {
        foreach ($values as $key => $item) {
            if (!in_array($key, $arrValues)) {
                $arrValues[] = $key;
            }
        }
    }
    return $arrValues;
}

$statuses = configIdataRanges($configIdataRanges, $statuses);

$excludeKeys = [
    'id',
    'Table_Name',
    'MAC',
];



?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Узел</title>
</head>
<body>
<table class='table_1'>
    <tr>
        <th colspan=2><b>Узел</b></th>
    </tr>
    <tr>
        <td class='even_th' style='width:110px'>Имя:</td>
        <td colspan=2><?= $name ?></td>
    </tr>
    <tr>
        <td class='even_th'>Статус:</td>
        <td><?= $severity ?></td>
        <td style='padding:0;width:15px;text-align:center'><img width=16 src='img/err_<?= $severity ?>.png'
                                                                alt='<?= $severity ?>' title='<?= $severity ?>'></td>
    </tr>

    <?php if (isset($results['alarm_events'])) : ?>
        <?php foreach ($results['alarm_events'] as $value)  : ?>
            <tr>
                <td class='even_th'><b>Тревога</b>:</td>
                <td><?= $value['message'] . " " . $value['event_timestamp'] ?></td>
                <td style='padding:0;width:15px;text-align:center'><img width=16
                                                                        src='img/err_<?= $value['severity_name'] ?>.png'
                                                                        alt='<?= $value['severity_name'] ?>'
                                                                        title='<?= $severity ?>'></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($results['idata'])) : ?>
        <?php foreach ($results['idata'] as $value) : ?>
            <?php $valueImg = '';
            $valueSeverityStatuses = getStatuses($configIdataRanges, $value['idata_value'], $value['description']);
            $valueStatus = isset($valueSeverityStatuses) ? $valueSeverityStatuses : '';
            for ($i = 0; $i < count($statuses); $i++) {
                if ($valueStatus == $statuses[$i]) {
                    $valueImg = $valueStatus;;
                }
            }
            ?>
            <tr>
                <td class='even_th'><?= $value['description'] ?></td>
                <td><?= $value['idata_value'] ?></td>
                <td style='padding:0;width:15px;text-align:center'><img width=16 src='img/err_<?= $valueImg ?>.png'
                                                                        alt='<?= $valueImg ?>' title='<?= $valueImg ?>'>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if($macAddressesValue == 'Result Table not found') : ?>
        <tr>
            <td nowrap="nowrap"><?= 'Result Table not found';?></td>
        </tr>
    <?php die; ?>
    <?php endif; ?>

    <?php if($errorOffset != 'error offset') :?>
    <?php if (isset($macAddressesValue) && !empty($macAddressesValue)) : ?>
        <?php foreach ($macAddressesValue as $line) : ?>
            <?php
            $tdValues = [];
            foreach ($line as $key => $str) {
                $displayNameValue = '';
                $name = '';
                $strValue = '';
                if (isset($key) && !empty($key) && !in_array($key, $excludeKeys)) {
                    $displayNameValue = (int) $str;
                    $name = $key;
                    $macValueStatuses = getMacAddressValues($configTdataRanges, $displayNameValue, $name, $line['Table_Name']);
                    if (isset($macValueStatuses) && !empty($macValueStatuses)) {
                        $strValue = $macValueStatuses . '<br>';
                    } else {
                        $strValue = $name .':'. $displayNameValue . '<br>';
                    }
                    $tdValues[] = $strValue;
                }

            }
            ?>
            <tr style="vertical-align: top;">
                <td class='even_th' title="<?= $line['id'] ?>"><?= $line['Table_Name'] ?></td>
                <td class='even_th'>
                    <?php foreach ($tdValues as $td) : ?>
                        <?= $td ?>
                    <?php endforeach; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php elseif(isset($macAddressesValue) && empty($macAddressesValue)) :?>
    <tr>
        <td nowrap="nowrap"><?= 'MAC Address not found';?></td>
    </tr>
    <?php endif; ?>
    <?php else :?>
        <tr>
            <td nowrap="nowrap"><?= 'Error offset';?></td>
        </tr>
    <?php endif; ?>
</table>
</body>
</html>

