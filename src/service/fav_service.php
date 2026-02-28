<?php

require_once __DIR__ . "/../config/config.php";

function add_station_to_favorites($cityKey, $uid, $db = new DB()) {
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }
    $sql = "INSERT INTO favorites (user_id, city_key, station_uid) VALUES (:user_id, :city_key, :station_uid)";
    try {
        $db->query($sql, [
            'user_id' => $user['id'],
            'city_key' => $cityKey,
            'station_uid' => $uid
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            throw new HTTPException("Station already in favorites", 400);
        }
        throw $e;
    }
}

function remove_station_from_favorites($cityKey, $uid, $db = new DB()) {
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }
    $sql = "DELETE FROM favorites WHERE user_id = :user_id AND city_key = :city_key AND station_uid = :station_uid";
    $db->query($sql, [
        'user_id' => $user['id'],
        'city_key' => $cityKey,
        'station_uid' => $uid
    ]);
}

function is_station_in_favorites($cityKey, $uid, $db = new DB()) {
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }
    $sql = "SELECT COUNT(*) FROM favorites WHERE user_id = :user_id AND city_key = :city_key AND station_uid = :station_uid";
    return $db->fetchOne($sql, [
        'user_id' => $user['id'],
        'city_key' => $cityKey,
        'station_uid' => $uid
    ]) > 0;
}

function get_users_favorites($db = new DB()) {
    $user = get_logged_in_user();
    if (!$user) {
        throw new HTTPException("Unauthorized", 401);
    }
    $sql = "SELECT city_key, station_uid FROM favorites WHERE user_id = :user_id";
    return $db->fetchAll($sql, ['user_id' => $user['id']]);
}