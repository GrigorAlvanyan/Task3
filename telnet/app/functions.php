<?php

use TelnetClient\TelnetClient;

function telnetConnection($ip, $port, $username, $password, $prompt = '$')
{
    $telnet = new TelnetClient($ip, $port);
    $telnet->connect();
    $telnet->setPrompt($prompt);
    $telnet->login($username, $password);

    return $telnet;
}



function isValidMacAddress($mac)
{
    if (preg_match('/^([a-fA-F0-9]{2}:){5}[a-fA-F0-9]{2}$/', $mac)) {
        return $mac;
    }
}


function getAssociatedStations($associatedStationLines) {

    $associatedStations = [];
    $mac = '';
    foreach ($associatedStationLines as $key => $line) {

        $macAddress = explode('  ', $line);
        if (isset($macAddress[0]) && isValidMacAddress($macAddress[0])) {
            $mac = $macAddress[0];
            $associatedStations[$mac]['mac'] = $mac;
            if (isset($macAddress[1]) && !empty($macAddress[1])){
                $signal = explode(' (', $macAddress[1]);
                $associatedStations[$mac]['signal'] = $signal[0];
            }
        } elseif (strpos($line, 'RX:')) {
            $rxLine = trim($line);
            $rxLine = substr($rxLine, 0, strpos($rxLine, '  '));
            $rxLine = explode(', ', $rxLine);
            if (count($rxLine) > 3) {
                unset($rxLine[count($rxLine) - 1]);
            }
            unset($rxLine[count($rxLine)]);
            $rxLine = implode(', ',$rxLine);
            $associatedStations[$mac]['rx'] = $rxLine;
        } elseif (strpos($line, 'TX:')) {
            $txLine = trim($line);
            $txLine = substr($txLine, 0, strpos($txLine, '  '));
            $txLine = explode(', ', $txLine);
            if (count($txLine) > 3) {
                unset($txLine[count($txLine) - 1]);
            }
            $txLine = implode(', ',$txLine);
            $associatedStations[$mac]['tx'] = $txLine;
        }

    }
    return $associatedStations;
}

function getWireless($iwinfoResults)
{
    $parsedIwinfo = [];
    foreach ($iwinfoResults as $iwinfoLine) {
        if (strpos($iwinfoLine, 'ESSID:')) {
            $iwinfoLine = substr($iwinfoLine,  strpos($iwinfoLine, '  '));
        }

        $parsedIwinfoLine = explode('  ', ltrim($iwinfoLine));

        if (count($parsedIwinfoLine) == 2) {

            $parsedIwinfo[] = $parsedIwinfoLine[0];
            $parsedIwinfo[] = $parsedIwinfoLine[1];
            unset($parsedIwinfoLine[1]);

        } else {
            $parsedIwinfo[] = $parsedIwinfoLine[0];
        }
    }

    $wireless = [];
    foreach ($parsedIwinfo as  $str) {
        $keys = '';
        $str = explode(': ', $str);
        $keys = $str[0];
        if ($keys == 'ESSID') {
            $str[1] = str_replace('"', '', $str[1]);
            $wireless['SSID'] = $str[1];
        } elseif ($keys == 'Channel') {
            $wireless['Channel'] = $str[1];
        } elseif ($keys == 'Bit Rate') {
            $wireless['Bitrate'] = $str[1];
        } elseif ($keys == 'Access Point') {
            $wireless['BSSID'] = $str[1];
        } elseif ($keys == 'Encryption') {
            $wireless['Encryption'] = $str[1];
        }
    }

    return $wireless;
}


function linesRemove($arr){
    unset($arr[0]);
    $resArrayCount = count($arr);
    unset($arr[$resArrayCount]);
    return $arr;
}


function isValidTimeStamp($timestamp)
{
    return ((string) (int) $timestamp === $timestamp)
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
}

function getDhcpLeases($dhcpLeasesFileLines) {


    $dhcpLeases = [];
    foreach($dhcpLeasesFileLines as $key => $line) {
        $line = array_reverse(explode(' ', $line));
        unset($line[0]);
        foreach ($line as $item) {

            if (isValidTimeStamp($item)) {

                $presentTime = time();
                $presentDate =  date('Y-m-d H:i:s', $presentTime);
                $timeDifference = $item - $presentTime;
                $timeDifference = $presentTime - $timeDifference;

                $oldDate=  date('Y-m-d H:i:s', $timeDifference);

                $assigned_time = "{$oldDate}";
                $completed_time= "{$presentDate}";

                $d1 = new DateTime($assigned_time);
                $d2 = new DateTime($completed_time);
                $interval = $d2->diff($d1);

                $dhcpLeases[$key][] = $interval->format(' %hh %im %ss');

            } else {
                $dhcpLeases[$key][] = $item;
            }
        }
    }

    return $dhcpLeases;
}

//todo need optimization
function getDeviceNameByMacAddress($macAddress)
{
    $deviceName = '';
    $filePath = '../files/macaddress_io-db.json';
    if (!file_exists($filePath)) {
        return $deviceName;
    }

    $macaddressIo = file_get_contents($filePath);
    $macaddressIo = json_decode($macaddressIo, 1);

    foreach ($macaddressIo as $item) {
        $mac = strtolower($item['oui']);
        $macAddress = strtolower($macAddress);
        if ($mac == $macAddress) {
            $deviceName = $item['companyName'];
        }
    }

    return $deviceName;
}




function nameOfMacAddress($associatedLines, $dhcpResultArr)
{


    foreach ($associatedLines as $key => $associatedValue) {

        $mac = substr($key,0,8);
        $name = getDeviceNameByMacAddress($mac);
        $associatedLines[$key]['brand'] = $name;
        foreach ($dhcpResultArr as $models) {
            foreach ($models as $model){
                if(strcasecmp($key, $model) == 0){
                    $associatedLines[$key]['hostName'] = $models[0];
                }
            }
        }
    }

    return $associatedLines;
}



