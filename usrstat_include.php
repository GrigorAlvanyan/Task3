<?php
error_reporting(E_ALL);

require_once 'helpers.php';
require_once 'src/app/functions.php';


$macaddressIo = file_get_contents('files/macaddress_io-db.json');
//$macaddressIo = json_decode($macaddressIo, 1);
//echo gettype($macaddressIo[0]);
//$macaddressIo = explode("\r\n" ,$macaddressIo);
//foreach ($macaddressIo as $line) {
//    echo $line;
//}
dd($macaddressIo);
die;



$configs = getConfigs();

$severityStatuses = $configs['severityStatuses'];
$errorsMessage = $configs['error_messages'];
$configIdataRanges = isset($configs['idata_ranges']) ? $configs['idata_ranges'] : [];
$filteredNames = isset($configs['filteredNames']) ? $configs['filteredNames'] : [];
$configTdataRanges = isset($configs['tdata_ranges']) ? $configs['tdata_ranges'] : [];
$eocIp = isset($_GET['eoc_ip']) && !empty($_GET['eoc_ip']) ? $_GET['eoc_ip'] : '';

$nodeName = '';
$severityValue = [];

$connection = dbConnection($configs['db_params'], null);

if (isset($_GET['name']) && !empty($_GET['name'])) {
    $nodeName = $_GET['name'];
} else {
    $nodeName = $personalinfo[2];
}


$results = getResult($connection, $nodeName, $filteredNames, $severityStatuses, $severityValue, $errorsMessage);
if (isset($results['error'])) {
    echo $results['error'];
}

$macAddressesValue = [];
$errorOffset = '';
$errorWrongMacAddress = '';
$objectProp = getObjectProperties($connection, $nodeName, $errorsMessage);

//dd($objectProp);die;

if (!empty($objectProp)) {


    if (isset($_GET['mac']) && !empty($_GET['mac'])) {
        $macName = $_GET['mac'];
        if(strlen(implode('', explode(':', trim($macName)))) == 12){
            $eoc_mac = implode('', explode(':', trim($macName)));
            $macAddressesValue = getMaccAddress($connection,$eoc_mac, $objectProp);
            if($macAddressesValue == 'error offset'){
                $errorOffset = $macAddressesValue;
            }
        } else {
            $errorWrongMacAddress = 'Wrong mac Address';
        }
    } else {
        if (isset($personalinfo[43])) {
            if (strpos($personalinfo[43], "375828706861857")) {
                $eoc_tmp_array = explode("**", substr($personalinfo[43], strpos($personalinfo[43], "7375828706861857"), 38));
                if (str_replace(":","",$eoc_tmp_array[2]) == 12){
                    $eoc_mac = str_replace(":","",$eoc_tmp_array[2]);
                    $macAddressesValue = getMaccAddress($connection, $eoc_mac, $objectProp);
                    if($macAddressesValue == 'error offset'){
                        $errorOffset = $macAddressesValue;
                    }
                } else {
                    $errorWrongMacAddress = 'Wrong mac Address';
                }
            }
        }
    }
} else {
    $objectProp =  'not found';
}

$connection->close();

$status = isset($results['severityValue']) && isset($results['severityValue']['max']) ? $results['severityValue']['max'] : '';
$severity = isset($results['severityValue']) && isset($results['severityValue']['severityName']) ? $results['severityValue']['severityName'] : '';
$name = isset($results['object_properties']) && isset($results['object_properties']['name']) ? $results['object_properties']['name'] : '';

$statuses = [];

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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="telnet/css/styles.css">
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


    <?php if($macAddressesValue == 'MAC Address not found') : ?>
            <tr>
                <td nowrap="nowrap"><?= 'MAC Address not found';?></td>
            </tr>
        <?php endif; ?>






