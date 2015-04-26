<?php

/**
 * 路由类
 *
 * @author ye
 */

namespace core\httpd;

class Router {

    protected $default_router = array(
        '/' => array(__CLASS__, 'index'),
    );
    protected $routers = array();

    function __construct($routers = array()) {
        if (empty($routers)) {
            $this->routers = $this->default_router;
        } else {
            $this->routers = $routers;
        }
    }

    function add($path, $func) {
        $this->routers[$path] = $func;
    }

    function dispatch(Request $request, Response $response) {
        if (empty($this->routers)) {
            $response->setCode(500);
            return false;
        }
        $curpath = $request->getPath();
        foreach ($this->routers as $path => $func) {
            if ($path == $curpath || preg_match('#^' . $path . '$#i', $curpath)) {
                if (is_callable($func)) {
                    call_user_func_array($func, array($request, $response));
                } else {
                    $response->setCode(500);
                    return false;
                }
            }
        }
    }

    function index($request, $response) {
        $response->write("Hello World!");
    }

}

