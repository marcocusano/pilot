<?php

    /////////////////
    // Definitions //
    /////////////////

    define("__ROOT__", __DIR__);
    define("__PILOT__", __ROOT__ . "/pilot");
    define("__COMPOSER__", __ROOT__ . "/vendor");
    define("__LOCALES__", __ROOT__ . "/locales");
    define("__VIEWS__", __ROOT__ . "/views");

    //////////////////
    // Requirements //
    //////////////////

    require_once __COMPOSER__ . "/autoload.php";
    require_once __PILOT__ . "/loader.php";

    ///////////////
    // Variables //
    ///////////////

    // Redirects
    $redirects = New \Pilot\Redirects;
    include_once __ROOT__ . "/redirects.php";
    $redirects->go();

    // Configs
    $databaseConfigs = array(
        "database" => array(
            "host" => "localhost",
            "port" => "3306",
            "name" => "dbname",
            "prefix" => "pilot_",
            "user" => "root",
            "password" => "root"
        )
    );
    $configs = New \Pilot\Configs(); // Use $databaseConfigs for an override of your /configs.json.database sending the array to the Class
    $lang = __LOCALES__ . "/" . $configs->get()["options"]["locale"];

    // Database
    $database = New \STDCLASS();
    if (!$configs->get()["installation"]["required"]) {
        $database = New \Pilot\Database($configs->get()["database"]); // Don't use $databaseConfigs if not using in $configs too
        try { $database->connect(); } catch(\MeekroDBException $error) {
            $response = New \Pilot\API\Response;
            $response->setCode(500);
            $response->setError("Unable to connect to MySQL server!");
            $response->echo();
        }
    }
 

    // Router
    $router = New \Pilot\Router(__VIEWS__);

    // Params
    $params = New \Pilot\Params;

    /////////////////////////
    // SANITIZE POST/PATCH //
    /////////////////////////

    $_PATCH = null; $_POST = null;
    (New \Pilot\API\Requests\Sanitizer)->sanitize($_SERVER["REQUEST_METHOD"]);

?>