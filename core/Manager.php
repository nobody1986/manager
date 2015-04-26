<?php

namespace core;

class Manager {

    protected $runner = NULL;

    function __construct($runner) {
        $this->runner = $runner;
    }

    function run() {
        $webserver = new \core\httpd\Httpd();
        $router = new \core\httpd\Router();
        $webserver->setRouter($router);
        $webserver->start();
    }

    function listtask() {
        
    }

}
