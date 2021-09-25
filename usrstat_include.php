<?php
error_reporting(E_ALL);

define('ROOT_DIR', __DIR__);


require_once ROOT_DIR . '/helpers.php';
require_once ROOT_DIR . '/src/db/functions.php';
require_once ROOT_DIR . '/src/app/functions.php';

$configs = getConfigs();

$severityStatuses = $configs['severityStatuses'];
$errorsMessage = $configs['error_messages'];
$configIdataRanges = isset($configs['idata_ranges']) ? $configs['idata_ranges'] : [];
$filteredNames = isset($configs['filteredNames']) ? $configs['filteredNames'] : [];
$configTdataRanges = isset($configs['tdata_ranges']) ? $configs['tdata_ranges'] : [];

if (isLocal()) {
    $eoc_ip = $row[15];
} else {
    $eoc_ip = str_replace(';','<br>',trim(filter($row[15]),';'));
}

$nodeName = '';
$severityValue = [];

$connection = dbConnection($configs['db_params'], null);

if (isLocal()) {
    $nodeName = $personalinfo[2];
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

if (!empty($objectProp)) {
    if (isLocal()) {
        if(isset($personalinfo[43]) && !empty($personalinfo[43])){
            $macName = $personalinfo[43];
            if(strlen(implode('', explode(':', trim($macName)))) == 12){
                $eoc_mac = implode('', explode(':', trim($macName)));
                $macAddressesValue = getMaccAddress($connection,$eoc_mac, $objectProp, $errorsMessage);
            } else {
                $macAddressesValue['error'] =  $errorsMessage['mac_address_errors']['wrong_mac_address'];
            }
        } else {
            $macAddressesValue['error'] = $errorsMessage['mac_address_errors']['no_mac_address'];
        }
    } else {
        if (isset($personalinfo[43])) {
            if (strpos($personalinfo[43], "375828706861857")) {
                $eoc_tmp_array = explode("**", substr($personalinfo[43], strpos($personalinfo[43], "7375828706861857"), 38));
                if(isset($eoc_tmp_array[2]) && !empty($eoc_tmp_array[2])) {
                    if (strlen(str_replace(":", "", $eoc_tmp_array[2])) == 12) {
                        $eoc_mac = str_replace(":", "", $eoc_tmp_array[2]);
                        $macAddressesValue = getMaccAddress($connection, $eoc_mac, $objectProp, $errorsMessage);
                    } else {
                        $macAddressesValue['error'] =  $errorsMessage['mac_address_errors']['wrong_mac_address'];
                    }
                }
                else {
                    $macAddressesValue['error'] = $errorsMessage['mac_address_errors']['no_mac_address'];
                }
            }
        }
    }
} else {
    $macAddressesValue['error'] = $errorsMessage['mac_address_errors']['not_found'];
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link rel="stylesheet" href="S1/css/staticStyle.css" type="text/css"/>
<script src="S1/telnet/views/xhr.js"></script>

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

        <td style='padding:0;width:15px;text-align:center'><img width=16 src='img/err_<?= $severity ?>.png' alt='<?= $severity ?>' title='<?= $severity ?>'></td>
    </tr>

    <?php if (isset($results['alarm_events'])) : ?>
        <?php foreach ($results['alarm_events'] as $value)  : ?>
            <tr>
                <td class='even_th'><b>Тревога</b>:</td>
                <td><?= $value['message'] . " " . $value['event_timestamp'] ?></td>
                <td style='padding:0;width:15px;text-align:center'><img width=16 src='img/err_<?= $value['severity_name'] ?>.png' alt='<?= $value['severity_name'] ?>' title='<?= $severity ?>'></td>
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
                <td style='padding:0;width:15px;text-align:center'>
                    <?php if (!empty($valueImg)) : ?>
                        <img width=16 src='img/err_<?= $valueImg ?>.png' alt='<?= $valueImg ?>' title='<?= $valueImg ?>'>
                    <?php endif;?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if(isset($macAddressesValue['error'])) : ?>
        <?php foreach ($configs['error_messages']['mac_address_errors'] as $error) :?>
            <?php if($error == $macAddressesValue['error'] ) :?>
                <tr>
                    <td nowrap="nowrap"><?= $macAddressesValue['error'];?></td>
                </tr>
            <?php endif ?>
        <?php endforeach;?>
    <?php else: ?>
        <?php foreach ($macAddressesValue as $line) : ?>
            <?php
            $tdValues =[];
            foreach ($line as $key => $str) {
                $tdValues = getMacAddressValue($excludeKeys, $str, $configTdataRanges, $line, $key);
                $tdValues = isset($tdValues) && !empty($tdValues) ? $tdValues : '';
            }
            ?>
            <tr style="vertical-align: top;">
                <td class='even_th' title="<?= $line['id'] ?>"><?= $line['Table_Name'] ?></td>
                <td>
                    <?php foreach ($tdValues as  $td) : ?>
                        <div><?=  $td['strValue'] ?></div>
                    <?php endforeach; ?>
                </td>
                <td style='padding:0;width:15px;text-align:center'>
                    <?php foreach ($tdValues as  $td) : ?>
                        <div>
                            <img width=16 src='img/err_<?= $td['macStatus'] ?>.png' alt='<?= $td['macStatus'] ?>' title='<?= $td['macStatus'] ?>'>
                        </div>
                    <?php endforeach; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    <tr>
        <td>
            <button id="get_tables" title="Telnet Info" >
                <span style="display: block; float: left">Telnet info</span>
                <img src="img/preloader.gif" alt="Telnet Info"  class="preloader" style="display:none; margin-left: 10px; width: 15px; height: 15px;" >
            </button>
        </td>
        <td>
            <div id="speedTest" style="float: left">
                <a href="javascript:void(0)">
                    <img src="img/speed_test.png" alt="Make SpeedTest" style="width: 35px; height: 35px;" title="Make SpeedTest">
                    <img src="img/preloader.gif"  class="SpeedTestpreloader" style="display:none; margin-left: 10px; width: 15px; height: 15px;" >
                </a>
            </div>
            <div id="Traffic" >
                <a href="javascript:void(0)">
                    <img src="img/allstat.png" alt="Show Graphs" style="width: 35px; height: 35px;" title="Show Graphs">
                </a>
            </div>

        </td>
        <td>
            <div id="restartRouter">
                <a href="javascript:void(0)">
                    <img src="img/restart.png" alt="Reboot" style="width: 35px; height: 35px;" title="Reboot">
                </a>
            </div>
        </td>
    </tr>
</table>

<script>


    $(document).ready(function(){
        $("#get_tables").click(function(){
            $.ajax({
                url: "<?=getPathTo('S1/telnet/index.php')?>",
                data: {"eoc_ip": "<?=$eoc_ip?>"},
                beforeSend: function() {
                    $('.preloader').css('display', 'block')
                    $('a, button').css({'pointer-events': 'none', 'cursor': 'no-drop'})
                },
                success: function(result) {
                    $('a, button').css({'pointer-events': 'auto', 'cursor': 'pointer'})
                    $('#SpeedTest a').css({'pointer-events': 'none', 'cursor': 'no-drop'})
                    $("#traffic_html").empty()
                    $("#Speed, .preloader").css('display', 'none')
                    $("#telnet_html").html(result);
                }
            });
        });


        $('#restartRouter a').click(function() {
            if (confirm('Вы уверены что хотите перезагрузить роутер?')) {
                $.ajax({
                    url: "<?php echo getPathTo('S1/telnet/index.php')?>",
                    data: {"eoc_ip": "<?=$eoc_ip?>", "restart": true},
                    beforeSend: function () {
                        $('a, button').css({'pointer-events': 'none', 'cursor': 'no-drop'})
                    },
                    success: function(result) {
                        $("#traffic_html, #telnet_html").empty()
                        $("#Speed").css('display', 'none')
                        $('a, button').css({'pointer-events': 'none', 'cursor': 'no-drop'})
                        $('.countdown').css('display', 'block')
                        var countdown = 45;
                        setInterval(function() {
                            countdown--;
                            var view = '00:' + countdown;
                            if (countdown < 10) {
                                view = '00:0' + countdown
                            }
                            if (countdown >= 0) {
                                $('.countdown').html(view)
                            }
                            if (countdown === 0) {
                                $('.countdown').css('display', 'none')
                                $("#telnet_html").html('<em>rebooted</em>')
                                $('a, button').css({'pointer-events': 'auto', 'cursor': 'pointer'})
                                $('#SpeedTest a').css({'pointer-events': 'none', 'cursor': 'no-drop'})
                            }
                        }, 1000)
                    }
                });
            }

        })

        $('#Traffic a').click(function() {
            $.ajax({
                url: "<?php echo getPathTo('S1/telnet/views/traffic.php')?>",
                data: {"eoc_ip": "<?=$eoc_ip?>", "traffic_url": "<?=getPathTo('S1/telnet/gettraffic.php')?>",
                    "svg": "<?=getPathTo('S1/telnet/bandwidth.svg')?>"},
                beforeSend: function () {
                    //
                },
                success: function(result) {
                    $("#telnet_html,#countdownSpeedTest").empty();
                    $("#SpeedTest a").css({'pointer-events': 'auto', 'cursor': 'pointer'})
                    $("#traffic_html").html(result);
                }
            });
        })
        $('#speedTest a').click(function() {
            $.ajax({
                url: "<?php echo getPathTo('S1/telnet/speedtest.php')?>",
                data: {"eoc_ip": "<?=$eoc_ip?>"},
                beforeSend: function () {
                    $("#telnet_html").empty();
                    $('a, button').css({'pointer-events': 'none', 'cursor': 'no-drop'})
                    $('.SpeedTestpreloader').css('display', 'block')
                },
                success: function(result) {
                    $("#telnet_html").empty();
                    $('a, button').css({'pointer-events': 'auto', 'cursor': 'pointer'})
                    $("#Speed").css('display', 'block')
                    $('.SpeedTestpreloader').css('display', 'none')
                    $("#Speedtest").html(result);
                }
            });
        })

    });

</script>

<div id="telnet_html"></div>
<div id="traffic_html"></div>
<div class="countdown"></div>
<div id="Speed" style="display: none">
    <textarea id="Speedtest" name="speedTest" rows="8" cols="100"></textarea>
</div>







