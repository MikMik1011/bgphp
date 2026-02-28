<?php
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../utils/exception.php';

function create_user($username, $password, $db = new DB())
{
    $existingUser = get_user_by_username($username, $db = new DB());
    if ($existingUser) {
        throw new HTTPException("User already exists", 400);
    }
    if (strlen($password) < 8) {
        throw new HTTPException("Password must be at least 8 characters long", 400);
    }
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $db->query($sql, ['username' => $username, 'password' => $hashedPassword]);
    $user = get_user_by_username($username, $db);
    unset($user['password']);
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['user'] = $user;

    return $user;
}

function update_user_password($username, $oldPassword, $newPassword, $db = new DB())
{
    $user = get_user_by_username($username, $db);
    if (!$user || !password_verify($oldPassword, $user['password'])) {
        throw new HTTPException("Invalid username or password", 400);
    }
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = :password WHERE username = :username";
    $db->query($sql, ['username' => $username, 'password' => $hashedPassword]);
    return get_user_by_username($username, $db);
}

function login_user($username, $password, $db = new DB())
{
    $user = get_user_by_username($username, $db);
    if (!$user || !password_verify($password, $user['password'])) {
        throw new HTTPException("Invalid username or password", 400);
    }
    unset($user['password']);
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['user'] = $user;

    return $user;
}

function logout_user()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    session_destroy();
}

function get_user_by_username($username, $db = new DB())
{
    return $db->fetchOne("SELECT * FROM users WHERE username = :username", ['username' => $username]);
}

function get_logged_in_user() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return $_SESSION['user'] ?? null;
}
