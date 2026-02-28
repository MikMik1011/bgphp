<?php
require_once __DIR__ . '/../src/service/user_service.php';
require_once __DIR__ . '/../src/utils/security_headers.php';

apply_security_headers();
logout_user();
header("Location: /login.php");
exit();
