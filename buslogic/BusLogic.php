<?php
abstract class BusLogic
{
    protected $baseUrl;
    protected $headers;

    abstract public function getAllStations();
    abstract public function getStationArrivals($stationUid);

    public function __construct($baseUrl, $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->headers = [
            "X-Api-Authentication: $apiKey",
            "User-Agent: okhttp/4.10.0",
            "Accept: application/json"
        ];
    }
}
