<?php

require_once "../config.php";
require_once "api.php";

if($_SERVER["REQUEST_METHOD"] !== "POST")
    exit_response(405, "invalid_request_method");

if($_POST["secret"] !== $api_write_key)
    exit_response(403, "invalid_secret");

const VALIDATION = [
    "ds1820_temp" => FILTER_VALIDATE_FLOAT,
    "am2302_temp" => FILTER_VALIDATE_FLOAT,
    "am2302_humidity" => FILTER_VALIDATE_FLOAT,
    "bmp180_temp" => FILTER_VALIDATE_FLOAT,
    "bmp180_pressure" => FILTER_VALIDATE_FLOAT
];

$filtered = filter_input_array(INPUT_POST, VALIDATION);

if($filtered === null)
    exit_response(400, "no_fields_given");

foreach($filtered as $key => $val) {
    if($val === false)
        exit_response(400, "invalid_field_value", $key);
    if($val === null)
        exit_response(400, "missing_field_value", $key);
}

$filtered = (object) $filtered;

$dbh = db_conn($pgsql_dsn);
$dbh->exec("SET search_path = weather_logging");

$query = $dbh->prepare("INSERT INTO weather (ds1820_temp, am2302_temp, am2302_humidity, bmp180_temp, bmp180_pressure) VALUES (?, ?, ?, ?, ?)");

if(!$query->execute([
    $filtered->ds1820_temp,
    $filtered->am2302_temp,
    $filtered->am2302_humidity,
    $filtered->bmp180_temp,
    $filtered->bmp180_pressure
]))
    exit_response(500, "database_error");

exit_response(200);