<!--todo improve error check-->
    <?php if($errorWrongMacAddress != 'Wrong mac Address') : ?>
        <?php if($macAddressesValue != 'Result Table not found') : ?>
            <?php if($objectProp != 'not found') : ?>
                <?php if($errorOffset != 'error offset'  && $macAddressesValue != 'MAC Address not found') :?>
                <?php if (isset($macAddressesValue) && !empty($macAddressesValue)) : ?>
                    <?php foreach ($macAddressesValue as $line) : ?>
                        <?php
                        $tdValues =[];
                            foreach ($line as $key => $str) {
                                $tdValue = [];
                                $displayNameValue = '';
                                $dispValue = '';
                                $macStatus = '';
                                $name = '';
                                $strValue = '';
                                if (isset($key) && !empty($key) && !in_array($key, $excludeKeys)) {

                                        $displayNameValue = (int)$str;
                                    $name = $key;
                                    $macValueStatuses = getMacAddressValues($configTdataRanges, $displayNameValue, $name, $line['Table_Name']);

                                        if (isset($macValueStatuses) && !empty($macValueStatuses)) {
                                        $strValue = $macValueStatuses['dispName'] . '<br>';
                                        $macStatus = $macValueStatuses['statuses'];
                                    } else {
                                        $strValue = $name . ':' . $displayNameValue . '<br>';
                                        $macStatus = '';
                                    }

                                        $tdValue = [
                                        'strValue' => $strValue,
                                        'macStatus' => $macStatus
                                    ];
                                    $tdValues[] = $tdValue;

                                    }
                            }
                        ?>
                        <tr style="vertical-align: top;">
                            <td class='even_th' title="<?= $line['id'] ?>"><?= $line['Table_Name'] ?></td>
                            <td class='even_th'>
                                <?php foreach ($tdValues as  $td) : ?>
                                <?=  $td['strValue'] ?>
                                <?php endforeach; ?>
                            </td>
                            <td style='padding:0;width:15px;text-align:center'>
                                <?php foreach ($tdValues as  $td) : ?>
                                <img width=16 src='img/err_<?= $td['macStatus'] ?>.png' alt='<?= $td['macStatus'] ?>' title='<?= $td['macStatus'] . '<br>' ?>'>
                                <?php endforeach; ?>
                            </td>

                            </tr>
                    <?php endforeach; ?>
                <?php elseif(isset($macAddressesValue) && empty($macAddressesValue)  && empty($errorWrongMacAddress)) :?>
                <tr>
                    <td nowrap="nowrap"><?php echo 'No Mac Address'; ?></td>
                </tr>
                <?php endif; ?>
                <?php elseif($errorOffset == 'error offset') :?>
                    <tr>
                        <td nowrap="nowrap"><?= 'Error offset';?></td>
                    </tr>
                <?php endif; ?>
            <?php else :?>
                <tr>
                    <td nowrap="nowrap"><?= 'Error offset';?></td>
                </tr>
            <?php endif; ?>
        <?php else :?>
            <tr>
                <td nowrap="nowrap"><?= 'Result Table not found';?></td>
            </tr>
        <?php endif; ?>
    <?php else :?>
        <tr>
            <td nowrap="nowrap"><?= 'Wrong mac Address';?></td>
        </tr>
    <?php endif; ?>
</table>





<script>
    $(document).ready(function(){
        $("#get_tables").click(function(){
            $.ajax({
                url: 'telnet/index.php',
                data: {"eoc_ip": "<?=$eocIp?>"},
                beforeSend: function() {
                    $('.preloader').css('display', 'block')
                },
                success: function(result) {
                    $('.preloader').css('display', 'none')
                    $("#telnet_html").html(result);
                }
            });
        });

    });




</script>

<button id="get_tables">
    <span style="display: block; float: left">Telnet info</span>
    <img src="images/preloader.gif" alt="" class="preloader" style="display:none; margin-left: 10px; width: 15px; height: 15px; ">
</button>

<div id="telnet_html"></div>


</body>
</html>



