<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    if($_POST['key'] == "QPEqL18ovm7c4iwWtYwe7jgTJnpCjTevv0S6kGGq65MrrOTdYLr4S1R9rzSw9IKSMFgRrI3WJ5G4N4yD" && isset($_POST['first']) && isset($_POST['last'])) {
        $first = $_POST['first'];
        $last = $_POST['last'];
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
                $link = mysqli_connect("localhost", "shrooms", "tXFnvPpHVQurHQSu", "shrooms");
                if($link) {
                    $output = array();
                    $result = mysqli_query($link, "SELECT * FROM log WHERE timestamp BETWEEN '$firstTimestamp' AND '$lastTimestamp'");
                    if(mysqli_num_rows($result) != 0) {
                        while($row = mysqli_fetch_object($result)) {
                            $record = array(
                                "temp" => array(
                                    "temp" => $row->temp_temp,
                                    "unit" => "°C"
                                ),
                                "hum" => array(
                                    "hum" => array(
                                        "hum" => $row->hum,
                                        "unit" => "%"
                                    ),
                                    "temp" => array(
                                        "temp" => $row->temp_hum,
                                        "unit" => "°C"
                                    )
                                ),
                                "pres" => array(
                                    "pres" => array(
                                        "pres" => $row->pres,
                                        "unit" => "hPa"
                                    ),
                                    "temp" => array(
                                        "temp" => $row->temp_pres,
                                        "unit" => "°C"
                                    )
                                )
                            );
                            array_push($output, $record);
                        }
                    }
                    else {
                        $output = array(
                            "error" => "badRequest",
                            "code" => 400,
                            "msg" => "Bad Request"
                        );
                    }
                }
                else {
                    $output = array(
                        "error" => "mysqlConnError",
                        "code" => 500,
                        "msg" => "Couldn't connect to MySQL DataBase"
                    );
                }
            }
            else {
                $output = array(
                    "error" => "badRequest",
                    "code" => 400,
                    "msg" => "Bad Request"
                );
            }
        }
        else {
            $output = array(
                "error" => "badRequest",
                "code" => 400,
                "msg" => "Bad Request"
            );
        }
    }
    else {
        $output = array(
            "error" => "badRequest",
            "code" => 400,
            "msg" => "Bad Request"
        );
    }
    echo json_encode($output);
?>
