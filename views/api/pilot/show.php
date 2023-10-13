<?php

    include_once __VIEWS__ . "/api/pilot.php";
    
    $show = $request->executor()->params()->get(3);
    $primary_key = $request->executor()->getTableSchema($request->executor()->params()->get(2));
    if (is_array($primary_key) && isset($primary_key["primary_key"])) { $primary_key = $primary_key["primary_key"]; } else { $primary_key = "id"; }
    $primary_key = (isset($_GET["primary_key"]) && is_string($_GET["primary_key"])) ? $_GET["primary_key"] : (is_string($primary_key) ? $primary_key : "id");
    switch ($show) {
        case 'info':
        case 'structure':
            // Allows you to get the {tableName} structure
            // Under development, cannot be used...
            #$tableStructure = $request->executor()->getTableStructure($request->executor()->params()->get(2));
            #if (is_null($tableStructure)) {
            #    (New \Pilot\API\Response)->setCode(500)->setError("There was an internal error while getting the table structure. Maybe a bug (?)");
            #} else {
            #    (New \Pilot\API\Response)->setCode(200)->setData($tableStructure)->echo();
            #}
            (New \Pilot\API\Response)->setCode(500)->setError("Under development! Please try again later.")->echo();
            break;
        case 'relations':
            // Allows you to get relations between {tableName} from your schema.json.table{}.relations[]
            $tableSchema = $request->executor()->getTableSchema($request->executor()->params()->get(2));
            if (is_array($tableSchema) && isset($tableSchema["relations"])) {
                (New \Pilot\API\Response)->setCode(200)->setData($tableSchema["relations"])->echo();
            } else {
                (New \Pilot\API\Response)->setCode(204)->echo();
            }
            break;
        case 'schema':
            (New \Pilot\API\Response)->setCode(200)->setData($request->executor()->getTableSchema($request->executor()->params()->get(2)))->echo();
            break;
        default:
            switch ($request->method) {
                case 'DELETE':
                    $request->executor()->deleteShow($show, $primary_key)->echo();
                    break;
                case 'GET':
                    $extra = $request->executor()->params()->get(4);
                    switch ($extra) {
                        case 'relations':
                            $request->executor()->getShowRelations($show)->echo();
                            break;
                        default:
                           $request->executor()->getShow($show, $primary_key)->echo();
                            break;
                    }
                    break;
                case 'PATCH':
                    global $_PATCH;
                    $request->executor()->patchShow($show, $_PATCH , $primary_key)->echo();
                    break;
                default:
                    (New \Pilot\API\Response)->setCode(400)->setError("Invalid Request Method")->echo();
                    break;
            }
            break;
    }



?>