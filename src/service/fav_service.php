<?php
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../service/user_service.php';
require_once __DIR__ . '/../utils/exception.php';

function add_station_to_favorites($city_key, $uid, $note = null, $db = new DB())
{
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }

    $sql = "INSERT INTO favorite_stations (user_id, city_key, station_uid, note) VALUES (:user_id, :city_key, :station_uid, :note)";
    try {
        $db->query($sql, [
            'user_id' => $user['id'],
            'city_key' => $city_key,
            'station_uid' => (int) $uid,
            'note' => $note
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === "23000") {
            throw new HTTPException("Station already in favorites", 400);
        }
        throw $e;
    }
}

function remove_station_from_favorites($city_key, $uid, $db = new DB())
{
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }

    $sql = "DELETE FROM favorite_stations WHERE user_id = :user_id AND city_key = :city_key AND station_uid = :station_uid";
    $stmt = $db->query($sql, [
        'user_id' => $user['id'],
        'city_key' => $city_key,
        'station_uid' => (int) $uid
    ]);

    if ($stmt->rowCount() === 0) {
        throw new HTTPException("Station is not in favorites", 404);
    }
}

function get_users_favorites($db = new DB())
{
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }

    $sql = "SELECT city_key, station_uid, note FROM favorite_stations WHERE user_id = :user_id ORDER BY city_key, station_uid";
    return $db->fetch_all($sql, ['user_id' => $user['id']]);
}
