<?php
require_once "../_service/service.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['city'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'City parameter is required'
    ]);
    exit;
}
$cityKey = $_GET['city'];

$city = $CITIES[$cityKey];

if (!isset($CITIES[$cityKey])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'City not found'
    ]);
    exit;
}

if (!isset($_GET['uid'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Station UID parameter is required'
    ]);
    exit;
}

$stations = get_stations($cityKey);

$uid = $_GET['uid'];
$station = $stations[$uid] ?? null;

$arrivals = get_arrivals($cityKey, $uid);

echo json_encode([
    'status' => 'success',
    'data' => [
        'station' => $station,
        'lines' => array_values($arrivals)
    ]
]);
