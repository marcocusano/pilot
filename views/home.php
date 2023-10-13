<?php

    // Simple usage
    $response = New \Pilot\API\Response;
    $response->setCode(200);

    $data = array( "message" => "Welcome to Pilot Framework", );

    $response->setData($data);
    $response->echo();

    // Perfect usage
    // (New \Pilot\API\Response)->setCode(200)->setData(array("message" => "Welcome to Pilot Framework"))->echo();

?>