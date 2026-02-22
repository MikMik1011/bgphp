<?php
require_once "config/config.php";

if (!isset($_GET['city'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'City parameter is required'
    ]);
    return;
}

$cityKey = $_GET['city'];
if (!isset($CITIES[$cityKey])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'City not found'
    ]);
    return;
}
$city = $CITIES[$cityKey];

if (isset($_GET['station_uid'])) {
    $arrivals = $city['repo']->getStationArrivals($_GET['station_uid']);
    echo json_encode([
        'status' => 'success',
        'data' => $arrivals
    ]);
    return;
}

$stations = $city['repo']->getAllStations();
echo json_encode([
    'status' => 'success',
    'data' => $stations
]);



?>
