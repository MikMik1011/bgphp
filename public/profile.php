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
    <div class="profile-container">
        <h1>Welcome, <span id="username"><?php echo $_SESSION['user']['username'] ?></span></h1>
        <table class="profile-table">
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
                    <td><button class="remove-btn">Remove</button></td>
                </tr>
                <tr>
                    <td>Los Angeles</td>
                    <td>Union Station</td>
                    <td>Historic landmark</td>
                    <td><button class="remove-btn">Remove</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>