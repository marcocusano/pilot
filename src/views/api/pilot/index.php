<?php

    include_once __VIEWS__ . "/api/pilot.php";
    $options = (isset($_GET["options"]) && is_array($_GET["options"])) ? New \Pilot\API\Requests\QueryOptions($_GET["options"]) : null;
    switch ($request->method) {
        case 'DELETE':
            if (is_null($options)) { (New \Pilot\API\Response)->setCode(400)->setError("Missing {QueryOptions} array")->echo(); }
            $request->executor()->deleteIndex($options)->echo();
            break;
        case 'GET':
            $request->executor()->getIndex($options)->echo();
            break;
        case 'PATCH':
            global $_PATCH;
            if (is_null($options)) { (New \Pilot\API\Response)->setCode(400)->setError("Missing {QueryOptions} array")->echo(); }
            $request->executor()->patchIndex($_PATCH, $options)->echo();
            break;
        case 'POST':
            $options = (isset($_GET["options"])) ? (is_array($_GET["options"]) ? (New \Pilot\API\Requests\QueryOptions($_GET["options"])) : $_GET["options"]) : "id";
            $request->executor()->postIndex($_POST, $options)->echo();
        default:
            (New \Pilot\API\Response)->setCode(405)->setError("Invalid Request Method")->echo();
            break;
    }
    
?>