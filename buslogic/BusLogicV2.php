<?php
require_once 'BusLogic.php';
class BusLogicV2 extends BusLogic
{
    private $encryptionKey;
    private $encryptionIV;

    private $endpoints = [
        'stations' => '/publicapi/v1/networkextended.php?ibfm=TM000001&action=get_cities_extended',
        'arrivals' => '/publicapi/v2/api.php'
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
        $payload = [
            'station_uid' => $stationUid,
            'session_id' => 'A' . time()
        ];
        $encryptedPayload = $this->encrypt($payload);
        $postFields = http_build_query([
            'action' => 'data_bulletin',
            'base' => $encryptedPayload
        ]);

        $url = $this->baseUrl . $this->endpoints['arrivals'];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->headers,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields
        ]);
    
        $response = curl_exec($ch);
        $decryptedResponse = $this->decrypt($response);

        if (isset($decryptedResponse['data'])) {
            return $decryptedResponse['data'];
        }
        return null;
    }

    private function encrypt($payload)
    {
        $payloadString = json_encode($payload);
        $encrypted = openssl_encrypt(
            $payloadString,
            'aes-256-cbc',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $this->encryptionIV
        );
        return base64_encode($encrypted);
    }

    private function decrypt($payload)
    {
        $encryptedData = base64_decode($payload);
        $decrypted = openssl_decrypt(
            $encryptedData,
            'aes-256-cbc',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $this->encryptionIV
        );
        return json_decode($decrypted, true);
    }

    public function __construct($baseUrl, $apiKey, $encryptionKey, $encryptionIV)
    {
        parent::__construct($baseUrl, $apiKey);
        $this->encryptionKey = base64_decode($encryptionKey);
        $this->encryptionIV = base64_decode($encryptionIV);
    }
}
