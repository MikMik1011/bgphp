<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../src/service/user_service.php';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $user = create_user($username, $password);
        header("Location: index.php");
        exit();
    } catch (HTTPException $e) {
        $errorMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        http_response_code($e->getStatusCode());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div class="page-shell">
        <h1>BG++</h1>
        <h3 class="subtitle">Register</h3>
        <?php if (isset($errorMessage)): ?>
            <div class="error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Register</button>
        </form>
        <a href="login.php">Already have an account? Login here.</a><br>
        <a href="index.php">Back to home</a>
    </div>
</body>
</html>
