<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    if($_GET['key'] === "QPEqL18ovm7c4iwWtYwe7jgTJnpCjTevv0S6kGGq65MrrOTdYLr4S1R9rzSw9IKSMFgRrI3WJ5G4N4yD") {
        $link = pg_connect("dbname=home user=home");
        if($link) {
            $result = pg_query($link, "SELECT * FROM weather ORDER BY id DESC LIMIT 1");
            $row = pg_fetch_object($result);
            $output = [
                "error" => "null",
                "ds1820" => [
                    "temp" => $row->ds1820_temp,
                    "unit" => "°C"
                ],
                "am2302" => [
                    "humidity" => [
                        "hum" => $row->am2302_humidity,
                        "unit" => "%"
                    ],
                    "temp" => [
                        "temp" => $row->am2302_temp,
                        "unit" => "°C"
                    ]
                ],
                "bmp180" => [
                    "pressure" => [
                        "pres" => $row->bmp180_pressure,
                        "unit" => "hPa"
                    ],
                    "temp" => [
                        "temp" => $row->bmp180_temp,
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
?>
