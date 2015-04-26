<?php
namespace core\httpd;
/**
 * 基于lievent的httpserver 单进程
 *
 * @author ye
 */


$path = dirname(__FILE__);
require("{$path}/Server.php");
require("{$path}/Request.php");
require("{$path}/Response.php");
require("{$path}/Router.php");
require("{$path}/Mime.php");

class Httpd extends Server {

    protected $request = array();
    protected $response = array();
    protected $router = null;

    function __construct($port = 80, $ip = "0.0.0.0") {
        parent::__construct($port, $ip);
        $this->bindHandle();
    }

    function setRouter($router) {
        $this->router = $router;
    }
    
    function doRequest($buffer,$id){
        $this->router->dispatch($this->request[$id], $this->response[$id]);
        $output = $this->response[$id]->toString();
        $ret = event_buffer_write($buffer, $output);
        return $ret;
    }

    function read($buffer, $id) {
        $socket = $this->connections[$id];
        $header = "";
        while ($read = event_buffer_read($buffer, 512)) {
            $header .= $read;
        }
        $addr = stream_socket_get_name($socket, true);
        $addr = explode(":", $addr);
        $this->request[$id] = new Request($header, $addr[0], $addr[1]);
        $this->response[$id] = new Response($this->request[$id]);
//        $this->router->dispatch($this->request[$id], $this->response[$id]);
//        $output = $this->response[$id]->toString();
//        $ret = event_buffer_write($buffer, $output);
        $this->doRequest($buffer, $id);
    }

    function write($buffer, $id) {
        if (!isset($this->request[$id])) {
            return false;
        }
        $this->close($id);
    }

    function error($buffer, $error, $id) {
        event_buffer_disable($this->buffers[$id], EV_READ | EV_WRITE);
        event_buffer_free($this->buffers[$id]);
        fclose($this->connections[$id]);
        unset($this->buffers[$id], $this->connections[$id]);
        if (isset($this->request[$id])) {
            unset($this->request[$id]);
        }
        if (isset($this->response[$id])) {
            unset($this->response[$id]);
        }
    }

    function close($id) {
        event_buffer_disable($this->buffers[$id], EV_READ | EV_WRITE);
        event_buffer_free($this->buffers[$id]);
        fclose($this->connections[$id]);
        unset($this->buffers[$id], $this->connections[$id]);
        if (isset($this->request[$id])) {
            unset($this->request[$id]);
        }
        if (isset($this->response[$id])) {
            unset($this->response[$id]);
        }
    }

}

