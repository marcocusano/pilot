<?php

    // Authorization
    $authorization = New \Pilot\API\Authorization;
    global $params;
    $request = New \Pilot\API\Requests($authorization, $params);
    return $request;
    
?>