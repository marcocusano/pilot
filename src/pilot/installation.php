<?php

    namespace Pilot {

        use Pilot\Installation\Response;

        class Installation {
            
            function __construct() {

            }

            function install():Response {             
                // Build using POST Data
                $c = $this->buildConfig();
                if (!is_array($c)) { return (New Response)->setTitle("Invalid Configuration")->setMessage("Missing or invalid parameters while building your configs.json file."); }
                $c["installation"]["required"] = false;
                $filename = __ROOT__ . "/configs.json";
                if (!file_put_contents($filename, json_encode($c, JSON_PRETTY_PRINT))) { return (New Response)->setContent("Configuration", "There was an error while overwriting the existing configs.json file. Please make sure you've the rights to manipulate files before installing the Pilot Framework."); }
                global $configs; $configs = New \Pilot\Configs; // Reload configurations
                global $database; $database = New \Pilot\Database($configs->get()["database"]);
                // Install the Template
                $template = (New \Pilot\Installation\Templates($_POST["template"]?:"scratch"))->install();
                if ($template->getType() === \Pilot\Installation\ResponseType::ERROR) { return $template; }
                // Configure an Application Token
                $token = New \Pilot\Installation\Tokens;
                return $token->generate();
            }

            function buildConfig():?array {
                // Check post required data integrity
                $requireds = array(
                    "database" => array("host", "port", "name", "prefix", "user", "password"),
                    "applications" => array("hash", "datetimeFormat"),
                    "options" => array("locale", "host")
                );
                global $configs;
                $c = $configs->get();
                foreach ($requireds as $mainKey => $keys) {
                    if (!isset($_POST["data"][$mainKey])) { return null; }
                    foreach($keys as $key) {
                        if (!isset($_POST["data"][$mainKey][$key])) { return null; }
                        $c[$mainKey][$key] = $_POST["data"][$mainKey][$key];
                    }
                }
                // Set defaults
                $c["database"]["prefix"] = str_replace(array("_", " ", "'", "{", "}", "#"), "", $c["database"]["prefix"]);
                $c["options"]["enableAuth"] = (isset($_POST["data"]["options"]["enableAuth"])) ? true : false;
                $c["options"]["enableQuery"] = (isset($_POST["data"]["options"]["enableQuery"])) ? true : false;
                // Check default required data integrity
                $defaultKeys = array(
                    "installation" => array(
                        "required" => false,
                    ),
                    "schema" => array(
                        "method" => "include"
                    ),
                    "options" => array(
                        "defaultEndpoint" => "/api",
                        "enableAuth" => false,
                        "enableQuery" => false,
                        "maintenance" => false
                    )
                );
                foreach($defaultKeys as $mainKey => $keys) {
                    if (!isset($c[$mainKey])) { $c[$mainKey] = array(); }
                    foreach($keys as $key => $value) { if (!isset($c[$mainKey][$key])) { $c[$mainKey][$key] = $value; }  }
                }
                // Integrity verified
                return $c;
            }

        }

    }

    namespace Pilot\Installation {

        enum ResponseType {
            case INPUT;
            case TEXT;
            case ERROR;
        }

        class Response {

            private function get(string $key):mixed { return $this->$key; }
            function getActionText():string { return $this->get("actionText"); }
            function getMessage():string { return $this->get("message"); }
            function getTitle():string { return $this->get("title"); }
            function getRedirect():string { return $this->get("redirect"); }
            function getType():ResponseType { return $this->get("type"); }

            private function set(string $key, mixed $value):Response { $this->$key = $value; return $this; }

            function setContent(string $title, string $message, ResponseType $type = ResponseType::TEXT):Response {
                $this->set("title", $title);
                $this->set("message", $message);
                $this->set("type", $type);
                return $this;
            }

            private ?string $message;
            function setMessage(string $message):Response { return $this->set("message", $message); }

            private string $redirect = "/";
            private string $actionText = "Reinstall";
            function setRedirect(string $redirect, string $text = "Reinstall"):Response {
                $this->set("redirect", $redirect);
                $this->set("actionText", $text);
                return $this;
            }

            private ?string $title;
            function setTitle(string $title):Response { return $this->set("title", $title); }

            private ResponseType $type = ResponseType::TEXT;
            function setType(ResponseType $type):Response { return $this->set("type", $type); }

        }

        class Templates {

            static $root = __VIEWS__ . "/install";

            private ?array $templates = null;
            private ?array $template = null;
            private string $defaultKey = "scratch";
            private string $templateKey = "scratch";
            private bool $exists = false;
            function __construct(string $templateKey = "scratch") {
                $this->templateKey = $templateKey;
                $this->templates = json_decode(file_get_contents(static::$root . "/templates.json"), true);
                $index = array_search($this->templateKey, array_column($this->templates, 'name'));
                if ($index >= 0) {
                    $this->template = $this->templates[$index];
                    $this->exists = true;
                }
            }

            function getTemplates():?array { return $this->templates; }
            function getTemplate():?array { return $this->template; }

            function install():\Pilot\Installation\Response {
                $schema = $this->installSchema();
                if ($schema->getType() === ResponseType::ERROR) { return $schema; };
                $database = $this->installDatabase();
                if ($database->getType() === ResponseType::ERROR) { return $database; }
                return (New Response)->setContent("JSON Schema and Database", "Installed successfully", ResponseType::TEXT);
            }

            function installSchema():\Pilot\Installation\Response {
                $response = New \Pilot\Installation\Response;
                if ($this->exists) {
                    $schemaFilename = static::$root . "/$this->defaultKey/schema.json";
                    if ($this->template["schema"]) { $schemaFilename = static::$root . "/$this->templateKey/schema.json"; }
                    $schemaData = json_decode(file_get_contents($schemaFilename), true);
                    if (!file_put_contents(__ROOT__ . "/schema.json", json_encode($schemaData, JSON_PRETTY_PRINT))) {
                        return $response->setContent("Installation Schema", "Error during the <code>schema.json</code> installation: cannot json encode the <code>schema.json</code> of <strong>$this->templateKey</strong>'s template.", ResponseType::ERROR);
                    }
                    return $response->setContent("JSON Schema", "JSON Schema installed successfully.", ResponseType::TEXT);
                } else { return $response->setContent("Installation Schema", "Template <code>$this->templateKey</code> not found during the <code>schema.json</code> installation.", ResponseType::ERROR); }
            }

            function installDatabase():\Pilot\Installation\Response {
                $response = New \Pilot\Installation\Response;
                if ($this->exists) {
                    $databaseFilename = static::$root . "/$this->defaultKey/database.sql";
                    if ($this->template["database"]) { $databaseFilename = static::$root . "/$this->templateKey/database.sql"; }
                    if (file_exists($databaseFilename)) {
                        $configs = New \Pilot\Configs; $mysqli = New \mysqli($configs->get()["database"]["host"], $configs->get()["database"]["user"], $configs->get()["database"]["password"], $configs->get()["database"]["name"], $configs->get()["database"]["port"]);
                        $query = str_replace("{prefix}", $configs->get()["database"]["prefix"], file_get_contents($databaseFilename));
                        if (!$mysqli->multi_query($query)) { return $response->setContent("SQL Database", "Internal error while executing the query in database.sql of <strong>$this->templateKey</strong>'s template.", ResponseType::ERROR); }
                        return $response->setContent("SQL Database", "Database installed successfully.", ResponseType::TEXT);
                    } else { return $response->setContent("SQL Database", "File <code>database.sql</code> not found in <code>$databaseFilename</code>.", ResponseType::ERROR); }
                } else { return $response->setContent("SQL Database", "Template <code>$this->templateKey</code> not found during the Database installation.", ResponseType::ERROR); }
            }

        }

        class Tokens {

            static $tableName = "#__api";

            private ?object $data = null;
            private bool $exists = false;
            function __construct(int $id = null) {
                if (is_string($id)) {
                    global $database;
                    $token = $database->GET_WHERE(static::$tableName, "id=$id");
                    if (count($token)) {
                        $this->data = $token[0];
                        $this->exists = true;
                    }
                }
            }

            function generate():\Pilot\Installation\Response {
                $response = New \Pilot\Installation\Response;
                if ($this->exists) { return $response->setContent("Multi applications not available yet", "You've already created an Application Token. Please check your Database to know more."); } else {
                    global $database; global $configs;
                    if (!$configs->get()["installation"]["required"]) {
                        $this->data = New \STDCLASS();
                        $this->data->token = hash($configs->get()["applications"]["hash"], date($configs->get()["applications"]["datetimeFormat"] . random_int(10, 99)));
                        $this->data->due_date = date($configs->get()["applications"]["due_date"]);
                        $this->data->active = 1;
                        try {
                            $database->SET(static::$tableName, $this->data);
                            return $response->setContent("Your Application Token", $this->data->token, ResponseType::INPUT)->setRedirect("/", "Let's Start!");
                        } catch (\MeekroDBException $error) { return (New \Pilot\Installation\Response)->setContent("Database Error", $error->getMessage()); }
                    } else {
                        return $response->setContent("Installation required", "You've to complete the installation step first, before generating an Application Token.");
                    }
                }
            }

            function getData():object|null { return ($this->exists) ? $this->data : null; }

            private bool $validated = false;
            function validate():bool {
                if (!$this->validated) { if ($this->exists && $this->data->active && date("Y-m-d H:i:s") < date("Y-m-d H:i:s", strtotime($this->data->due_date))) { $this->validated = true; } }
                return $this->validated;
            }

        }

    }

?>