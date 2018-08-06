<?php

require_once "../config.php";
require_once "api.php";

function validate_timestamp($ts) {
    return new DateTime($ts);
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

$query = $dbh->prepare("SELECT weather_quantiles(?, ?, ?)");

if(!$query->execute([$filtered->start->format(DateTime::ATOM), $filtered->end->format(DateTime::ATOM), $filtered->limit]))
    exit_response(500, "database_error");

$rows = [];
while(($row = $query->fetch(PDO::FETCH_OBJ)) !== false) {
    $row->time = (new DateTime($row->time))->format(DateTime::W3C);
    $rows[] = $row;
}

exit_response(200, null, null, $rows);
