<?php
require_once __DIR__ . '/session_service.php';
require_once __DIR__ . '/../utils/exception.php';

function get_csrf_token()
{
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function is_valid_csrf_token($token)
{
    if (!is_string($token) || $token === '') {
        return false;
    }

    $session_token = get_csrf_token();
    return hash_equals($session_token, $token);
}

function require_valid_csrf_token_or_throw()
{
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_valid_csrf_token((string) $token)) {
        throw new HTTPException('Invalid CSRF token', 403);
    }
}
