<?php
require_once __DIR__ . '/../src/service/fav_service.php';
require_once __DIR__ . '/../src/service/bgpp_service.php';

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: /login.php");
    exit();
}

$favorites = [];
try {
    $favorites = get_users_favorites();
} catch (HTTPException $e) {
    $favorites = [];
}

$favoriteRows = [];
foreach ($favorites as $favorite) {
    $cityKey = $favorite['city_key'];
    if (!isset($CITIES[$cityKey])) {
        continue;
    }

    $uid = (int) $favorite['station_uid'];
    $stations = get_stations($cityKey);
    $station = $stations[$uid] ?? null;

    $favoriteRows[] = [
        'city_key' => $cityKey,
        'city_name' => $CITIES[$cityKey]['name'],
        'station_uid' => $uid,
        'station_name' => $station['name'] ?? 'Unknown station',
        'station_id' => $station['id'] ?? '',
        'note' => $favorite['note'] ?? ''
    ];
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
            <p id="profile-message" style="display:none"></p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>City</th>
                        <th>Station Name</th>
                        <th>Note</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="favorites-table-body">
                    <?php if (count($favoriteRows) === 0): ?>
                        <tr id="empty-favorites-row">
                            <td colspan="4">No favorite stations yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($favoriteRows as $row): ?>
                            <tr data-city="<?php echo htmlspecialchars($row['city_key'], ENT_QUOTES, 'UTF-8'); ?>" data-uid="<?php echo (int) $row['station_uid']; ?>">
                                <td><?php echo htmlspecialchars($row['city_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['station_name'], ENT_QUOTES, 'UTF-8'); ?><?php echo $row['station_id'] !== '' ? ' (' . htmlspecialchars($row['station_id'], ENT_QUOTES, 'UTF-8') . ')' : ''; ?></td>
                                <td><?php echo htmlspecialchars($row['note'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><button type="button" class="profile-remove-btn">Remove</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php">Back to home</a> /
        <a href="logout.php">Logout</a>
    </div>
    <script>
        const profileMessage = document.getElementById('profile-message');
        const favoritesBody = document.getElementById('favorites-table-body');

        const showProfileMessage = (message, isError) => {
            profileMessage.textContent = message;
            profileMessage.style.color = isError ? '#ff6b6b' : '#1abc9c';
            profileMessage.style.display = 'block';
        };

        const ensureEmptyState = () => {
            if (favoritesBody.querySelectorAll('tr[data-city]').length > 0) {
                return;
            }

            if (!document.getElementById('empty-favorites-row')) {
                const row = document.createElement('tr');
                row.id = 'empty-favorites-row';
                row.innerHTML = '<td colspan="4">No favorite stations yet.</td>';
                favoritesBody.appendChild(row);
            }
        };

        favoritesBody.addEventListener('click', async (event) => {
            if (!event.target.classList.contains('profile-remove-btn')) {
                return;
            }

            const row = event.target.closest('tr[data-city]');
            if (!row) {
                return;
            }

            const city = row.getAttribute('data-city');
            const uid = row.getAttribute('data-uid');

            const params = new URLSearchParams();
            params.set('action', 'remove');
            params.set('city', city);
            params.set('uid', uid);

            try {
                const response = await fetch('/api/favorites.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: params.toString()
                });
                const payload = await response.json();

                if (!response.ok || payload.status !== 'success') {
                    throw new Error(payload.message || 'Could not remove favorite.');
                }

                row.remove();
                const emptyRow = document.getElementById('empty-favorites-row');
                if (emptyRow) {
                    emptyRow.remove();
                }
                ensureEmptyState();
                showProfileMessage('Favorite removed.', false);
            } catch (error) {
                showProfileMessage(error.message || 'Could not remove favorite.', true);
            }
        });
    </script>
</body>

</html>
