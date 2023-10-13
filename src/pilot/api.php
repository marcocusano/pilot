<?php

    namespace Pilot\API {

        class Authorization {

            private string $authorization;
            function __construct() {
                $authorization = str_replace(array("?", "=", "#", "/", "'", '"', ",", "."), "", $_SERVER["HTTP_AUTHORIZATION"]);
                if (is_string($authorization)) { $this->authorization = $authorization; }
            }

            function validate(bool|null $forcedValue = null):bool {
                // Use for debug purpose only, just leave it to null
                if (is_bool($forcedValue)) { return $forcedValue; }
                // Validation
                if (!empty($this->authorization)) { global $database;
                    try {
                        $authorizations = $database->GET_WHERE("#__applications", "authorization='$this->authorization' AND status=1 AND expiration_date<'" . date("Y-m-d H:i:s") . "'");
                        return (count($authorizations)) ? true : false;
                    } catch (\MeekroDBException $e) {
                        $response = (New Response)->setCode(500);
                        $response->setError($e->getMessage());
                        $response->echo();
                        exit;
                    }
                } else {
                    $response = (New Response)->setCode(500);
                    $response->setError("Missing Authorization Code");
                    $response->echo();
                }
            }

        }

        class Requests {

            public Authorization $auth;
            public \Pilot\Params $params;
            public string $method;
            function __construct(Authorization $auth, \Pilot\Params $params) {
                $this->auth = $auth;
                $this->params = $params;
                $this->method = $_SERVER['REQUEST_METHOD'];
            }

            function executor():Executor|\Pilot\API\Requests\Auth|\Pilot\API\Requests\Pilot|\Pilot\API\Requests\Query|null {
                if (!$this->auth->validate(true)) { (New Response)->setCode(401)->echo(); exit; }
                switch ($this->params->get(1)) {
                    case "auth":
                        return (New \Pilot\API\Requests\Auth($this));
                        break;
                    case "pilot":
                        return (New \Pilot\API\Requests\Pilot($this));
                        break;
                    case "query":
                        return (New \Pilot\API\Requests\Query($this));
                        break;
                    default:
                        return null;
                        break;
                }
            }

        }

        enum ResponseType {
            case JSON;
            case DEBUG;
        }

        class Response {

            private array $response;
            function __construct() {
                $this->response = array(
                    "request" => array(
                        "method" => $_SERVER["REQUEST_METHOD"],
                        "endpoint" => (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
                        "timestamp" => time(),
                    ),
                    "code" => 400,
                    "message" => "Bad request",
                    "data" => array(),
                );
            }

            function setCode(int $code):Response {
                http_response_code($code);
                $this->response["code"] = $code;
                $this->response["message"] = $this->message(strval($code));
                return $this;
            }

            function setData(array $data):Response { $this->response["data"] = $data; return $this; }

            function setError(mixed $error):Response {
                $this->response["error"] = $error;
                return $this;
            }

            function echo(ResponseType $type = ResponseType::JSON):bool {
                $this->setCode(http_response_code());
                switch ($type) {
                    case ResponseType::JSON:
                        echo json_encode($this->response);
                        exit;
                        return true;
                        break;
                    case ResponseType::DEBUG:
                        var_dump($this->response);
                        exit;
                        return true;
                        break;
                    default:
                        return false;
                        break;
                }
            }

            private array $statusCodes;
            function message($code):string { global $lang;
                if (!isset($this->statusCodes)) { $this->statusCodes = json_decode(file_get_contents("$lang/statusCodes.json"), true); }
                return $this->statusCodes[$code]?:"Pilot Framework: Unknown status code error.";
            }

        }

    }

    namespace Pilot\API\Requests {

        require_once __DIR__ . "/ApiRequests/auth.php";
        require_once __DIR__ . "/ApiRequests/pilot.php";
        require_once __DIR__ . "/ApiRequests/query.php";

        class Executor {

            private \Pilot\API\Requests $request;
            function __construct(\Pilot\API\Requests $request) {
                $this->request = $request;
            }

            private array|null $schema = null;
            function getSchema():array|null {
                if (is_null($this->schema)) {
                    $schemaFilename = __ROOT__ . "/schema.json";
                    if (file_exists($schemaFilename)) {
                        try {
                            $this->schema = json_decode(file_get_contents($schemaFilename), true);
                            if (is_null($this->schema)) { (New \Pilot\API\Response)->setCode(500)->setError("Invalid schema.json")->echo(); return null; }
                        } catch (\TypeError $th) {
                            (New \Pilot\API\Response)->setCode(500)->setError("Invalid schema.json")->echo();
                            return null;
                        }
                    } else { (New \Pilot\API\Response)->setCode(500)->setError("Missing schema.json")->echo(); return null; }
                }
                return $this->schema;
            }

            function params() { return $this->request->params; }

            function request() { return $this->request; }

            function getRelationKey(string $tableName, array $relations, string $relation_key = null):string|null { return isset($relations[$tableName]) ? $relation_key = $relations[$tableName] : $relation_key; }

        }

        class Sanitizer {

            function sanitize(string $method):bool {
                global $_PATCH;
                $response = New \Pilot\API\Response;
                try {
                    $data = file_get_contents("php://input");
                    switch ($method) {
                        case 'PATCH':
                            $_PATCH = json_decode($data, true);
                            if (is_null($_PATCH)) { parset_str($data, $_PATCH); }
                            break;
                        case 'POST':
                            $_POST = json_decode($data, true);
                            if (is_null($_POST)) { parse_str($data, $_POST); }
                            break;
                        default:
                            return true;
                            break;
                    }
                    if (is_null($_POST) && is_null($_PATCH)) { $response->setCode(400)->setError("Missing or invalid JSON Data")->echo(); return false; }
                    return true;
                } catch (\Throwable $error) {
                    $response->setCode(500)->setError("There was an error while decoding the JSON Data: " . $error->getMessage())->echo();
                }
            }

        }

    }

?>