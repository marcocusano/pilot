<?php

    namespace Pilot {

        Enum QueryOperators {
            case EQUALS;
            case LIKE;
            case MAJOR;
            case MAJOREQUALS;
            case MINOR;
            case MINOREQUALS;
            case NOT;
            case ISNOT;
        }

        class Database {

            //////////
            // Init //
            //////////

            private array $configs;
            private bool $connected = false;
            private string $prefix;
            private \MeekroDB $manager;
            private bool $error;
            function __construct($configs) {
                $this->configs = $configs;
                // Config checks ...
                if (!$configs["host"]) { (New \Pilot\Utilities\Errors)->echo("database", "host"); }
                if (!$configs["user"] || !$configs["password"]) { (New \Pilot\Utilities\Errors)->echo("database", "credentials"); }
                if (!$configs["name"]) { (New \Pilot\Utilities\Errors)->echo("database", "name"); }
                if (!$configs["prefix"]) { (New \Pilot\Utilities\Errors)->echo("database", "prefix"); } $this->prefix = $configs["prefix"];
                // Initialize MeekroDB ...
                $this->manager = new  \MeekroDB($configs["host"], $configs["user"], $configs["password"], $configs["name"], $configs["port"], (isset($configs["encode"])?$configs["encode"]:"UTF8MB4"));
                $this->connected = false; $this->error = false;
            }

            function onSuccess() { $this->connected = true; return true; }

            function onError() {
                $this->error = true;
                if ($this->checkingConnected) {
                    $this->checkingConnected = false;
                    $this->connected = false;
                }
                return true;
            }

            private bool $checkingConnected = false;
            function connect() { $this->checkingConnected = true; return $this->QUERY("SELECT 1", "boolean"); }

            function buildQuery ($query, $select = null, $from = null, $where = null, $group = null, $order = null, $limit = null, $offset = null) {
                if ($select) { $query.= "SELECT $select"; }
                if ($from) { $query.= " FROM $from"; }
                if ($where) { $query.= " WHERE $where"; }
                if ($group) { $query.= " GROUP BY $group"; }
                if ($order) { $query.= " ORDER BY $order"; }
                if (is_numeric($limit)) { $query.= " LIMIT $limit"; }
                if (is_numeric($offset)) { $query.= " OFFSET $offset"; }
                return $query;
            }

            function getOperator (QueryOperators $operator, $value = null) {
                $op = "=";
                switch ($operator) {
                    case QueryOperators::ISNOT:
                        $op = is_null($value) ? " IS NOT " : "='$value'";
                        break;
                    case QueryOperators::LIKE:
                        $op = "'%$value%'";
                    case QueryOperators::MAJOR:
                        $op = is_null($value) ? " IS NOT " : ">'$value'";
                        break;
                    case QueryOperators::MAJOREQUALS:
                        $op = is_null($value) ? " IS NOT " : ">='$value'";
                        break;
                    case QueryOperators::MINOR:
                        $op = is_null($value) ? " IS NOT " : "<'$value'";
                        break;
                    case QueryOperators::MINOREQUALS:
                        $op = is_null($value) ? " IS NOT " : "<='$value'";
                        break;
                    case QueryOperators::NOT:
                        $op = is_null($value) ? " IS NOT " : "<>'$value'";
                        break;
                    default:
                        $op = is_null($value) ? " IS NOT " : "='$value'";
                        break;
                }
                return $op;
            }

            function replacePrefix(string $string, bool $useDbName = true) {
                $allows = array("#__", "###", "prefix__", "pre__", "{prefix}", "{pre}");
                $pattern = ($useDbName) ? $this->configs["name"] . "." . $this->prefix . "_" : $this->prefix . "_";
                return str_replace($allows, $pattern, $string);
            }

            //////////////
            // Standard //
            //////////////

            function QUERY($sql, $responseType = "object", $useDbName = true, $debug = false) {
                $sql = $this->replacePrefix($sql, $useDbName);
                if ($debug) { var_dump($sql); echo "<br><br>"; }
                $response = $this->manager->QUERY($sql);
                if ($debug) { var_dump($response); exit; }
                if ($responseType == "array") {
                    return is_array($response)?$response:array();
                } else if ($responseType == "object") {
                    $object = (New \Pilot\Utilities)->arrayToObject($response, true);
                    return is_array($object)?$object:array();
                } else if ($responseType == "boolean") {
                    return (is_bool($response) || is_int($response) ? $response : ( is_array($response) ? (count($response) ? true : false) : false ));
                } else { return $this->error; }
            }

            function INFORMATION_SCHEME($table) {
                $sql = "SELECT *";
                $sql .= " FROM  INFORMATION_SCHEMA.TABLES";
                $sql .= " WHERE TABLE_SCHEMA = '" . $this->configs["name"] . "'";
                $sql .= " AND WHERE TABLE_NAME = '$table'";
                $result = $this->QUERY($sql, "object", false);
                if (count($result)) { return $result[0]; } else { return $result; }
            }

            function AUTO_INCREMENT($table) {
                $sql = "SELECT `AUTO_INCREMENT`";
                $sql .= " FROM  INFORMATION_SCHEMA.TABLES";
                $sql .= " WHERE TABLE_SCHEMA = '" . $this->configs["name"] . "'";
                $sql .= " AND TABLE_NAME = '$table'";
                $result = $this->QUERY($sql, "object", false);
                if (count($result)) { return $result[0]->AUTO_INCREMENT; } else { return -1; }
            }

            function PRIMARY_KEY($table, $checkUnique = false) {
                $table = New \Pilot\Database\Tables($table); $k = null;
                foreach($table->columns() as $c) { if ($c->Key == "PRI") { $k = $c->Field; } }
                return $k;
            } function PRIMARY($table, $checkUnique = false) { return $this->PRIMARY_KEY($table, $checkUnique); }

            ////////////
            // Select //
            ////////////

            function GET ($table, $column = null, $value = null, QueryOperators $operator = QueryOperators::EQUALS, $group = null, $order = null, $limit = null, $offset = null, $select = "*") {
                $where = $column && $value ? $column.$this->getOperator($operator, $value) : null;
                $query = $this->buildQuery("", $select, $table, $where, $group, $order, $limit, $offset);
                return $this->QUERY($query);
            }

            function GET_WHERE ($table, $where = null, $order = null, $group = null, $limit = null, $select = "*") {
                return $this->GET_PAGING($table, $where, $order, $group, $limit, null, $select);
            }

            function GET_PAGING ($table, $where = null, $order = null, $group = null, $limit = null, $offset = null, $select = "*") {
                $query = $this->buildQuery("", $select, $table, $where, $group, $order, $limit, $offset);
                return $this->QUERY($query);
            }

            function ROW ($table, $column = null, $value = null, $order = null, $group = null, $limit = null, $select = "*") {
                $results = $this->GET($table, $column, $value, QueryOperator::EQUALS, $group, $order, $limit, null, $select);
                if (is_array($results) && count($results)) { return $results[0]; } else { return null; }
            }

            function ROW_WHERE ($table, $where = null, $order = null, $group = null, $limit = null, $select = "*") {
                $results = $this->GET_COMPLEX($table, $where, $order, $group, $limit, $select);
                if (is_array($results) && count($results)) { return $results[0]; } else { return null; }
            }

            /////////////////////
            // Insert / Update //
            /////////////////////

            function SET ($table, $data, $sqlOnly = false) {
                $columns = array(); $values = array();
                foreach($data as $data_column => $data_value) { if ($data_value || is_numeric($data_value) || is_bool($data_value)) {
                    $data_value = str_replace("'", "''", $data_value);
                    $columns[] = $data_column; $values[] = "'$data_value'";
                } }
                $sql = "INSERT INTO $table (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ")";
                if ($sqlOnly) { return $sql; } else { return $this->QUERY($sql, "boolean"); }
            }

            function SET_WHERE ($table, $data, $where = null) {
                $sql = $this->SET($table, $data, true);
                if ($where) {
                    $sql.= " WHERE $where";
                    return $this->QUERY($where, "boolean");
                } else { return $this->QUERY($sql, "boolean"); }
            }

            function SET_BATCH (String $table, Array $data) {
                if (empty($data)) { return false; }
                $keys = array_keys($data[0]);
                $values = array_map(function($row) { return '(' . implode(',', array_map(function($value) { return $value; }, $row)) . ')'; }, $data);
                $sql = "INSERT INTO $table (" . implode(',', $keys) . ") VALUES " . implode(',', $values);
                return $this->QUERY($sql);
            }

            function UPDATE ($table, $data, $primaryKey = "id") {
                $sql = "UPDATE $table SET ";
                $index = 0;
                foreach($data as $data_column => $data_value) { if ($data_value || is_numeric($data_value) || is_bool($data_value)) {
                    $data_value = str_replace("'", "''", $data_value);
                    if ($index <= 0) { $sql.= "$data_column='$data_value'"; } else { $sql.= ", $data_column='$data_value'"; }
                    $index++;
                } }
                if ($this->QUERY($sql, "boolean")) { return 1; } else { return 0; }
            }

            function UPDATE_WHERE ($table, $data, $where) {
                $sql = "UPDATE $table SET ";
                $index = 0;
                foreach($data as $data_column => $data_value) { if ($data_value || is_numeric($data_value) || is_bool($data_value)) {
                    $data_value = str_replace("'", "''", $data_value);
                    if ($index <= 0) { $sql.= "$data_column='$data_value'"; } else { $sql.= ", $data_column='$data_value'"; }
                    $index++;
                } }
                $sql.= " WHERE $where";
                if ($this->QUERY($sql, "boolean")) { return 1; } else { return 0; }
            }

            //////////
            // Drop //
            //////////

            function DELETE ($table, $column, $value) {
                $value = str_replace("'", "''", $value);
                $sql = "DELETE FROM $table"; if ($column && $value) { $sql .= " WHERE $column='$value'"; }
                if ($this->QUERY($sql, "boolean")) { return $this->manager->affectedRows(); } else { return 0; }
            }

            function DELETE_WHERE ($table, $where) {
                $sql = "DELETE FROM $table";
                if ($where) { $sql .= " WHERE $where"; }
                if ($this->QUERY($sql, "boolean")) { return $this->manager->affectedRows(); } else { return 0; }
            }

            function TRUNCATE ($table) {
                $sql = "TRUNCATE TABLE $table";
                if ($this->QUERY($sql, "boolean")) { return $this->manager->affectedRows(); } else { return 0; }
            }

        }

    }

    namespace Pilot\Database {

        class Tables {

            function __construct($table_name) { global $database;
                $this->table = $table_name;
            }

            ////////////////////////
            // INFORMATION SCHEMA //
            ////////////////////////

            function columns() {
                if (!$this->columns) { $this->columns = $this->queryData("DESCRIBE $this->table;", $this->columns); }
                return $this->columns;
            }

            //////////
            // ROWS //
            //////////

            function delete() {

            }

            function cache($query) {
                $this->cache = $this->queryData($query, $this->cache);
                return $this->cache;
            }

            function get() {

            }

            function patch() {

            }

            function post() {

            }

            /////////////////////
            // INNER UTILITIES //
            /////////////////////

            private function queryData($query, $array) { global $database; if (is_array($array)) { return $array; } else { return $database->QUERY($query); } }

        }

        class Rows {

        }

    }

?>
