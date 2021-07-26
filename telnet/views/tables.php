
<?php if(!empty($dhcpResultArr)) : ?>
<div class="telnet_tables">
<!--<div class="table_1">-->
    <table>
        <h2> DHCP Leases </h2>
        <tr>
            <th><b>Hostname</b></th>
            <th><b>IPv4-Address</b></th>
            <th><b>MAC-Address</b></th>
            <th><b>Leasetime remaining</b></th>
        </tr>
        <?php foreach ($dhcpResultArr as $dhcpResult) : ?>
            <tr>
                <?php foreach ($dhcpResult as $res) : ?>
                    <td><?php echo $res; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>


    <div>
        <a href="javascript:void(0)" id="restartRouter">
            <img src="images/restart.png" alt="" style="width: 16px; height: 16px;">
        </a>
    </div>


<?php if(!empty($wireless)) : ?>
    <div class="wireless">
        <h2>Wireless</h2>
        <table>
            <tr>
                <td><strong>SSID:</strong> <?php echo isset($wireless['SSID']) ? $wireless['SSID'] : ''?></td>
            </tr>
            <tr>
                <td><strong>Channel:</strong> <?php echo isset($wireless['Channel']) ? $wireless['Channel'] : ''?></td>
            </tr>
            <tr>
                <td><strong>Bitrate:</strong> <?php echo isset($wireless['Bitrate']) ? $wireless['Bitrate'] : ''?></td>
            </tr>
            <tr>
                <td><strong>BSSID:</strong> <?php echo isset($wireless['BSSID']) ? $wireless['BSSID'] : ''?></td>
            </tr>
            <tr>
                <td><strong>Encryption:</strong> <?php echo isset($wireless['Encryption']) ? $wireless['Encryption'] : ''?></td>
            </tr>

        </table>
    </div>
<?php endif; ?>


<?php if(!empty($nameOfMacAddress)) : ?>
    <div class="associated_stations">
        <table>
            <h2>Associated Stations</h2>
            <tr>
                <th style="border-right: 1px solid transparent;"></th>
                <th><b>Hostname</b></th>
                <th><b>MAC-Address</b></th>
                <th><b>RX Rate</b></th>
                <th><b>TX Rate</b></th>
            </tr>
            <?php foreach ($nameOfMacAddress as $res) : ?>

                <tr >
                    <td style="border-right: 1px solid transparent;">
                        <img width=30 src='img/associated_icon.png'>
                    </td>
                    <td>
                        <?php echo $res['hostName']; ?>
                    </td>
                    <td>
                        <?php echo $res['mac']; ?>
                    </td>
                    <td><?php echo $res['rx']; ?></td>
                    <td><?php echo $res['tx']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php endif; ?>



<script>
    $(document).ready(function(){

        $('#restartRouter').click(function() {
            if (confirm('Вы уверены что хотите перезагрузить роутер?')) {
                $.ajax({
                    url: 'telnet/index.php',
                    data: {"eoc_ip": "<?=$eocIp?>"},
                    success: function(result) {
                        $("#telnet_html").html(result);
                    }
                });
            }
        })

    });




</script>

