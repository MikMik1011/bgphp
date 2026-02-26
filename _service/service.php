<?php
require_once __DIR__ . "/../_config/config.php";
require_once __DIR__ . "/../_parser/parser.php";

function is_valid_city($city)
{
    global $CITIES;
    return isset($CITIES[$city]);
}

function get_stations($city)
{
    global $CITIES;
    $stations = apcu_fetch("stations_$city");
    if ($stations === false) {
        $cityData = $CITIES[$city];
        $fetched_stations = $cityData['repo']->getAllStations();
        $stations = parseStations($fetched_stations);
        apcu_store("stations_$city", $stations, 86400);
    }
    return $stations;
}

function get_arrivals($city, $uid)
{
    global $CITIES;
    $arrivals = apcu_fetch("arrivals_{$city}_{$uid}");
    if ($arrivals === false) {
        $cityData = $CITIES[$city];
        $fetched_arrivals = $cityData['repo']->getStationArrivals($uid);
        $arrivals = parseArrivals($fetched_arrivals);
        apcu_store("arrivals_{$city}_{$uid}", $arrivals, 15);
    }
    return $arrivals;
}
