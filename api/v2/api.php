<?php

function exit_response($code, $err_msg = null, $err_subject = null, $data = null) {
    header("Access-Control-Allow-Origin: *");
    header("Content-type: application/json");
    http_response_code($code);
    $error = array_filter([
        "msg" => $err_msg,
        "subject" => $err_subject
    ], function ($val) { return $val !== null; });
    exit(json_encode([
        "error" => empty($error) ? null : $error,
        "data" => $data
    ]));
}

function db_conn($dsn) {
    $dbh = new PDO($dsn);
    $dbh->exec("SET search_path = weather_logging");
    return $dbh;
}
