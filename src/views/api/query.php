<?php 

    $request = require_once(__VIEWS__ . "/api.php");
    $request->executor()->response()->echo();

?>