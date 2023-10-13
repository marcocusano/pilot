<?php

    namespace Pilot {

        class Params {

            public array $params = array();

            public array $queryParams = array();

            function __construct() {
                $this->queryParams = $_GET; unset($this->queryParams["params"]);
                $params = (isset($_GET["params"])) ? explode("/", $_GET["params"]) : array();
                if (count($params)) { } else {
                    $params = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
                    $params = explode("/", $params, 1);
                }
                if (is_array($params)) {
                    foreach ($params as $param) {  $this->params[] = str_replace("/", "", $param); }
                    return true;
                } else { return false; }
            }

            function get(int $index = null):string|null {
                try {
                    return $this->hasParams() ? (isset($index) ? (isset($this->params[$index]) ? $this->params[$index] : null) : $this->params) : null;
                } catch (\Throwable $th) { return null; }
            }

            function hasParam(string $param):bool { return in_array($param, $this->params) ? true : false; }

            function hasParams():bool { return count($this->params) ? true : false; }

            function hasQueryParam(string $param):bool { return in_array($param, $this->queryParams) ? true : false; }

            function hasQueryParams():bool { return count($this->queryParams) ? true : false; }

            function queryParams(string $key = null):string|null { return $this->hasQueryParams() ? (isset($key) ? $this->queryParams[$key] : $this->queryParams) : null; }

        }

    }

?>