<?php
require("./config/config.php");
require("./core/Loader.php");

\core\Loader::autoLoad();

//$manager = new \Core\MultiProcessServer();
//$manager->start();


$webserver = new \core\httpd\Httpd();
$router = new \core\httpd\Router();
$webserver->setRouter($router);
$webserver->start();
