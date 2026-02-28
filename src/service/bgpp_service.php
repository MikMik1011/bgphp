<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../parser/parser.php";

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
        $city_data = $CITIES[$city];
        $fetched_stations = $city_data['repo']->get_all_stations();
        $stations = parse_stations($fetched_stations);
        apcu_store("stations_$city", $stations, 86400);
    }
    return $stations;
}

function get_arrivals($city, $uid)
{
    global $CITIES;
    $arrivals = apcu_fetch("arrivals_{$city}_{$uid}");
    if ($arrivals === false) {
        $city_data = $CITIES[$city];
        $fetched_arrivals = $city_data['repo']->get_station_arrivals($uid);
        $arrivals = parse_arrivals($fetched_arrivals);
        apcu_store("arrivals_{$city}_{$uid}", $arrivals, 15);
    }
    return $arrivals;
}
