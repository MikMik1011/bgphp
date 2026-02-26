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

if (!isset($CITIES[$cityKey])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'City not found'
    ]);
    exit;
}

$stations = get_stations($cityKey);

echo json_encode([
    'status' => 'success',
    'data' => array_values($stations)
]);
