<?php

    namespace Pilot\API\Requests {

        class Query extends \Pilot\API\Requests\Executor {

            function response():\Pilot\API\Response {
                $response = New \Pilot\API\Response;
                $query = $this->params()->queryParams("query");
                if (is_string($query)) {
                    global $database;
                    try {
                        $r = $database->QUERY($query);
                        if ($response) { return $response->setCode(200)->setData($r); } else { return $response->setCode(500); }
                    } catch (\MeekroDBException $error) { return $response->setCode(500)->setError($error->getMessage()); }
                } else { return $response->setCode(400)->setError("Missing {query} params."); }
            }

        }

    }

?>