<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../src/service/user_service.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        if ($password !== $confirm_password) {
            throw new HTTPException("Password and confirmation do not match.", 400);
        }
        create_user($username, $password);
        header("Location: index.php");
        exit();
    } catch (HTTPException $e) {
        $error_message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        http_response_code($e->get_status_code());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | BGPHP</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div class="page-shell">
        <h1>BG++</h1>
        <h3 class="subtitle">Register</h3>
        <?php if (isset($error_message)): ?>
            <div class="error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="username">Username:</label>
            <input
                type="text"
                id="username"
                name="username"
                required
                minlength="3"
                maxlength="32"
                pattern="[A-Za-z0-9_.-]{3,32}"
                title="3-32 chars: letters, numbers, dot, underscore, dash."
                autocomplete="username">

            <label for="password">Password:</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                minlength="8"
                pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,}"
                title="At least 8 characters with at least one letter and one number."
                autocomplete="new-password">

            <label for="confirm_password">Confirm password:</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
                minlength="8"
                autocomplete="new-password">

            <button type="submit">Register</button>
        </form>
        <a href="login.php">Already have an account? Login here.</a><br>
        <a href="index.php">Back to home</a>
    </div>
</body>
</html>
