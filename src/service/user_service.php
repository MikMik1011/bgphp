<?php
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../utils/exception.php';
require_once __DIR__ . '/session_service.php';

function validate_username_or_fail($username)
{
    if (!preg_match('/^[A-Za-z0-9_.-]{3,32}$/', $username)) {
        throw new HTTPException("Username must be 3-32 chars and use only letters, numbers, dot, underscore or dash.", 400);
    }
}

function validate_password_or_fail($password)
{
    if (strlen($password) < 8) {
        throw new HTTPException("Password must be at least 8 characters long.", 400);
    }

    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        throw new HTTPException("Password must contain at least one letter and one number.", 400);
    }
}

function create_user($username, $password, $db = new DB())
{
    $username = trim($username);

    validate_username_or_fail($username);
    validate_password_or_fail($password);

    $existing_user = get_user_by_username($username, $db);
    if ($existing_user) {
        throw new HTTPException("User already exists", 400);
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $db->query($sql, ['username' => $username, 'password' => $hashed_password]);
    $user = get_user_by_username($username, $db);
    unset($user['password']);
    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['user'] = $user;

    return $user;
}

function update_user_password($username, $old_password, $new_password, $db = new DB())
{
    $username = trim($username);

    $user = get_user_by_username($username, $db);
    if (!$user || !password_verify($old_password, $user['password'])) {
        throw new HTTPException("Invalid username or password", 400);
    }
    validate_password_or_fail($new_password);

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = :password WHERE username = :username";
    $db->query($sql, ['username' => $username, 'password' => $hashed_password]);
    return get_user_by_username($username, $db);
}

function login_user($username, $password, $db = new DB())
{
    $username = trim($username);

    $user = get_user_by_username($username, $db);
    if (!$user || !password_verify($password, $user['password'])) {
        throw new HTTPException("Invalid username or password", 400);
    }
    unset($user['password']);
    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['user'] = $user;

    return $user;
}

function logout_user()
{
    start_secure_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        $cookie_options = [
            'expires' => time() - 42000,
            'path' => $params['path'] ?? '/',
            'secure' => (bool) ($params['secure'] ?? false),
            'httponly' => (bool) ($params['httponly'] ?? true),
            'samesite' => $params['samesite'] ?? 'Lax',
        ];
        if (!empty($params['domain'])) {
            $cookie_options['domain'] = $params['domain'];
        }
        setcookie(session_name(), '', $cookie_options);
    }

    session_destroy();
}

function get_user_by_username($username, $db = new DB())
{
    return $db->fetch_one("SELECT * FROM users WHERE username = :username", ['username' => $username]);
}

function get_logged_in_user() {
    start_secure_session();
    return $_SESSION['user'] ?? null;
}
