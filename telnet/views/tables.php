
<?php if(!empty($uciLines)) : ?>
    <?php foreach ($uciLines as $line) : ?>
        <table class="table_1">
            <tr>
                <th colspan=2><b>Timestamp</b></th>
            </tr>
            <tr>
                <td class='even_th' width="250px">Local Time</td>
                <td><?= $line ?></td>
            </tr>
        </table>
    <?php endforeach; ?>
<?php endif; ?>




<?php if(!empty($uptimeResultLine)) : ?>
<table class="table_1">
    <tr>
        <th colspan=2><b>System</b></th>
    </tr>
    <tr>
        <td class='even_th' width="250px">Local Time</td>
        <td><?= $localTimeResultLine ?></td>
    </tr>
    <tr>
        <td class='even_th' width="250px">Uptime</td>
        <td><?= $uptimeResultLine ?></td>
    </tr>

</table>
<?php endif; ?>

<?php if(!empty($dhcpResultArr)) : ?>

    <table class="table_1">
        <tr>
            <th><b>DHCP Leases</b></th>
        </tr>
        <tr>
            <th><b>Hostname</b></th>
            <th><b>IPv4-Address</b></th>
            <th><b>MAC-Address</b></th>
            <th><b>Leasetime remaining</b></th>
        </tr>
        <?php foreach ($dhcpResultArr as $dhcpResult) : ?>
            <tr>
                <?php foreach ($dhcpResult as $res) : ?>
                    <td class='even_th'><?php echo $res; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>


<?php if(!empty($wireless)) : ?>
    <table  class="table_1">
        <tbody>
        <tr>
            <td width="10%"></td>
            <td>
                <table>
                    <tbody>
                    <tr>
                        <td style='padding:0;width:15px; vertical-align: top;'>
                            <img width=16 src="img/<?= $signal?>.png">
                        </td>
                        <td class='even_th'>
                                <strong>SSID:</strong><?php echo isset($wireless['SSID']) ? $wireless['SSID'] : ''?>
                                <br>
                                <strong>Channel:</strong><?php echo isset($wireless['Channel']) ? $wireless['Channel'] : ''?>
                                <br>
                                <strong>Bitrate:</strong><?php echo isset($wireless['Bitrate']) ? $wireless['Bitrate'] : ''?>
                                <br>
                                <strong>BSSID:</strong><?php echo isset($wireless['BSSID']) ? $wireless['BSSID'] : ''?>
                                <br>
                                <strong>Encryption:</strong><?php echo isset($wireless['Encryption']) ? $wireless['Encryption'] : ''?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>

<?php endif; ?>


<?php if(!empty($nameOfMacAddress)) : ?>
        <table class="table_1">
            <tr>
                <th><b>Associated Stations</b></th>
            </tr>
            <tr>
                <th style="width:20px"></th>
                <th><b>Hostname</b></th>
                <th><b>Brand</b></th>
                <th><b>MAC-Address</b></th>
                <th><b>RX Rate</b></th>
                <th><b>TX Rate</b></th>
            </tr>
            <?php foreach ($nameOfMacAddress as $res) : ?>

                <tr>
                    <td style='padding:0;width:15px;text-align: center'>
                        <img width=16 src='img/<?= $signal?>.png' title='<?php echo $res['signal']; ?>' alt='<?php echo $res['signal']; ?>'>
                    </td>
                    <td class='even_th'><?php echo isset($res['hostName']) ? $res['hostName'] : ''?></td>
                    <td class='even_th'><?php echo isset($res['brand']) ? $res['brand'] : ''?></td>
                    <td class='even_th'><?php echo $res['mac'];?> </td>
                    <td class='even_th'><?php echo $res['rx']; ?></td>
                    <td class='even_th'><?php echo $res['tx']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>



