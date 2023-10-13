<?php

    namespace Pilot\API\Requests {

        class Options {
            
            public QueryOptions|null $query = null;
        }

        class QueryOptions {
            public string $where;
            public string|null $group = null;
            public string|null $order = null;
            public int|null $limit = null;
            function __construct(array|object $options) {
                if (is_object($options)) { $options = (array) $options; }
                $this->where = $options["where"];
                $this->group = isset($options["group"]) ? $options["group"] : null;
                $this->order = isset($options["order"]) ? $options["order"] : null;
                $this->limit = isset($options["limit"]) ? $options["limit"] : null;
            }
        }

        Enum ValidationTableError {
            case TABLE;
            case METHOD;
        }

        class Pilot extends \Pilot\API\Requests\Executor {

            private ?string $tableName = null;
            private ?string $prefix = null;
            private \Pilot\API\Requests $request;
            private ?ValidationTableError $validationTableError = null;
            function __construct(\Pilot\API\Requests $request) {
                $this->request = $request;
                parent::__construct($request);
                $this->validateTable($this->params()->get(2));
                if (!is_null($this->validationTableError)) {
                    if ($this->validationTableError === ValidationTableError::TABLE) {
                        (New \Pilot\API\Response)->setCode(400)->setError("Table not found or not allowed in schema.json")->echo();
                    } else if ($this->validationTableError === ValidationTableError::METHOD) {
                        (New \Pilot\API\Response)->setCode(400)->setError("Method not allowed in schema.json")->echo();
                    }
                }
            }

            function deleteIndex(QueryOptions $options):\Pilot\API\Response {
                $response = New \Pilot\API\Response;
                try {
                    global $database;
                    if (count($database->GET_WHERE($this->prefix . $this->tableName, $options->where))) {
                        return ($database->DELETE_WHERE($this->prefix . $this->tableName, $options->where)) ? $response->setCode(200) : $response->setCode(500)->setError("Unknown internal error: maybe a bug (?)");
                    } else { return $response->setCode(204)->setError("Content not found or already deleted."); }
                } catch (\MeekroDBException $error) { return $response->setCode(500)->setError($error->getMessage()); }
            }

            function deleteShow(string $show, string $primary_key = "id") {
                $response = New \Pilot\API\Response;
                try {
                    global $database;
                    $query = "$primary_key='$show'";
                    if (count($database->GET_WHERE($this->prefix . $this->tableName, $query))) {
                        $result = $database->DELETE_WHERE($this->prefix . $this->tableName, $query);
                        return ($result) ? $response->setCode(200) : $response->setCode(500)->setError("Unknown internal error: maybe a bug (?)");
                    } else { return $response->setCode(204)->setError("Content not found or already deleted."); }
                } catch (\MeekroDBException $error) { return $response->setCode(500)->setError($error->getMessage()); }
            }

            function getIndex(QueryOptions $options = null):\Pilot\API\Response {
                global $database;
                $data = array();
                $response = New \Pilot\API\Response;
                if (is_object($options)) {
                    try {
                        $data = $database->GET_WHERE($this->prefix . $this->tableName, $options->where, $options->order, $options->group, $options->limit);
                        if (count($data)) { $response->setCode(200); } else { $response->setCode(204); }
                    } catch (\MeekroDBException $error) { $response->setCode(500)->setError($error->getMessage()); }
                } else {
                    try {
                        $data = $database->GET($this->prefix . $this->tableName);
                        if (count($data)) { $response->setCode(200); } else { $response->setCode(204); }
                    } catch (\MeekroDBException $error) { $response->setCode(500)->setError($error->getMessage()); }
                }
                return $response->setData($data);
            }

            function getShow(string $show, string $primary_key = "id"):\Pilot\API\Response {
                $response = New \Pilot\API\Response;
                try {
                    global $database;
                    $result = $database->GET_WHERE($this->prefix . $this->tableName, "$primary_key='$show'");
                    return (count($result)) ? $response->setCode(200)->setData($result) : $response->setCode(404);
                } catch (\MeekroDBException $error) { return $response->setCode(500)->setError($error->getMessage()); }
            }

            function getShowRelations(string $show):\Pilot\API\Response {
                $response = New \Pilot\API\Response;
                $relationTable = $this->params()->get(5);
                if (is_null($relationTable)) { return $response->setCode(400)->setError("Missing {relationTableName} (5) param."); }
                $tableSchema = $this->getTableSchema($this->tableName);
                if (is_null($tableSchema)) { return $response->setCode(500)->setError("Could not retrieve table schema. Maybe a bug (?)"); }
                if (!isset($tableSchema["relations"])) { return $response->setCode(404)->setError("There aren't relations for this table."); }
                $relation_key = $this->getRelationKey($relationTable, $tableSchema["relations"], isset($tableSchema["relation_key"]) ? $tableSchema["relation_key"] : null);
                if (!is_string($relation_key)) { return $response->setCode(500)->setError("Could not retrieve the relation key between the origin table and the related table. Please check your schema.json"); }
                global $database; $data = $database->GET_WHERE($this->prefix . $relationTable, "$relation_key='$show'");
                if (count($data)) { return $response->setCode(200)->setData($data); } else { return $response->setCode(204); }
            }

            function patchIndex(array $data, QueryOptions $options):\Pilot\API\Response {
                global $database;
                $response = New \Pilot\API\Response;
                try {
                    if ($database->UPDATE_WHERE($this->prefix . $this->tableName, $data, $options->where)) { return $response->setCode(200)->setData($data); }
                    return $response->setCode(500)->setError("There was an internal error while executing the query based on your PATCH request.");
                } catch (\MeekroDBException $error) { return $response->setCode(500)->setError($error->getMessage()); }
            }

            function patchShow(string $show, array $data, string $primary_key = "id"):\Pilot\API\Response {
                global $database;
                $response = New \Pilot\API\Response;
                try {
                    if ($database->UPDATE_WHERE($this->prefix . $this->tableName, $data, "$primary_key='$show'"))  { return $response->setCode(200)->setData($data); }
                    return $response->setCode(500)->setError("There was an internal error while executing the query based on your PATCH request.");
                } catch (\MeekroDBException $error) { return $response->setCode(500)->setError($error->getMessage()); }
            }

            function postIndex(array $data):\Pilot\API\Response {
                global $database;
                $response = New \Pilot\API\Response;
                try {
                    if ($database->SET($this->prefix . $this->tableName, (object) $data)) { $response->setCode(201)->setData($data); } else { $response->setCode(500)->setError("There was an internal error while executing the query based on your POST request."); }
                } catch (\MeekroDBException $error) { $response->setCode(500)->setError($error->getMessage()); }
                return $response;
            }

            function validateTable(string $tableName):bool { 
                $schema = $this->getTableSchema($tableName);
                if (is_null($schema)) { $this->validationTableError = ValidationTableError::TABLE; return false; }
                if (isset($schema["methods"]) && count($schema["methods"])) { if (!in_array($this->request->method, $schema["methods"])) { $this->validationTableError = ValidationTableError::METHOD; return false; } }
                global $configs;
                $this->prefix = $configs->get()["database"]["prefix"] . "_";
                $this->tableName = $tableName;
                return true;
            }

            private array|null $tableSchema = null;
            function getTableSchema(string $tableName):array|null {
                if (is_null($this->tableSchema)) {
                    $index = null;
                    if (is_array($this->getSchema())) { $index = array_search($tableName, array_column($this->getSchema(), "table")); } else { return null; }
                    if (is_numeric($index) && $index >= 0) { $this->tableSchema = $this->getSchema()[$index]; } else { return null; }
                }
                return $this->tableSchema;
            }

        }

    }
