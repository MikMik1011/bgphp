<?php
require_once __DIR__ . "/../../src/service/fav_service.php";
require_once __DIR__ . "/../../src/service/bgpp_service.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $favorites = get_users_favorites();
        $enriched = array_map(function ($favorite) use ($CITIES) {
            $cityKey = $favorite['city_key'];
            $uid = (int) $favorite['station_uid'];
            $stations = get_stations($cityKey);
            $station = $stations[$uid] ?? null;

            return [
                'city_key' => $cityKey,
                'city_name' => $CITIES[$cityKey]['name'] ?? $cityKey,
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
    $cityKey = $_POST['city'] ?? '';
    $uid = $_POST['uid'] ?? null;
    $note = $_POST['note'] ?? null;

    if (!$cityKey || $uid === null) {
        throw new HTTPException("City and uid are required", 400);
    }

    if (!is_valid_city($cityKey)) {
        throw new HTTPException("City not found", 404);
    }

    if ($action === 'add') {
        add_station_to_favorites($cityKey, $uid, $note);
    } elseif ($action === 'remove') {
        remove_station_from_favorites($cityKey, $uid);
    } else {
        throw new HTTPException("Invalid action", 400);
    }

    echo json_encode([
        'status' => 'success',
        'message' => $action === 'add' ? 'Favorite added' : 'Favorite removed'
    ]);
} catch (HTTPException $e) {
    http_response_code($e->getStatusCode());
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
