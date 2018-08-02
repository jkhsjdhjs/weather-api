<?php

require_once "../config.php";
require_once "api.php";

function validate_timestamp($ts) {
    return DateTime::createFromFormat(DateTime::ATOM, $ts);
}

if($_SERVER["REQUEST_METHOD"] !== "GET")
    exit_response(405, "invalid_request_method");

$def = [
    "start" => [
        "filter" => FILTER_CALLBACK,
        "options" => "validate_timestamp"
    ],
    "end" => [
        "filter" => FILTER_CALLBACK,
        "options" => "validate_timestamp"
    ],
    "limit" => [
        "filter" => FILTER_VALIDATE_INT,
        "flags"  => FILTER_REQUIRE_SCALAR,
        "options" => [ "min_range" => 1 ]
    ]
];

$filtered = filter_input_array(INPUT_GET, $def);

foreach($filtered as $key => $val) {
    if($val === false)
        exit_response(400, "invalid_field_value", $key);
    if($val === null)
        exit_response(400, "missing_field", $key);
}

$filtered = (object) $filtered;

$dbh = db_conn($pgsql_dsn);

$query = $dbh->prepare(
    "WITH f AS (
      SELECT
        created_at,
        ds1820_temp as temp,
        am2302_humidity as humidity,
        bmp180_pressure as pressure,
        row_number() OVER (ORDER BY created_at ASC) as n
      FROM weather
      WHERE created_at BETWEEN ? AND ?
    )
    SELECT
      percentile_disc(0.5) WITHIN GROUP (ORDER BY created_at) as time,
      AVG(temp) as temp,
      stddev_pop(temp) as temp_stddev,
      AVG(humidity) as humidity,
      stddev_pop(humidity) as humidity_stddev,
      AVG(pressure) as pressure,
      stddev_poppressure) as pressure_stddev
    FROM f
    GROUP BY ceil(n / floor((SELECT COUNT(*) FROM f) / (? - 1)))
    ORDER BY time ASC"
);

if(!$query->execute([$filtered->start->format(DateTime::ATOM), $filtered->end->format(DateTime::ATOM), $filtered->limit]))
    exit_response(500, "database_error");

$rows = [];
while(($row = $query->fetch(PDO::FETCH_OBJ)) !== false)
    $rows[] = $row;

exit_response(200, null, null, $rows);
