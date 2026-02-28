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

$stations = get_stations($city_key);

echo json_encode([
    'status' => 'success',
    'data' => array_values($stations)
]);
