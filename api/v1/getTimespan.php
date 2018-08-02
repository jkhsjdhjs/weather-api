<?php

require_once "../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if($_GET['key'] === $api_key && isset($_GET['first']) && isset($_GET['last'])) {
    $first = $_GET['first'];
    $last = $_GET['last'];
    if(preg_match("/(\d+)\/(\d+)\/(\d+)\/(\d+)\/(\d+)/", $first, $matchesFirst) && preg_match("/(\d+)\/(\d+)\/(\d+)\/(\d+)\/(\d+)/", $last, $matchesLast)) {
        for($i = 1; $i <= 5; $i++) {
            if(!is_numeric($matchesFirst[$i]) || !is_numeric($matchesLast[$i])) {
                $err_flag = true;
            }
        }
        if(!isset($err_flag)) {
            $dayFirst = $matchesFirst[1];
            $dayLast = $matchesLast[1];
            $monthFirst = $matchesFirst[2];
            $monthLast = $matchesLast[2];
            $yearFirst = $matchesFirst[3];
            $yearLast = $matchesLast[3];
            $hourFirst = $matchesFirst[4];
            $hourLast = $matchesLast[4];
            $minFirst = $matchesFirst[5];
            $minLast = $matchesLast[5];
            $firstTimestamp = $yearFirst . "-" . $monthFirst . "-" . $dayFirst . " " . $hourFirst . ":" . $minFirst . ":59";
            $lastTimestamp = $yearLast . "-" . $monthLast . "-" . $dayLast . " " . $hourLast . ":" . $minLast . ":59";
            $link = pg_connect(str_replace(";", " ", str_replace("pgsql:", "", $pgsql_dsn)));
            if($link) {
                $output = [ "error" => null ];
                pg_query("SET search_path = weather_logging");
                $result = pg_query($link, "SELECT * FROM weather WHERE created_at BETWEEN '$firstTimestamp' AND '$lastTimestamp'");
                if(pg_num_rows($result) != 0) {
                    while($row = pg_fetch_object($result)) {
                        $record = [
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
                        array_push($output, $record);
                    }
                }
                else {
                    $output = [
                        "error" => "badRequest",
                        "code" => 400,
                        "msg" => "Bad Request"
                    ];
                }
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
    }
    else {
        $output = [
            "error" => "badRequest",
            "code" => 400,
            "msg" => "Bad Request"
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
