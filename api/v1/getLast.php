<?php

require_once "../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
if($_GET['key'] === $api_key) {
    $link = pg_connect(str_replace([";", "pgsql:"], [" "], $pgsql_dsn));
    if($link) {
        pg_query("SET search_path = weather_logging");
        $result = pg_query($link, "SELECT * FROM weather ORDER BY id DESC LIMIT 1");
        $row = pg_fetch_object($result);
        $output = [
            "error" => "null",
            "ds1820" => [
                "temp" => [
                    "value" => $row->ds1820_temp,
                    "unit" => "°C"
                ]
            ],
            "am2302" => [
                "humidity" => [
                    "value" => $row->am2302_humidity,
                    "unit" => "%"
                ],
                "temp" => [
                    "value" => $row->am2302_temp,
                    "unit" => "°C"
                ]
            ],
            "bmp180" => [
                "pressure" => [
                    "value" => $row->bmp180_pressure,
                    "unit" => "hPa"
                ],
                "temp" => [
                    "value" => $row->bmp180_temp,
                    "unit" => "°C"
                ]
            ],
            "created_at" => $row->created_at
        ];
    }
    else {
        $output = [
            "error" => "mysqlConnError",
            "code" => 500,
            "msg" => "Couldn't connect to MySQL DataBase"
        ];
    }
}
else {
    $output = [
        "error" => "badRequest",
        "code" => 400,
        "msg" => "Bad Request"
    ];
}
echo json_encode($output);
