<?php

    namespace Pilot {

        class Redirects {

            private array $redirects;
            function __construct() {
                $this->redirects = array();
            }

            function addRedirect($path, $to, $type = 301):array {
                return $this->redirects[$path] = array(
                    "type" => $type,
                    "to" => $to
                );
            }

            function go($withoutParams = true):mixed {
                $scheme = null; if ($withoutParams) { $scheme = explode("?", $_SERVER["REQUEST_URI"])[0]; } else { $scheme = $_SERVER["REQUEST_URI"]; }
                if (isset($this->redirects[$scheme])) { header("Location: " . $this->redirects[$scheme]["to"], true, $this->redirects[$scheme]["type"]); exit; }
                return false;
            }

        }

    }

?>
