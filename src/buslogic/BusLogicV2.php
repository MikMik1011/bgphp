<?php
require_once 'BusLogic.php';
class BusLogicV2 extends BusLogic
{
    private $encryption_key;
    private $encryption_iv;

    private $endpoints = [
        'stations' => '/publicapi/v1/networkextended.php?ibfm=TM000001&action=get_cities_extended',
        'arrivals' => '/publicapi/v2/api.php'
    ];

    public function get_all_stations()
    {
        $url = $this->base_url . $this->endpoints['stations'];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->headers,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function get_station_arrivals($station_uid)
    {
        $payload = [
            'station_uid' => $station_uid,
            'session_id' => 'A' . time()
        ];
        $encrypted_payload = $this->encrypt($payload);
        $post_fields = http_build_query([
            'action' => 'data_bulletin',
            'base' => $encrypted_payload
        ]);

        $url = $this->base_url . $this->endpoints['arrivals'];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->headers,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_fields
        ]);
    
        $response = curl_exec($ch);
        $decrypted_response = $this->decrypt($response);

        if (isset($decrypted_response['data'])) {
            return $decrypted_response['data'];
        }
        return null;
    }

    private function encrypt($payload)
    {
        $payload_string = json_encode($payload);
        $encrypted = openssl_encrypt(
            $payload_string,
            'aes-256-cbc',
            $this->encryption_key,
            OPENSSL_RAW_DATA,
            $this->encryption_iv
        );
        return base64_encode($encrypted);
    }

    private function decrypt($payload)
    {
        $encrypted_data = base64_decode($payload);
        $decrypted = openssl_decrypt(
            $encrypted_data,
            'aes-256-cbc',
            $this->encryption_key,
            OPENSSL_RAW_DATA,
            $this->encryption_iv
        );
        return json_decode($decrypted, true);
    }

    public function __construct($base_url, $api_key, $encryption_key, $encryption_iv)
    {
        parent::__construct($base_url, $api_key);
        $this->encryption_key = base64_decode($encryption_key);
        $this->encryption_iv = base64_decode($encryption_iv);
    }
}
