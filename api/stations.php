<?php
require_once "../_config/config.php";
require_once "../_parser/parser.php";

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
$city = $CITIES[$cityKey];

$stations = $city['repo']->getAllStations();
$parsedStations = parseStations($stations);
echo json_encode([
    'status' => 'success',
    'data' => $parsedStations
]);
