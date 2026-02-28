<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: /login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="/css/index.css">
</head>

<body>
    <div class="page-shell">
        <h1>BG++</h1>
        <h3 class="subtitle">Profile</h3>
        <div class="card-panel">
            <p>Welcome, <strong id="username"><?php echo htmlspecialchars($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>City</th>
                        <th>Station Name</th>
                        <th>Note</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>New York</td>
                        <td>Grand Central</td>
                        <td>Busy station</td>
                        <td><button type="button">Remove</button></td>
                    </tr>
                    <tr>
                        <td>Los Angeles</td>
                        <td>Union Station</td>
                        <td>Historic landmark</td>
                        <td><button type="button">Remove</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <a href="index.php">Back to home</a>
    </div>
</body>

</html>
