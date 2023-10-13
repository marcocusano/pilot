<?php

    header('Content-Type: application/json; charset=utf-8');
    ini_set('display_errors', 'On'); error_reporting(E_ALL);
    set_error_handler(function ($type, $message, $file, $line) {
        http_response_code(500);
        die(json_encode(array(
            "code" => 500,
            "message" => "Pilot Framework Custom Error Handler",
            "error" => "PHP Error",
            "data" => array(
                "file" => $file,
                "line" => $line,
                "type" => $type,
                "message" => $message,
            ),
        ))); exit;
    });
    set_exception_handler(function($ex){
        http_response_code(500);
        die(json_encode(array(
            "code" => 500,
            "message" => "Pilot Framework Custom Exception Handler",
            "error" => "PHP Exception",
            "data" => array(
                "file" => $ex->getFile(),
                "line" => $ex->getLine(),
                "message" => $ex->getMessage()
            ),
        ))); exit;
    });

?>