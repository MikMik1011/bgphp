<?php
require_once __DIR__ . "/../../src/service/fav_service.php";
require_once __DIR__ . "/../../src/service/bgpp_service.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $favorites = get_users_favorites();
        $enriched = array_map(function ($favorite) use ($CITIES) {
            $city_key = $favorite['city_key'];
            $uid = (int) $favorite['station_uid'];
            $stations = get_stations($city_key);
            $station = $stations[$uid] ?? null;

            return [
                'city_key' => $city_key,
                'city_name' => $CITIES[$city_key]['name'] ?? $city_key,
                'station_uid' => $uid,
                'station_name' => $station['name'] ?? 'Unknown station',
                'station_id' => $station['id'] ?? '',
                'note' => $favorite['note'] ?? ''
            ];
        }, $favorites);

        echo json_encode([
            'status' => 'success',
            'data' => $enriched
        ]);
        exit;
    }

    if ($method !== 'POST') {
        throw new HTTPException("Method not allowed", 405);
    }

    $action = $_POST['action'] ?? '';
    $city_key = $_POST['city'] ?? '';
    $uid = $_POST['uid'] ?? null;
    $note = $_POST['note'] ?? null;

    if (!$city_key || $uid === null) {
        throw new HTTPException("City and uid are required", 400);
    }

    if (!is_valid_city($city_key)) {
        throw new HTTPException("City not found", 404);
    }

    if ($action === 'add') {
        add_station_to_favorites($city_key, $uid, $note);
    } elseif ($action === 'remove') {
        remove_station_from_favorites($city_key, $uid);
    } else {
        throw new HTTPException("Invalid action", 400);
    }

    echo json_encode([
        'status' => 'success',
        'message' => $action === 'add' ? 'Favorite added' : 'Favorite removed'
    ]);
} catch (HTTPException $e) {
    http_response_code($e->get_status_code());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
}
