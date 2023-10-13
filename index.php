<?php
    require_once __DIR__ . "/init.php";
    if ($configs->get()["options"]["maintenance"]) { include __VIEWS__ . "/maintenance.php"; }
    if ($configs->get()["installation"]["required"]) { include __VIEWS__ . "/install.php"; exit; } else { include __DIR__ . "/routes.php"; }
 ?>