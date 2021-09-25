<table class="table_1">
    <tr>
        <th colspan=2><b>System</b></th>
    </tr>
    <?php if(!empty($modelResult)) :?>
        <tr>
            <td class='even_th' width="250px">Model</td>
            <td><?= $modelResult ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($hardwareVersion)) :?>
        <tr>
            <td class='even_th' width="250px">Hardware Version</td>
            <td><?= $hardwareVersion ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($firmwareVersion)) :?>
        <tr>
            <td class='even_th' width="250px">Firmware Version</td>
            <td><?= $firmwareVersion ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($softwareVersion)) :?>
        <tr>
            <td class='even_th' width="250px">Software Version</td>
            <td><?= $softwareVersion ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($serialNumber)) :?>
        <tr>
            <td class='even_th' width="250px">Serial Number</td>
            <td><?= $serialNumber ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($localTimeResultLine)) :?>
        <tr>
            <td class='even_th' width="250px">Local Time</td>
            <td><?= $localTimeResultLine ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($uptimeResultLine)) :?>
        <tr>
            <td class='even_th' width="250px">Uptime</td>
            <td><?= $uptimeResultLine ?></td>
        </tr>
    <?php endif; ?>
</table>




<?php if(!empty($networks)) : ?>
    <table class="table_1">
        <tr>
            <th colspan=3><b>Network</b></th>
        </tr>
        <tr>
            <td>IPv4 WAN Status</td>
            <td style='padding:0;width:15px;text-align: center;'><img width=16 src="img/network.png"></td>
            <td><strong>Service WAN</strong></td>
        </tr>
        <?php if(!empty($networks['Type'])) :?>
            <tr>
                <?php if(!empty($portsInfoResult)) :?>
                    <td align="center">
                        <?php foreach ($portsInfoResult as $port) :?>
                            <img width=16 src='img/<?= $port['signal'] ?>'  title='<?= $port['info'] ?>'>
                        <?php endforeach;?>
                    </td>
                <?php else: ?>
                    <td></td>
                <?php endif ?>
                <td></td>
                <td><strong>Type: </strong><?= $networks['Type'] ?></td>
            </tr>
        <?php endif; ?>
        <?php if(!empty($networks['Type'])) :?>
            <tr>
                <td></td>
                <td></td>
                <td><strong>Address: </strong><?= $networks['Address'] ?></td>
            </tr>
        <?php endif; ?>
        <?php if(!empty($networks['Type'])) :?>
            <tr>
                <td></td>
                <td></td>
                <td><strong>Netmask: </strong><?= $networks['Netmask'] ?></td>
            </tr>
        <?php endif; ?>
        <?php if(!empty($networks['Type'])) :?>
            <tr>
                <td></td>
                <td></td>
                <td><strong>Gateway: </strong><?= $networks['Gateway'] ?></td>
            </tr>
        <?php endif; ?>
        <?php if(isset($networks['DNS']) &&  !empty($networks['DNS'])) : ?>
            <?php foreach ($networks['DNS'] as $key => $dns) : ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td><strong><?= $key ?></strong><?= $dns ?></td>
                </tr>
            <?php endforeach ?>
        <?php endif; ?>
        <?php if(!empty($networks['Type'])) :?>
            <tr>
                <td></td>
                <td></td>
                <td><strong>Connected: </strong><?= $networks['uptime'] ?></td>
            </tr>
        <?php endif; ?>
    </table>
<?php endif; ?>





<table class="table_1">
    <tr>
        <th colspan=5><b>DHCP Leases</b></th>
    </tr>
    <tr>
        <th><b>Hostname</b></th>
        <th><b>Brand</b></th>
        <th><b>IPv4-Address</b></th>
        <th><b>MAC-Address</b></th>
        <th><b>Leasetime remaining</b></th>
    </tr>
    <?php if(!empty($dhcpResultArr)) : ?>
        <?php foreach ($dhcpResultArr as $dhcpResult) : ?>
            <tr>
                    <td><?php echo  isset($dhcpResult['hostname']) ? $dhcpResult['hostname'] : ''; ?></td>
                    <td><?php echo  isset($dhcpResult['brand']) ? $dhcpResult['brand'] : ''; ?></td>
                    <td><?php echo  isset($dhcpResult['ip']) ? $dhcpResult['ip'] : ''; ?></td>
                    <td><?php echo  isset($dhcpResult['macAddress']) ? $dhcpResult['macAddress'] : ''; ?></td>
                    <td><?php echo  isset($dhcpResult['timestamp']) ? $dhcpResult['timestamp'] : ''; ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else:?>
        <tr>
            <td><em>There are no active leases.</em></td>
        </tr>
    <?php endif; ?>
</table>



<?php if(!empty($wireless)) : ?>
    <table  class="table_1">
        <th colspan=2><b>WLAN</b></th>
            <tr>
                <?php if(!empty($qualitySignal)) : ?>
                <td style='padding:0;width:15px;text-align: center'>
                    <div class="signal <?=$qualitySignal['icon'];?>" title="<?= $wirelesSig ?>">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                <?php endif; ?>
                <td><strong>SSID:</strong><?php echo isset($wireless['SSID']) ? $wireless['SSID'] : ''?></td>
            </tr>
            <tr>
                <td><small><?= $qualitySignal['result']?></small></td>
                <td><strong>Channel:</strong><?php echo isset($wireless['Channel']) ? $wireless['Channel'] : ''?></td>
            </tr>
        <tr>
            <td></td>
            <td><strong>Bitrate:</strong><?php echo isset($wireless['Bitrate']) ? $wireless['Bitrate'] : ''?></td>
        </tr>
        <tr>
            <td></td>
            <td><strong>BSSID:</strong><?php echo isset($wireless['BSSID']) ? $wireless['BSSID'] : ''?></td>
        </tr>
        <tr>
            <td></td>
            <td><strong>Encryption:</strong><?php echo isset($wireless['Encryption']) ? $wireless['Encryption'] : ''?></td>
        </tr>
    </table>
<?php endif; ?>




<table class="table_1">
    <?php if(!empty($nameOfMacAddress)) : ?>
    <tr>
        <th colspan=6><b>Associated Stations</b></th>
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
                <td style='padding:0;width:15px;'>
                    <div class="signal <?=$res['dBmSignal'];?>" title="<?= $res['signal']?>">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </td>
                <td><?php echo isset($res['hostName']) ? $res['hostName'] : ''?></td>
                <td><?php echo isset($res['brand']) ? $res['brand'] : ''?></td>
                <td><?php echo $res['mac'];?> </td>
                <td><?php echo $res['rx']; ?></td>
                <td><?php echo $res['tx']; ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <th colspan=5><b>Associated Stations</b></th>
        </tr>
        <tr>
            <th><b>Hostname</b></th>
            <th><b>Brand</b></th>
            <th><b>MAC-Address</b></th>
            <th><b>RX Rate</b></th>
            <th><b>TX Rate</b></th>
        </tr>
        <tr>
            <td><em>No information available</em></td>
        </tr>
    <?php endif; ?>
</table>
