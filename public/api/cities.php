<?php
require_once __DIR__ . "/../../src/config/config.php";

header('Content-Type: application/json; charset=utf-8');

$cities = [];

foreach ($CITIES as $key => $city) {
    $cities[] = [
        'key' => $key,
        'name' => $city['name']
    ];
}

echo json_encode([
    'status' => 'success',
    'data' => $cities
]);
