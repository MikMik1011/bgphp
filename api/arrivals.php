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

$city = $CITIES[$cityKey];

if (!isset($city)) {
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
$uid = $_GET['uid'];


$arrivals = $city['repo']->getStationArrivals($uid);
$parsedArrivals = parseArrivals($arrivals);
echo json_encode([
    'status' => 'success',
    'data' => $parsedArrivals
]);
