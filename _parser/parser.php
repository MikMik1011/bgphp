<?php

function parseStations($stations) {
    $result = [];
    foreach ($stations['stations'] as $station) {
        $id_uppercase = strtoupper($station['station_id']);
        $result[$id_uppercase] = [
            'name' => $station['name'],
            'uid' => $station['id'],
            'id' => $station['station_id'],
            'coords' => [
                'lat' => (float)$station['coordinates']['latitude'],
                'lon' => (float)$station['coordinates']['longitude'],
            ],
        ];
    }
    return array_values($result);
}

function parseArrivals($arrivals) {
    $result = [];
    if(empty($arrivals) || (isset($arrivals[0]['just_coordinates']) && $arrivals[0]['just_coordinates'] === '1')) {
        return $result;
    }
    
    foreach ($arrivals as $arrival) {
        $line_number = $arrival['line_number'];
        $parsedArrival = [
            'etaSeconds' => $arrival['seconds_left'] ?? 0,
            'etaStations' => $arrival['stations_between'] ?? 0,
            'garageNo' => $arrival['vehicles'][0]['garageNo'] ?? "Unknown",
            'coords' => [
                'lat' => (float)$arrival['vehicles'][0]['lat'] ?? 0,
                'lon' => (float)$arrival['vehicles'][0]['lng'] ?? 0,
            ],
        ];

        if(!isset($result[$line_number])) {
            $result[$line_number] = [
                'lineNumber' => $line_number,
                'lineName' => $arrival['line_title'] ?? "Unknown",
                'arrivals' => []
            ];
        }

        $result[$line_number]['arrivals'][] = $parsedArrival;
    }
    return array_values($result);
}