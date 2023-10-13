<?php

    namespace Pilot {

        class Configs {
            
            public bool $validated = false;
            private array $configs;
            function __construct(Array $overrideConfigs = null) {
                $this->configs = array();
                $configs = json_decode(file_get_contents(__ROOT__ . "/configs.json"), true);
                if (!is_array($configs) && !is_array($overrideConfigs)) { 
                    (New \Pilot\Utilities\Errors)->echo("configs", "empty");
                    return null;
                } else if (is_array($configs)) { foreach($configs as $key => $value) { $this->configs[$key] = $value; }}
                if (is_array($overrideConfigs)) { foreach($overrideConfigs as $key => $value) { $this->configs[$key] = $value; }}
                $this->validated = true;
            }

            function get(Array $keys = null):mixed {
                if ($this->validated) {
                    if (is_array($keys)) {
                        $value = null; $first = true;
                        foreach ($keys as $key) { $value = $first ? $this->configs[$key] : $value[$key]; $first = false; }
                    } else { return $this->configs; }
                } else { return null; }
            }

            function save():bool {
                if ($this->validated) {

                } else { return false; }
            }

        }

    }

?>