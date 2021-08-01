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


function getAssociatedStations($associatedStationLines)
{
    $associatedStations = [];
    $mac = '';
    foreach ($associatedStationLines as $key => $line) {
        $macAddress = explode('  ', $line);
        if (isset($macAddress[0]) && isValidMacAddress($macAddress[0])) {
            $mac = $macAddress[0];
            $associatedStations[$mac]['mac'] = $mac;
            if (isset($macAddress[1]) && !empty($macAddress[1])) {
                $signal = explode(' (', $macAddress[1]);
                $associatedStations[$mac]['signal'] = $signal[0];
                $signals = explode('/', $signal[0]);
                $associatedStations[$mac]['dBmFrom'] = (float)getdBmValue($signals[0]);
                $associatedStations[$mac]['dBmTo'] = (float)getdBmValue($signals[1]);
            }
        } elseif (strpos($line, 'RX:')) {
            $rxLine = getRXTXLine($line);
            $associatedStations[$mac]['rx'] = $rxLine;
            $associatedStations[$mac]['rxValue'] = (float)getRxTx($rxLine);;
        } elseif (strpos($line, 'TX:')) {
            $txLine = getRXTXLine($line);
            $associatedStations[$mac]['tx'] = $txLine;
            $associatedStations[$mac]['txValue'] = (float)getRxTx($txLine);;
        }
    }
    return $associatedStations;
}


function getRXTXLine($line)
{
    $lineValue = trim($line);
    $lineValue = substr($lineValue, 0, strpos($lineValue, '  '));
    $lineValue = explode(', ', $lineValue);
    if (count($lineValue) > 3) {
        unset($lineValue[count($lineValue) - 1]);
    }
    unset($lineValue[count($lineValue)]);
    $lineValue = implode(', ', $lineValue);

    return $lineValue;
}


function getdBmValue($dBmValue)
{
    $dBmValue = explode(' ', ltrim($dBmValue));

    return $dBmValue[0];
}


function getRxTx($values)
{
    $value = explode(': ', $values)[1];
    $value = explode(' ', $value)[0];

    return $value;
}

function getWireless($iwinfoResults)
{
    $parsedIwinfo = [];
    foreach ($iwinfoResults as $iwinfoLine) {
        if (strpos($iwinfoLine, 'ESSID:')) {
            $iwinfoLine = substr($iwinfoLine, strpos($iwinfoLine, '  '));
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
    foreach ($parsedIwinfo as $str) {
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


function linesRemove($arr)
{
    unset($arr[0]);
    $resArrayCount = count($arr);
    unset($arr[$resArrayCount]);
    return $arr;
}


function isValidTimeStamp($timestamp)
{
    return ((string)(int)$timestamp === $timestamp)
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
}

function getDhcpLeases($dhcpLeasesFileLines)
{
    $dhcpLeases = [];
    foreach ($dhcpLeasesFileLines as $key => $line) {
        $line = array_reverse(explode(' ', $line));
        unset($line[0]);
        foreach ($line as $item) {

            if (isValidTimeStamp($item)) {

                $presentTime = time();
                $presentDate = date('Y-m-d H:i:s', $presentTime);
                $timeDifference = $item - $presentTime;
                $timeDifference = $presentTime - $timeDifference;

                $oldDate = date('Y-m-d H:i:s', $timeDifference);

                $assigned_time = "{$oldDate}";
                $completed_time = "{$presentDate}";

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

        $mac = substr($key, 0, 8);
        $name = getDeviceNameByMacAddress($mac);
        $associatedLines[$key]['brand'] = $name;
        foreach ($dhcpResultArr as $models) {
            foreach ($models as $model) {
                if (strcasecmp($key, $model) == 0) {
                    $associatedLines[$key]['hostName'] = $models[0];
                }
            }
        }
    }

    return $associatedLines;
}


function secondsToWords($seconds)
{
    $days = (int)($seconds / 86400);
    $hours = (int)(($seconds - ($days * 86400)) / 3600);
    $mins = (int)(($seconds - $days * 86400 - $hours * 3600) / 60);
    $secs = (int)($seconds - ($days * 86400) - ($hours * 3600) - ($mins * 60));
    return sprintf("%ds", $secs);
}



//todo needs refactoring
function getUptime($uptimeResult)
{
    $dayValue = '';
    $uptimeResultvalues = explode(', ', $uptimeResult[1]);
    if (strpos($uptimeResultvalues[0], 'day') || strpos($uptimeResultvalues[0], 'days')) {
        $dayValue = explode(' ', ltrim($uptimeResultvalues[0]));
        if ($dayValue[3] == 'day' || $dayValue[3] == 'days') {
            $dayValue[3] = 'd';
        }
        unset($dayValue[0], $dayValue[1]);
        $dayValue = implode('', $dayValue);
    } else {
        $uptimeResultvalues[1] = explode(' ', $uptimeResultvalues[0])[1];
    }
    if (strpos($uptimeResultvalues[1], ':')) {

        $hourMinut = explode(':', $uptimeResultvalues[1]);
        $hourMinut[0] .= 'h';
        if (strlen($hourMinut[1]) == 2 && $hourMinut[1][0] == 0) {
            $hourMinut[1] = $hourMinut[1][1];
        }
        $hourMinut[1] .= 'm';
        $dateValue = implode(' ', $hourMinut);
    } elseif (strpos($uptimeResultvalues[1], 'min')) {
        $dateValue = str_replace(' min', 'm', $uptimeResultvalues[1]);
    } else {
        $hourMinut = explode(' ', $uptimeResultvalues[1]);
        $hourMinut[1] = 'm';
        $dateValue = implode('', $hourMinut);
    }
    $uptime = $dayValue . ' ' . $dateValue;
    $uptime .= ' ' . secondsToWords(time());

    return $uptime;
}

function getLocalTime($dateResults)
{
    $line = explode(' ', $dateResults[1]);
    $lineSize = count($line);
    $lineSize -= 2;
    unset($line[$lineSize]);
    $line = implode(' ', $line);
    return $line;
}

function getSignal($associatedLines)
{
    $dBmphoto = 'signal-0';
    foreach ($associatedLines as $macAddress) {
        $tx = explode('-', $macAddress['dBmFrom'])[1];
        $rx = explode('-', $macAddress['dBmTo'])[1];


        if ($tx > 0 && $tx <= ($rx - ($rx / 4) * 3)) {
            $dBmphoto = 'signal-0-25';
        } elseif ($tx > ($rx - ($rx / 4) * 3) && $tx <= ($rx - ($rx / 4) * 2)) {
            $dBmphoto = 'signal-25-50';
        } elseif ($tx > ($rx - ($rx / 4) * 2) && $tx <= ($rx - ($rx / 4) * 1)) {
            $dBmphoto = 'signal-50-75';
        } elseif ($tx > ($rx - ($rx / 4) * 1) && $tx <= $rx) {
            $dBmphoto = 'signal-75-100';
        }
    }
    return $dBmphoto;

}
