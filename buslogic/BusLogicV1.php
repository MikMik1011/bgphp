<?php
require_once 'BusLogic.php';
class BusLogicV1 extends BusLogic
{
    private $endpoints = [
        'stations' => '/publicapi/v1/networkextended.php?ibfm=TM000001&action=get_cities_extended',
        'arrivals' => '/publicapi/v1/announcement/announcement.php?ibfm=TM000001&action=get_announcement_data&station_uid='
    ];

    public function getAllStations()
    {
        $url = $this->baseUrl . $this->endpoints['stations'];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->headers,
            CURLOPT_TIMEOUT        => 10,
        ]);
    
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function getStationArrivals($stationUid)
    {
        $url = $this->baseUrl . $this->endpoints['arrivals'] . $stationUid;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->headers,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }
}
