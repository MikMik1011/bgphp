<?php
require_once __DIR__ . '/../src/service/user_service.php';

logout_user();
header("Location: /login.php");
exit();
