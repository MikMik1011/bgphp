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

$stations = get_stations($city_key);

echo json_encode([
    'status' => 'success',
    'data' => array_values($stations)
]);
