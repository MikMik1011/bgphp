<?php
require_once __DIR__ . "/../src/config/config.php";
session_start();
$loggedInUser = $_SESSION['user'] ?? null;
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>BGPHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/index.css">

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin="anonymous">

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link
        href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="js/script.js"></script>
    <script src="js/mapLayers.js"></script>

</head>

<body class="body" onload="toggleTable()">

    <h1>BG++</h1>
    <h3 id="motto">Fixamo fix ideje since 2023</h3>
    <?php if ($loggedInUser): ?>
        <p>
            Logged in as
            <strong><?php echo htmlspecialchars($loggedInUser['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
            -
            <a href="profile.php">Profile</a>
            /
            <a href="logout.php">Logout</a>
        </p>
    <?php else: ?>
        <p><a href="login.php">Login</a> / <a href="register.php">Register</a></p>
    <?php endif; ?>
    <a href="#" onclick="document.getElementById('fair-usage-modal').style.display='block';return false;">
        Fair Usage Policy
    </a>

    <form id="myForm">
        <label for="city">Grad:</label>
        <select id="city" name="city" onchange="onCityChange()">
            <?php
            foreach ($CITIES as $key => $city) {
                echo "<option value=\"$key\">{$city['name']}</option>";
            }
            ?>
        </select>

        <label for="searchMode">Tip pretrage:</label>
        <select id="searchMode" name="searchMode" onchange="onSearchModeChange()">
            <option value="name">Ime/ID stanice</option>
            <option value="coords">Lokacija</option>
            <option value="favorites" disabled>Omiljene stanice (uskoro)</option>
        </select>


        <div class="name-search" style="display:none">
            <label for="name-input">Ime/ID stanice:</label>
            <select class="select2" id="name-input" onchange="toggleTable()">
                <option> Dobavljanje liste stanica, molimo sacekajte... </option>
            </select>
        </div>

        <div class="coords-search" style="display:none">
            <button type="button" onclick="searchByGPS()">Pronadji najbliže stanice</button>

            <label id="stationsMaxDistance-label">Najveća udaljenost (350m):</label>
            <input
                type="range"
                id="stationsMaxDistance-input"
                min="50"
                max="1000"
                value="350"
                step="50"
                onchange="$('#stationsMaxDistance-label').html(`Najveća udaljenost (${this.value}m):`)">

            <label for="coords-input">Stanica:</label>
            <select class="select2" id="coords-input" onchange="toggleTable()" style="display:none"></select>
        </div>

        <input id="submit" type="submit" value="Kad će mi bus?">

        <div id="sort-data-wrapper">
            <label for="dataSaver">Ušteda podataka:</label>
            <input type="checkbox" id="dataSaver" checked>

            <label for="sort-lines">Sortiranje linija:</label>
            <input type="checkbox" id="sort-lines" onchange="if(currQuery) submitHandlers[getSearchMode()]()">
        </div>

    </form>

    <div id="result">
        <p id="stationName"></p>
        <p id="lastUpdated"></p>
        <p id="updateInProgress" style="display:none">Ažuriranje u toku...</p>
        <p id="error" style="display:none">Greška pri ažuriranju</p>
        <?php if ($loggedInUser): ?>
            <div id="favorite-toggle-wrapper" style="display:none">
                <button type="button" id="favorite-toggle-btn"></button>
                <p id="favorite-message" style="display:none"></p>
            </div>
        <?php endif; ?>

        <table id="tabela" border="2" style="display:none">
            <thead>
                <tr>
                    <th>Linija</th>
                    <th>ETA</th>
                    <th>Stanice</th>
                    <th>ID vozila</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>

    <div id="map"></div>
    </div>

    <?php if ($loggedInUser): ?>
        <div id="favorite-note-modal" style="display:none">
            <div class="favorite-note-modal-content">
                <h3>Add Favorite Note</h3>
                <label for="favorite-note-input">Note (optional)</label>
                <input type="text" id="favorite-note-input" maxlength="255" placeholder="e.g. Near work, platform A">
                <div class="favorite-note-actions">
                    <button type="button" id="favorite-note-save">Save favorite</button>
                    <button type="button" id="favorite-note-cancel">Cancel</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div
        id="fair-usage-modal"
        style="
        display:none;
        position:fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background:rgba(0,0,0,0.6);
        backdrop-filter:blur(3px);
        padding:40px;
        overflow:auto;
        z-index:999999999;
    ">
        <div
            style="
            background:white;
            padding:20px;
            max-width:700px;
            margin:0 auto;
            border-radius:8px;
        ">
            <h2>Fair Usage Policy</h2>

            <p>
                This service is officially hosted at https://bgpp.misa.st and works
                only as a proxy layer to the official API, which has been reverse
                engineered for compatibility and research.
            </p>

            <h3>1. Permitted Use</h3>
            <p>
                The BGPP proxy API may be used only in the official BGPP app or for
                personal research, testing, and educational work.
            </p>

            <h3>2. Third Party Projects</h3>
            <p>
                If you want to use this API in your own project, you must adapt the
                source code for your needs or self host your own instance. Direct use
                of https://bgpp.misa.st in third party apps is not allowed.
            </p>

            <h3>3. Restrictions</h3>
            <p>
                Usage that places excessive load, bypasses limits, mirrors data,
                or harms stability is forbidden.
            </p>

            <h3>4. Enforcement</h3>
            <p>
                Violations may result in temporary or permanent blocks.
            </p>

            <h3>5. Changes</h3>
            <p>
                This policy may change at any time. Continued use indicates acceptance.
            </p>

            <button
                onclick="document.getElementById('fair-usage-modal').style.display='none';"
                style="margin-top:20px;padding:8px 14px;cursor:pointer">
                Close
            </button>

        </div>
    </div>

</body>

</html>
