<?php

function connection($dbConfigs, $personalinfo)
{
    $connection = mysqli_connect($dbConfigs['host'], $dbConfigs['user'], $dbConfigs['password'], $dbConfigs['db_name']);
    if (!$connection) {
        echo "Error: Unable to connect to MySQL. Please check your DB connection parameters.\n";
        exit;
    }

    return $connection;
}

function checkIsTableExist($tableName, $connection)
{
    $tableExist = $connection->query("SHOW TABLES LIKE '" . $tableName . "'");
    $tableExist = $tableExist->num_rows;

    if (!$tableExist) {
        return null;
    }

    return $tableExist;
}

function objectProperties($connection, $nodeName, $returnData)
{
    $sql = "SELECT `name`,`object_id`,`status` FROM object_properties WHERE name=?";

    if ($stmt = $connection->prepare($sql)) {
        $stmt->bind_param("s", $nodeName);
        $stmt->execute();
        $result = $stmt->get_result();
        $objectProperties = $result->fetch_assoc();

        if (!isset($objectProperties)) {
            $returnData['object_properties']['name'] = "(Name: $nodeName not found)";
//            dd($returnData);die;
            return ['returnData' => $returnData];
        }

        $stmt->close();
    } else {
        return null;
    }

    return $objectProperties;
}

function severity($connection, $objectId)
{
    $sql = "SELECT  DATE_FORMAT(FROM_UNIXTIME(MAX(`event_timestamp`)), '%H:%i:%s %e-%m-%Y') as event_timestamp,
                    `event_name`, 
                    `event_code`,
                    `severity`,
                    `message` 
        FROM alarm_events
        WHERE source_object_id = {$objectId}
        GROUP BY severity";

    $resultStatus = $connection->query($sql);
    $resultStatus = $resultStatus->fetch_all(MYSQLI_ASSOC);

    return $resultStatus;
}

function itemIds($connection, $filteredNames, $objectId)
{
    if (count($filteredNames) !== 0) {
        $filteredNames = "'" . implode("','", $filteredNames) . "'";

        $sqlItem = "SELECT * FROM items WHERE node_id = $objectId AND `description` IN ($filteredNames)";
    } else {
        $sqlItem = "SELECT * FROM items WHERE node_id = $objectId ";
    }

    $resultItems = $connection->query($sqlItem);
    $resultItems = $resultItems->fetch_all(MYSQLI_ASSOC);

    return $resultItems;
}

function idataByItemId($connection, $idataTable, $itemId)
{
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
        WHERE {$idataTable}.item_id = {$itemId} 
        ORDER BY {$idataTable}.idata_timestamp DESC
        LIMIT 1 ";

    $resultValues = $connection->query($sqlValue);
    $result = $resultValues->fetch_assoc();

    return $result;
}

function itemIdsArray($connection,$tdata)
{
    $sql = "SELECT DISTINCT item_id FROM {$tdata} ";

    $tableIdValues = $connection->query($sql);
    $tableIdValues = $tableIdValues->fetch_all(MYSQLI_ASSOC);

    return $tableIdValues;

}

function tdataArrayValues($connection, $tdata, $resultItem)
{
    $sqlFin = "SELECT `item_id`, `tdata_timestamp`, `tdata_value`          
                        FROM {$tdata} 
                        WHERE item_id = {$resultItem['item_id']} 
                        ORDER BY `tdata_timestamp` DESC  LIMIT 1";

    $resultValues = $connection->query($sqlFin);
    $resultValues = $resultValues->fetch_all(MYSQLI_ASSOC);

    return $resultValues;
}

