<?php
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../service/user_service.php';
require_once __DIR__ . '/../utils/exception.php';

function add_station_to_favorites($cityKey, $uid, $note = null, $db = new DB())
{
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }

    $sql = "INSERT INTO favorite_stations (user_id, city_key, station_uid, note) VALUES (:user_id, :city_key, :station_uid, :note)";
    try {
        $db->query($sql, [
            'user_id' => $user['id'],
            'city_key' => $cityKey,
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

function remove_station_from_favorites($cityKey, $uid, $db = new DB())
{
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }

    $sql = "DELETE FROM favorite_stations WHERE user_id = :user_id AND city_key = :city_key AND station_uid = :station_uid";
    $stmt = $db->query($sql, [
        'user_id' => $user['id'],
        'city_key' => $cityKey,
        'station_uid' => (int) $uid
    ]);

    if ($stmt->rowCount() === 0) {
        throw new HTTPException("Station is not in favorites", 404);
    }
}

function is_station_in_favorites($cityKey, $uid, $db = new DB())
{
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }

    $sql = "SELECT 1 FROM favorite_stations WHERE user_id = :user_id AND city_key = :city_key AND station_uid = :station_uid LIMIT 1";
    $row = $db->fetchOne($sql, [
        'user_id' => $user['id'],
        'city_key' => $cityKey,
        'station_uid' => (int) $uid
    ]);

    return $row !== false;
}

function get_users_favorites($db = new DB())
{
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }

    $sql = "SELECT city_key, station_uid, note FROM favorite_stations WHERE user_id = :user_id ORDER BY city_key, station_uid";
    return $db->fetchAll($sql, ['user_id' => $user['id']]);
}
