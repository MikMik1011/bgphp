<?php
require_once __DIR__ . "/../../src/service/bgpp_service.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['city'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'City parameter is required'
    ]);
    exit;
}
$city_key = $_GET['city'];

if (!isset($CITIES[$city_key])) {
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

$stations = get_stations($city_key);

$uid = $_GET['uid'];
$station = $stations[$uid] ?? null;

$arrivals = get_arrivals($city_key, $uid);

echo json_encode([
    'status' => 'success',
    'data' => [
        'station' => $station,
        'lines' => array_values($arrivals)
    ]
]);
