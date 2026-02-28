<?php
require_once __DIR__ . "/../../src/service/bgpp_service.php";
require_once __DIR__ . "/../../src/utils/http_response.php";
require_once __DIR__ . "/../../src/utils/security_headers.php";

apply_security_headers();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['city'])) {
    respond_with_error('City parameter is required', 400);
}
$city_key = trim($_GET['city']);

if (!isset($CITIES[$city_key])) {
    respond_with_error('City not found', 404);
}

if (!isset($_GET['uid'])) {
    respond_with_error('Station UID parameter is required', 400);
}

$stations = get_stations($city_key);

$uid = trim((string) $_GET['uid']);
if ($uid === '' || !ctype_digit($uid)) {
    respond_with_error('Station UID must be a positive integer', 400);
}

$station = $stations[$uid] ?? null;
if (!$station) {
    respond_with_error('Station not found', 404);
}

$arrivals = get_arrivals($city_key, $uid);

echo json_encode([
    'status' => 'success',
    'data' => [
        'station' => $station,
        'lines' => array_values($arrivals)
    ]
]);
