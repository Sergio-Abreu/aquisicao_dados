<?php
/* @var $this yii\web\View */
$this->title = 'My Yii Application';
?>
<script src="https://code.highcharts.com/stock/highstock.js"></script>
<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
<div class="site-index">
    <div class="jumbotron">
        <h1>Gráfico</h1>
    </div>
    <div class="body-content">
        <form>
            <!--<div class="form-group">
                <label for="exampleInputEmail1">Email address</label>
                <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
            </div>-->
            <div class="form-group">
                <label for="serial-ports">Portas</label>
                <select class="form-control" id="serial-ports">
                </select>
            </div>
            <div class="form-group">
                <label for="baud-rates">Baudrate</label>
                <select class="form-control" id="baud-rates">
                    <option>9600</option>
                    <option>19200</option>
                    <option>38400</option>
                    <option>57600</option>
                    <option>115200</option>
                </select>
            </div>
            <div class="form-check">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" checked="checked" id="check_sensor_1" onclick="toggleSensor(this.id, 0);">
                    Sensor 1
                </label>
            </div>
            <div class="form-check">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" checked="checked" id="check_sensor_2" onclick="toggleSensor(this.id, 1);">
                    Sensor 2
                </label>
            </div>
            <div class="form-check">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" checked="checked" id="check_sensor_3" onclick="toggleSensor(this.id, 2);">
                    Sensor 3
                </label>
            </div>
            <button type="button" class="btn btn-primary" onclick="connect();">Conectar</button>
            <button type="button" class="btn btn-danger" onclick="disconnect();">Cancelar</button>
        </form>
        <div id="container" style="height: 400px; min-width: 310px"></div>
    </div>
</div>
<script>
    var getSeries = function(name){
        return {
            name: name,
            data: (function () {
                var data = [],
                    time = (new Date()).getTime(),
                    i;
                for (i = -999; i <= 0; i += 1) {
                    data.push([time + i * 1000, 0]);
                }
                return data;
            }()),
            tooltip: {
                valueDecimals: 2
            },
            states: {
                hover: {
                    lineWidthPlus: 0
                }
            }
        }
    };
    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });
    var hc = Highcharts.stockChart('container', {
        rangeSelector: {
            buttons: [{
                count: 1,
                type: 'minute',
                text: '1M'
            }, {
                count: 5,
                type: 'minute',
                text: '5M'
            }, {
                type: 'all',
                text: 'All'
            }],
            inputEnabled: false,
            selected: 0
        },
        title: {
            text: 'Live random data'
        },
        xAxis: {
            type: 'datetime',
            tickPixelInterval: 150
        },
        yAxis: {
            title: {
                text: 'Temperatura'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        exporting: {
            enabled: true
        },
        series: [getSeries('Sensor 1'), getSeries('Sensor 2'), getSeries('Sensor 3')]
    });
    var addPointIntoSerie = function(x, y, index) {
        hc.series[index].addPoint([x, y], true, true);
    };
    var ws = new WebSocket('ws://localhost:8080/');
    ws.onopen = function() {
        document.body.style.backgroundColor = '#cfc';
    };
    ws.onclose = function() {
        document.body.style.backgroundColor = '#ff8982';
    };
    ws.onmessage = function(event) {
        var data = JSON.parse(event.data);
        switch (data.action) {
            case 'serialRead':
                for (var i=0; i<data.data.length; i++) {
                    addPointIntoSerie(data.data[i].timestamp, data.data[i].value, i);
                }
                break;
            case 'serialAvailable':
                for (var j=0; j<data.data.length; j++) {
                    var option = document.createElement("option");
                    option.text = data.data[j];
                    document.getElementById("serial-ports").add(option);
                }
                break;
            default:
                console.log(data);
        }
    };
    var toggleSensor = function(id, index) {
        hc.series[index].setVisible(document.getElementById(id).checked);
    };
    var connect = function() {
        ws.send(
            JSON.stringify(
                {
                    action: 'connect',
                    data: {
                        baudRate: document.getElementById('baud-rates').value,
                        serialPort:document.getElementById('serial-ports').value
                    }
                }
            )
        );
    };
    var disconnect = function() {
        ws.send(
            JSON.stringify(
                {
                    action: 'disconnect',
                    data: {}
                }
            )
        );
    };
</script>