<?php

    namespace Pilot\Utilities {

        class Errors {

            private string $prefix;
            private array $errors;
            function __construct() {
                $this->prefix = "Pilot Framework Error ({prefix}):";
                $this->errors = array();
                // Configs 
                $errorKey = "configs"; $prefix = $this->prefix; $prefix = str_replace("{prefix}", $errorKey, $prefix);
                $this->errors[$errorKey]["empty"] = array("code" => 0, "message" => "$prefix Misconfiguration of '/configs.json' file.");
                // Database
                $errorKey = "database"; $prefix = $this->prefix; $prefix = str_replace("{prefix}", $errorKey, $prefix);
                $this->errors[$errorKey]["host"] = array("code" => 0, "message" => "$prefix Missing or invalid host.");
                $this->errors[$errorKey]["credentials"] = array("code" => 0, "message" => "$prefix Missing or invalid username or password.");
                $this->errors[$errorKey]["name"] = array("code" => 0, "message" => "$prefix Missing or invalid database name.");
                $this->errors[$errorKey]["prefix"] = array("code" => 0, "message" => "$prefix Missing or invalid table prefix to replace with #__.");
                $this->errors[$errorKey]["connection"] = array("code" => 0, "message" => "$prefix Unable to connect. Please make sure you've specified a valid host.");
                // Custom
                // $customErrors = (file_exists(__DIR__ . "/customs/errors.json"))?json_decode(file_get_contents(__DIR__ . "/customs/errors.json")):array();
                // if (is_array($customErrors) && count($customErrors)) { foreach($customErrors as $errCat => $errMsg) { if (!$this->errors[$errCat]) { $this->errors[$errCat] = $errMsg; } } }
            }

            function get($category, $key) {
                $prefix = $this->prefix; $prefix = str_replace("{prefix}", "Internal", $prefix);
                return $this->errors[$category][$key]?:array(
                    "code" => 0,
                    "message" => "$prefix Unknown internal error."
                );
            }

            function echo($category, $key) {
                $prefix = $this->prefix; $prefix = str_replace("{prefix}", "Internal", $prefix);
                die (json_encode($this->errors[$category][$key])?:json_encode(array(
                    "code" => 0,
                    "message" => "$prefix Unknown internal error."
                ))); exit;
            }

        }

    }

    namespace Pilot {

        class Utilities {

            function aliasize($value) {
                $value = str_replace(array("#", "?", "$", "'", '"', "^", "@", "%", "/", "(", ")", "=", "*"), "", $value);
                $value = str_replace(array("_", "&", " "), "-", $value);
                return strtolower($value);
            }

            function allowedCharacters($string, $pattern = array()) {
                for ($i = 0; $i < strlen($string); $i++) { if (!in_array(substr($string, $i, 0), $pattern)) { return false; } }
                return true;
            }

            function arrayToObject($array, $jsonBased = false) {
                if ($jsonBased) { return json_decode(json_encode($array)); } else {
                    if (is_array($array)) {
                        $object = New \STDCLASS();
                        foreach($array as $key => $value) {
                            if (is_array($value)) {
                                $object->$key = $this->arrayToObject($value);
                            } else { $object->$key = $value; }
                        }
                        return $object;
                    } else { return $array; }
                }
            }

            function HTMLAttributeEncode($object) { return (is_object($object) || is_array($object))? base64_encode(json_encode($object)) : base64_encode($object); }
            function HTMLAttributeDecode($string) { return json_decode(base64_decode($string)); }

            function copyd($source, $dest) {
                try {
                    if (!file_exists($dest)) { mkdir($dest, 0755); }
                    foreach (scandir($source) as $item) {
                        if (!in_array($item ,array(".",".."))) {
                            $rsource = $source . DIRECTORY_SEPARATOR . $item;
                            $rdest = $dest . DIRECTORY_SEPARATOR . $item;
                            if (is_dir($rsource)) {
                                $this->copyd($rsource, $rdest);
                            } else {
                                if (!file_exists($rsource)) { copy($rsource, $rdest); }
                            }
                        }
                    }
                    return true;
                } catch (\Exception $e) { return false; }
            } function copyDirectory($source, $dest) { return $this->copyd($source, $dest); }

            function clientIP() {
                if (getenv("HTTP_CLIENT_IP"))
                    return getenv("HTTP_CLIENT_IP");
                else if(getenv("HTTP_X_FORWARDED_FOR"))
                    return getenv("HTTP_X_FORWARDED_FOR");
                else if(getenv("HTTP_X_FORWARDED"))
                    return getenv("HTTP_X_FORWARDED");
                else if(getenv("HTTP_FORWARDED_FOR"))
                    return getenv("HTTP_FORWARDED_FOR");
                else if(getenv("HTTP_FORWARDED"))
                    return getenv('HTTP_FORWARDED');
                else if(getenv("REMOTE_ADDR"))
                    return getenv("REMOTE_ADDR");
                else
                    return "UNKNOWN";
            } function getClientIP() { return $this->clientIP(); }

            function custom_echo($x, $length) { if(strlen($x)<=$length) { echo $x; } else { $y=substr($x,0,$length) . '...'; echo $y; } }

            function domainName($url = null, $allowSubdomain = false) {
                if ($allowSubdomain) { return $_SERVER["HTTP_HOST"]; } else {
                    $host = $url?parse_url($url)["host"]:$_SERVER["HTTP_HOST"]; $host = explode(".", $host);
                    return $host[count($host)-2] . "." . $host[count($host)-1];
                }
            }

            function format_filesize($bytes, $unit = "MB") {
                if ($unit == "GB") { return number_format($bytes / 1073741824, 2); }
                if ($unit == "MB") { return number_format($bytes / 1048576, 2); }
                if ($unit == "KB") { return number_format($bytes / 1024, 2); }
                return $bytes;
            }

            function formatBytes($bytes, $precision = 2, $returnUnit = false) { 
                $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
                $bytes = max($bytes, 0); 
                $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
                $pow = min($pow, count($units) - 1); 

                // Uncomment one of the following alternatives
                // $bytes /= pow(1024, $pow);
                // $bytes /= (1 << (10 * $pow)); 

                return $returnUnit ? round($bytes, $precision) . ' ' . $units[$pow] : round($bytes, $precision);
            } 

            function generateRandomString($length = 10, $probeType = "chars") {
                $password = ""; $probe = array();
                if ($probeType == "chars") {
                    $probe = array(
                        "1","2","3","4","5","6","7","8","9","0",
                        "a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
                        "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"
                    );
                } else if ($probeType == "numbers") {
                    $probe = array("1","2","3","4","5","6","7","8","9","0");
                } else if ($probeType == "password") {
                    $probe = array(
                        "1","2","3","4","5","6","7","8","9","0",
                        "a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
                        "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
                        "#", "$", "?", "=", ".", "!", "-", "_"
                    );
                }
                for($i = 0; $i < $length; $i++) { $password.= $probe[rand(0, count($probe) - 1)]; }
                return $password;
            } function generateRandomPassword($length = 16) { return $this->generateRandomString($length, "password"); }

            function getIpInfo($ip, $token = "YOUR_TOKEN") {
                $info = file_get_contents("https://ipinfo.io/$ip/json?token=$token");
                $info = json_decode($info);
                return $info;
            }

            function google_recaptcha($captcha = null) { if (!$captcha) { return false; }
                $secret = "YOUR_SECRET";
                $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=". $captcha ."&remoteip=". $_SERVER["REMOTE_ADDR"]);
                $decode = json_decode($verify, true);
                if ($decode["success"]) { return true; } else { return false; }
            } function googleRecaptcha($captcha = null) { return $this->google_recaptcha($captcha); }

            function hashPassword($password) { return password_hash($password, PASSWORD_BCRYPT); } function hash_password($password) { return $this->hashPassword($password); }

            function is_date($date, $format = 'Y-m-d H:i:s') { $d = \DateTime::createFromFormat($format, $date); return $d && $d->format($format) == $date; }

            function is_decimal($val) { return is_numeric($val) && floor($val) != $val; }

            function objectToArray($object, $jsonBased = false) {
                if ($jsonBased) { return json_decode(json_encode($object), true); } else {
                    if (is_object($object)) {
                        $array = array();
                        foreach($object as $key => $value) {
                            if (is_object($value)) {
                                $array[$key] = $this->objectToArray($value);
                            } else { $array[$key] = $value; }
                        }
                        return $array;
                    } else { return $object; }
                }
            }

            function validate($value, $type = null) {
                if ($type == "array" && !is_array($value)) { return false;
                } else if ($type == "boolean" && !is_bool($value)) { return false;
                } else if ($type == "email" && !filter_var($value, FILTER_VALIDATE_EMAIL)) { return false;
                } else if ($type == "object" && !is_object($value)) { return false;
                } else if ($type == "numeric" && !is_numeric($value)) { return false;
                } else { if (!isset($value)) { return false; }}
                return true;
            }

        }

    }

?>
