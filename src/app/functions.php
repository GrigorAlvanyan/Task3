<?php

function dbConnection($dbConfigs, $personalinfo)
{
    $connection = connection($dbConfigs, $personalinfo);

    return $connection;
}

function htmlTableDecode($value, $errorsMessage){
    $htmlTable = @zlib_decode(substr(base64_decode("{$value['tdata_value']}"), 4));
    if (empty($htmlTable)) {
        $htmlTable = @zlib_decode(substr(base64_decode("{$value['tdata_value']}"), 5));
        if (empty($htmlTable)) {
            return ['error' => $errorsMessage['mac_address_errors']['error_offset']];
        }
    }
    return $htmlTable;
}

function getDomDocument($htmlTable){
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

    return $result = [
        'rows' => $rows,
        'tableName' => $tableName,
        'columnCount' => $columnCount,
        'col' => $col
    ];
}


function getMaccAddress($connection, $eoc_mac, $objectProp, $errorsMessage)
{
    $macAddressTables = [];
    $numb = 0;
    if (isset($objectProp['object_id'])) {
        $tdata = 'tdata_' .  $objectProp['object_id'];
        $isLocal = isLocal();
        if (isset($isLocal) && $isLocal) {
        $tdata = 'tdata_78528';
        }
        $tableIdValues = itemIdsArray($connection, $tdata);
            foreach ($tableIdValues as $resultItem) {
                $resultValues = tdataArrayValues($connection, $tdata, $resultItem);
                 foreach ($resultValues as $value) {
                     $htmlTable = htmlTableDecode($value, $errorsMessage);
                     if (isset($htmlTable['error'])) {
                         return $htmlTable;
                     }
                        $result = getDomDocument($htmlTable);
                        $rows = $result['rows'];
                        $tableName = $result['tableName'];
                        $columnCount = $result['columnCount'];
                        $col = $result['col'];
                        foreach ($rows as $row) {
                            $cols = $row->getElementsByTagName('td');
                            if ($cols->item(1)->nodeValue == $eoc_mac) {
                                $numb++;
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
            }
            if ($numb == 0) {
                return  ['error' => $errorsMessage['mac_address_errors']['mac_address_not_found']];
            }
        return $macAddressTables;
    } else {
        return ['error' => $errorsMessage['mac_address_errors']['result_table_not_found']];
    }
}

function getMacAddressValues($configTdataRanges, $displayNameValue, $displayName, $tableName)
{
    $dispName = '';
    $dispValue = [];
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
                                $dispName = $key . ':' . $displayNameValue;
                                $dispValue = [
                                    'dispName'=> $dispName,
                                    'statuses'=> $statuses
                                ];
                            }
                        }
                    }
                    return $dispValue;
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

    $objectProperties = objectProperties($connection, $nodeName, $returnData);

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
    $resultStatus = severity($connection, $objectProperties['object_id']);

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
}

function getIdataIds($connection, $objectProperties, $filteredNames, $errorsMessage)
{
    $objectId = $objectProperties['object_id'];

    $resultItems = itemIds($connection, $filteredNames, $objectId);

    $itemIds = [];
    foreach ($resultItems as $resultItem) {
        $itemIds[] = $resultItem['item_id'];
    }

    $idataTable = 'idata_' . $objectProperties['object_id'];

    return ['idatatTable' => $idataTable, 'itemIds' => $itemIds];
}

function getIdata($connection, $itemIds, $today, $idataTable, $errorsMessage)
{
    $resultValuesData = [];
    foreach ($itemIds as $itemId) {
        $idata = idataByItemId($connection, $idataTable, $itemId);

        if ($idata) {
            $eventTime = $idata['idata_timestamp'];
            $eventTimeExplode = explode(' ', trim($eventTime));

            if (isset($eventTimeExplode[1]) && $eventTimeExplode[1] == $today) {
                $idata['idata_timestamp'] = $eventTimeExplode[0];
            }
            $resultValuesData[$idata['item_id']] = $idata;
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
    if ($objectProperties === null) {
        return ['error' => $nodeName . $errorsMessage['node_name'] . __LINE__];
    } elseif (isset($objectProperties['returnData'])) {
        return $objectProperties['returnData'];
    }

    $getSeverityVal = getSeverity($connection, $objectProperties, $severityStatuses, $today, $errorsMessage);
    $severityValue = $getSeverityVal['severityValue'];
    $resultStatus = $getSeverityVal['resultStatus'];

    $idataTableValues = getIdataIds($connection, $objectProperties, $filteredNames, $errorsMessage);
    if ($idataTableValues === null) {
        return ['error' => $errorsMessage['items'] . __LINE__];
    }

    $idataTable = $idataTableValues['idatatTable'];
    $tableExist = checkIsTableExist($idataTable, $connection);
    if ($tableExist === null) {
        return ['error' => $idataTable . $errorsMessage['table'] . __LINE__];
    }
    $itemIds = $idataTableValues['itemIds'];
    $resultValuesData = getIdata($connection, $itemIds, $today, $idataTable, $errorsMessage);
    if ($resultValuesData === null) {
        return ['error' => $errorsMessage['values'] . __LINE__, '_mysql_error' => $connection->error];
    }

    return [
        'object_properties' => $objectProperties,
        'alarm_events' => $resultStatus,
        'idata' => $resultValuesData,
        'severityValue' => $severityValue
    ];
}


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

