<?php

echo json_encode(['action' => 'serialAvailable', 'data' => ['com1', 'com2', 'tty/tl']]).PHP_EOL;

/**
 * @return array
 */
function generateSensorData($id): array
{
    return [
        'sensorId'  => $id,
        'timestamp' => (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp() * 1000,
        'value'     => rand(0, 10)
    ];
}

$sendData = false;
while (true) {
    $read = [STDIN];
    $write = null;
    $except = null;
    $stream = stream_select($read, $write, $except, 0, 200000);
    if (!(false === $stream || $stream <= 0) && !empty($line = trim(fgets(STDIN)))) {
        $sendData = true;
        echo $line.PHP_EOL;
    }
    if (!$sendData) {
        continue;
    }
    $data = [];
    $data[] = generateSensorData(1);
    $data[] = generateSensorData(2);
    $data[] = generateSensorData(3);
    echo json_encode(['action' => 'serialRead', 'data' => $data]).PHP_EOL;
    usleep(1000000);
}