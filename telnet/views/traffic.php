<?php

define('ROOT_DIR', __DIR__);


require_once ROOT_DIR . '/../../helpers.php';
require_once ROOT_DIR . '/../app/functions.php';
$configs = include ROOT_DIR . '/../../config.php';
$invalidIp = $configs['error_messages']['invalid_ip_address'];

$eoc_ip = $_GET['eoc_ip'];
$svg = $_GET['svg'];

$ip = validateIp($eoc_ip);

?>


<?php if($ip === true) : ?>
    <script type="text/javascript">

            // <![CDATA[
            var bwxhr = new XHR();

            var G;
            var TIME = 0;
            var RXB = 1;
            var RXP = 2;
            var TXB = 3;
            var TXP = 4;

            var width = 760;
            var height = 300;
            var step = 5;

            var data_wanted = Math.floor(width / step);
            var data_fill = 0;
            var data_stamp = 0;

            var data_rx = [];
            var data_tx = [];

            var line_rx;
            var line_tx;

            var label_25;
            var label_50;
            var label_75;

            var label_rx_cur;
            var label_rx_avg;
            var label_rx_peak;

            var label_tx_cur;
            var label_tx_avg;
            var label_tx_peak;

            var label_scale;

            String.prototype.format = function () {
                if (!RegExp)
                    return;

                var html_esc = [/&/g, '&#38;', /"/g, '&#34;', /'/g, '&#39;', /</g, '&#60;', />/g, '&#62;'];
                var quot_esc = [/"/g, '&#34;', /'/g, '&#39;'];

                function esc(s, r) {
                    for (var i = 0; i < r.length; i += 2)
                        s = s.replace(r[i], r[i + 1]);
                    return s;
                }

                var str = this;
                var out = '';
                var re = /^(([^%]*)%('.|0|\x20)?(-)?(\d+)?(\.\d+)?(%|b|c|d|u|f|o|s|x|X|q|h|j|t|m))/;
                var a = b = [], numSubstitutions = 0, numMatches = 0;

                while (a = re.exec(str)) {
                    var m = a[1];
                    var leftpart = a[2], pPad = a[3], pJustify = a[4], pMinLength = a[5];
                    var pPrecision = a[6], pType = a[7];

                    numMatches++;

                    if (pType == '%') {
                        subst = '%';
                    } else {
                        if (numSubstitutions < arguments.length) {
                            var param = arguments[numSubstitutions++];

                            var pad = '';
                            if (pPad && pPad.substr(0, 1) == "'")
                                pad = leftpart.substr(1, 1);
                            else if (pPad)
                                pad = pPad;

                            var justifyRight = true;
                            if (pJustify && pJustify === "-")
                                justifyRight = false;

                            var minLength = -1;
                            if (pMinLength)
                                minLength = parseInt(pMinLength);

                            var precision = -1;
                            if (pPrecision && pType == 'f')
                                precision = parseInt(pPrecision.substring(1));

                            var subst = param;

                            switch (pType) {
                                case 'b':
                                    subst = (parseInt(param) || 0).toString(2);
                                    break;

                                case 'c':
                                    subst = String.fromCharCode(parseInt(param) || 0);
                                    break;

                                case 'd':
                                    subst = (parseInt(param) || 0);
                                    break;

                                case 'u':
                                    subst = Math.abs(parseInt(param) || 0);
                                    break;

                                case 'f':
                                    subst = (precision > -1)
                                        ? ((parseFloat(param) || 0.0)).toFixed(precision)
                                        : (parseFloat(param) || 0.0);
                                    break;

                                case 'o':
                                    subst = (parseInt(param) || 0).toString(8);
                                    break;

                                case 's':
                                    subst = param;
                                    break;

                                case 'x':
                                    subst = ('' + (parseInt(param) || 0).toString(16)).toLowerCase();
                                    break;

                                case 'X':
                                    subst = ('' + (parseInt(param) || 0).toString(16)).toUpperCase();
                                    break;

                                case 'h':
                                    subst = esc(param, html_esc);
                                    break;

                                case 'q':
                                    subst = esc(param, quot_esc);
                                    break;

                                case 'j':
                                    subst = String.serialize(param);
                                    break;

                                case 't':
                                    var td = 0;
                                    var th = 0;
                                    var tm = 0;
                                    var ts = (param || 0);

                                    if (ts > 60) {
                                        tm = Math.floor(ts / 60);
                                        ts = (ts % 60);
                                    }

                                    if (tm > 60) {
                                        th = Math.floor(tm / 60);
                                        tm = (tm % 60);
                                    }

                                    if (th > 24) {
                                        td = Math.floor(th / 24);
                                        th = (th % 24);
                                    }

                                    subst = (td > 0)
                                        ? String.format('%dd %dh %dm %ds', td, th, tm, ts)
                                        : String.format('%dh %dm %ds', th, tm, ts);

                                    break;

                                case 'm':
                                    var mf = pMinLength ? parseInt(pMinLength) : 1000;
                                    var pr = pPrecision ? Math.floor(10 * parseFloat('0' + pPrecision)) : 2;

                                    var i = 0;
                                    var val = parseFloat(param || 0);
                                    var units = ['', 'K', 'M', 'G', 'T', 'P', 'E'];

                                    for (i = 0; (i < units.length) && (val > mf); i++)
                                        val /= mf;

                                    subst = val.toFixed(pr) + ' ' + units[i];
                                    break;
                            }
                        }
                    }

                    out += leftpart + subst;
                    str = str.substr(m.length);
                }

                return out + str;
            }
            String.format = function () {
                var a = [];
                for (var i = 1; i < arguments.length; i++)
                    a.push(arguments[i]);
                return ''.format.apply(arguments[0], a);
            }

            function bandwidth_label(bytes, br) {
                //console.log("bytes:"+bytes);
                var uby = 'kB/s';
                var kby = (bytes / 1024);


                if (kby >= 1024) {
                    uby = 'MB/s';
                    kby = kby / 1024;
                }

                var ubi = 'kbit/s';
                var kbi = (bytes * 8 / 1024);

                if (kbi >= 1024) {
                    ubi = 'Mbit/s';
                    kbi = kbi / 1024;
                }

                return (String.format("%f %s%s(%f %s)",
                    kbi.toFixed(2), ubi,
                    br ? '<br />' : ' ',
                    kby.toFixed(2), uby
                ));
            }

            /* wait for SVG */
            window.setTimeout(
                function () {
                    var svg = document.getElementById('bwsvg');
                    try {
                        G = svg.getSVGDocument
                            ? svg.getSVGDocument() : svg.contentDocument;
                    } catch (e) {
                        G = document.embeds['bwsvg'].getSVGDocument();
                    }
                    if (!G) {
                        window.setTimeout(arguments.callee, 1000);
                    } else {
                        /* find sizes */
                        width = svg.offsetWidth - 2;
                        height = svg.offsetHeight - 2;
                        data_wanted = Math.ceil(width / step);

                        /* prefill datasets */
                        for (var i = 0; i < data_wanted; i++) {
                            data_rx[i] = 0;
                            data_tx[i] = 0;
                        }

                        /* find svg elements */
                        line_rx = G.getElementById('rx');
                        line_tx = G.getElementById('tx');

                        label_25 = G.getElementById('label_25');
                        label_50 = G.getElementById('label_50');
                        label_75 = G.getElementById('label_75');

                        label_rx_cur = document.getElementById('rx_bw_cur');
                        label_rx_avg = document.getElementById('rx_bw_avg');
                        label_rx_peak = document.getElementById('rx_bw_peak');

                        label_tx_cur = document.getElementById('tx_bw_cur');
                        label_tx_avg = document.getElementById('tx_bw_avg');
                        label_tx_peak = document.getElementById('tx_bw_peak');

                        label_scale = document.getElementById('scale');


                        /* plot horizontal time interval lines */
                        for (var i = width % (step * 60); i < width; i += step * 60) {
                            var line = G.createElementNS('http://www.w3.org/2000/svg', 'line');
                            line.setAttribute('x1', i);
                            line.setAttribute('y1', 0);
                            line.setAttribute('x2', i);
                            line.setAttribute('y2', '100%');
                            line.setAttribute('style', 'stroke:black;stroke-width:0.1');

                            var text = G.createElementNS('http://www.w3.org/2000/svg', 'text');
                            text.setAttribute('x', i + 5);
                            text.setAttribute('y', 15);
                            text.setAttribute('style', 'fill:#999999; font-size:9pt');
                            text.appendChild(G.createTextNode(Math.round((width - i) / step / 60) + 'minute'));

                            label_25.parentNode.appendChild(line);
                            label_25.parentNode.appendChild(text);
                        }


                        label_scale.innerHTML = String.format('(%d minute window, %d second interval)', data_wanted / 60, 3);


                        /* render datasets, start update interval */
                        XHR.poll(3, "<?= $_GET['traffic_url'] ?>", {"eoc_ip": "<?= $_GET['eoc_ip'] ?>"},
                            function (data, x) {

                                data = JSON.parse(data.responseText);

                                var data_max = 0;
                                var data_scale = 0;

                                var data_rx_avg = 0;
                                var data_tx_avg = 0;

                                var data_rx_peak = 0;
                                var data_tx_peak = 0;

                                for (var i = data_stamp ? 0 : 1; i < data.length; i++) {
                                    /* skip overlapping entries */
                                    if (data[i][TIME] <= data_stamp)
                                        continue;

                                    /* normalize difference against time interval */
                                    if (i > 0) {
                                        var time_delta = data[i][TIME] - data[i - 1][TIME];
                                        if (time_delta) {
                                            data_rx.push((data[i][RXB] - data[i - 1][RXB]) / time_delta);
                                            data_tx.push((data[i][TXB] - data[i - 1][TXB]) / time_delta);
                                        }
                                    }
                                }

                                /* cut off outdated entries */
                                data_rx = data_rx.slice(data_rx.length - data_wanted, data_rx.length);
                                data_tx = data_tx.slice(data_tx.length - data_wanted, data_tx.length);

                                /* find peak */
                                for (var i = 0; i < data_rx.length; i++) {
                                    data_max = Math.max(data_max, data_rx[i]);
                                    data_max = Math.max(data_max, data_tx[i]);

                                    data_rx_peak = Math.max(data_rx_peak, data_rx[i]);
                                    data_tx_peak = Math.max(data_tx_peak, data_tx[i]);

                                    if (i > 0) {
                                        data_rx_avg = (data_rx_avg + data_rx[i]) / 2;
                                        data_tx_avg = (data_tx_avg + data_tx[i]) / 2;
                                    } else {
                                        data_rx_avg = data_rx[i];
                                        data_tx_avg = data_tx[i];
                                    }
                                }

                                /* remember current timestamp, calculate horizontal scale */
                                data_stamp = data[data.length - 1][TIME];
                                data_scale = height / (data_max * 1.1);


                                /* plot data */
                                var pt_rx = '0,' + height;
                                var pt_tx = '0,' + height;

                                var y_rx = 0;
                                var y_tx = 0;

                                for (var i = 0; i < data_rx.length; i++) {
                                    var x = i * step;

                                    y_rx = height - Math.floor(data_rx[i] * data_scale);
                                    y_tx = height - Math.floor(data_tx[i] * data_scale);

                                    pt_rx += ' ' + x + ',' + y_rx;
                                    pt_tx += ' ' + x + ',' + y_tx;
                                }

                                pt_rx += ' ' + width + ',' + y_rx + ' ' + width + ',' + height;
                                pt_tx += ' ' + width + ',' + y_tx + ' ' + width + ',' + height;


                                line_rx.setAttribute('points', pt_rx);
                                line_tx.setAttribute('points', pt_tx);

                                label_25.firstChild.data = bandwidth_label(1.1 * 0.25 * data_max);
                                label_50.firstChild.data = bandwidth_label(1.1 * 0.50 * data_max);
                                label_75.firstChild.data = bandwidth_label(1.1 * 0.75 * data_max);

                                label_rx_cur.innerHTML = bandwidth_label(data_rx[data_rx.length - 1], true);
                                label_tx_cur.innerHTML = bandwidth_label(data_tx[data_tx.length - 1], true);

                                label_rx_avg.innerHTML = bandwidth_label(data_rx_avg, true);
                                label_tx_avg.innerHTML = bandwidth_label(data_tx_avg, true);

                                label_rx_peak.innerHTML = bandwidth_label(data_rx_peak, true);
                                label_tx_peak.innerHTML = bandwidth_label(data_tx_peak, true);

                            }
                        );

                    }
                }, 1000
            );// ]]>

    </script>
    <h2><a id="content" name="content">Realtime Monitor</a></h2>



    <embed id="bwsvg" style="width:100%; height:300px; border:1px solid #000000; background-color:#FFFFFF" src="<?=$svg?>" />
<div style="text-align:right"><small id="scale">-</small></div>
<br />

<table style="width:100%; table-layout:fixed" cellspacing="5">
	<tr>
		<td style="text-align:right; vertical-align:top"><strong style="border-bottom:2px solid blue">Down</strong></td>
		<td id="rx_bw_cur">0 kbit/s<br />(0 kB/s)</td>

		<td style="text-align:right; vertical-align:top"><strong>Average:</strong></td>
		<td id="rx_bw_avg">0 kbit/s<br />(0 kB/s)</td>

		<td style="text-align:right; vertical-align:top"><strong>Peak:</strong></td>
		<td id="rx_bw_peak">0 kbit/s<br />(0 kB/s)</td>
	</tr>
	<tr>
		<td style="text-align:right; vertical-align:top"><strong style="border-bottom:2px solid green">Upstream</strong></td>
		<td id="tx_bw_cur">0 kbit/s<br />(0 kB/s)</td>

		<td style="text-align:right; vertical-align:top"><strong>Average:</strong></td>
		<td id="tx_bw_avg">0 kbit/s<br />(0 kB/s)</td>

		<td style="text-align:right; vertical-align:top"><strong>Peak:</strong></td>
		<td id="tx_bw_peak">0 kbit/s<br />(0 kB/s)</td>
	</tr>
</table>



<?php else: ?>
    <?= $invalidIp ?>
<?php endif; ?>