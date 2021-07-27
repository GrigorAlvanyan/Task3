
<div style="margin-top: 20px;">
    <a href="javascript:void(0)" id="restartRouter">
        <img src="images/restart.png" alt="" style="width: 35px; height: 35px;">
    </a>
</div>



<?php if(!empty($dhcpResultArr)) : ?>
<div class="table_1">
    <table>
        <h2>DHCP Leases</h2>
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
    <div class="table_1">
        <h2>Wireless</h2>
        <table>
            <tr>
<!--                <td style="border-right: 1px solid transparent;">-->
<!--                <td style="text-align:center; width:32px; padding:3px"><img width=16 src='images/associated_icon.png'></td>-->
                <td class='even_th'><strong>SSID:</strong> <?php echo isset($wireless['SSID']) ? $wireless['SSID'] : ''?></td>
            </tr>
            <tr>
                <td class='even_th'><strong>Channel:</strong> <?php echo isset($wireless['Channel']) ? $wireless['Channel'] : ''?></td>
            </tr>
            <tr>
                <td class='even_th'><strong>Bitrate:</strong> <?php echo isset($wireless['Bitrate']) ? $wireless['Bitrate'] : ''?></td>
            </tr>
            <tr>
                <td class='even_th'><strong>BSSID:</strong> <?php echo isset($wireless['BSSID']) ? $wireless['BSSID'] : ''?></td>
            </tr>
            <tr>
                <td class='even_th'><strong>Encryption:</strong> <?php echo isset($wireless['Encryption']) ? $wireless['Encryption'] : ''?></td>
            </tr>
        </table>
    </div>
<?php endif; ?>


<?php if(!empty($nameOfMacAddress)) : ?>
    <div class="table_1">
        <table>
            <h2>Associated Stations</h2>
            <tr>
                <th style="border-right: 15px solid transparent;"></th>
                <th><b>Hostname</b></th>
                <th><b>Brand</b></th>
                <th><b>MAC-Address</b></th>
                <th><b>RX Rate</b></th>
                <th><b>TX Rate</b></th>
            </tr>
            <?php foreach ($nameOfMacAddress as $res) : ?>

                <tr>
                    <td style="border-right: 1px solid transparent;">
                    <td style='padding:0;width:15px;text-align:left'>
                        <img width=16 src='images/associated_icon.png' title='<?php echo $res['signal']; ?>' alt='<?php echo $res['signal']; ?>' >
                    </td>
                    <td class='even_th'><?php echo isset($res['hostName']) ? $res['hostName'] : ''?></td>
                    <td class='even_th'><?php echo isset($res['brand']) ? $res['brand'] : ''?></td>
                    <td class='even_th'><?php echo $res['mac'];?> </td>
                    <td class='even_th'><?php echo $res['rx']; ?></td>
                    <td class='even_th'><?php echo $res['tx']; ?></td>
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
                    data: {"eoc_ip": "<?=$eoc_ip?>", "restart": true},
                    beforeSend: function () {
                        //
                    },
                    success: function(result) {
                        // console.log(11);
                    }
                });
            }
        })
    });




</script>

