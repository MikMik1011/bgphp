<?php
require_once __DIR__ . '/../_buslogic/BusLogicV1.php';
require_once __DIR__ . '/../_buslogic/BusLogicV2.php';
$CITIES = [
    "bg" => [
        "name" => "Beograd",
        "repo" => new BusLogicV2("https://announcement-bgnaplata.ticketing.rs", "1688dc355af72ef09287", "3+Lhz8XaOli6bHIoYPGuq9Y8SZxEjX6eN7AFPZuLCLs=", "IvUScqUudyxBTBU9ZCyjow==")
    ],
    "ns" => [
        "name" => "Novi Sad",
        "repo" => new BusLogicV1("https://online.nsmart.rs", "4670f468049bbee2260")
    ],
    "ni" => [
        "name" => "Niš",
        "repo" => new BusLogicV1("https://online.jgpnis.rs", "cddfd29e495b4851965d")
    ],
]
?>