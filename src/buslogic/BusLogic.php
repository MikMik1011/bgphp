<?php
abstract class BusLogic
{
    protected $base_url;
    protected $headers;

    abstract public function get_all_stations();
    abstract public function get_station_arrivals($station_uid);

    public function __construct($base_url, $api_key)
    {
        $this->base_url = $base_url;
        $this->headers = [
            "X-Api-Authentication: $api_key",
            "User-Agent: okhttp/4.10.0",
            "Accept: application/json"
        ];
    }
}
