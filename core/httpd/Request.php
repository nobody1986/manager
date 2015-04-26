<?php

/**
 * 请求类封装
 *
 * @author ye
 */

namespace core\httpd;

class Request {

    /**
     * @string
     */
    protected $query_string;
    protected $_requestHandler;
    protected $_header;
    protected $_header_raw;
    protected $_cookies;
    protected $_ipaddr;
    protected $_port;

    function __construct($header, $ip, $port) {
        $this->_header_raw = explode("\r\n", $header);
        $this->_ipaddr = $ip;
        $this->_port = $port;
        $this->_cookies = array();
        $this->_header = array();
        foreach ($this->_header_raw as $key => $value) {
            if (strtolower($key) == 'cookie') {
                $value = explode(';', $value);
                foreach ($value as $l) {
                    $l = explode('=', $l);
                    $l[0] = trim($l[0]);
                    $this->_cookies[$l[0]] = trim($l[1]);
                }
            }
        }
        $this->_header = $this->pasrseHead();
    }

    function getCookie($key) {
        if (empty($this->_cookies[$key])) {
            return null;
        }
        return $this->_cookies[$key];
    }

    function getClientIp() {
        return $this->_header['client_ip'];
    }

    function getServerIp() {
        return '127.0.0.1';
    }

    function setRequestHandler($requestHandler) {
        $this->_requestHandler = $requestHandler;
    }

    function getPath() {
        return $this->_header['path'];
    }

    function get($key = null) {
        if(empty($key)){
            return $this->_header['get_params'];
        }else{
            return isset($this->_header['get_params'][$key]) ? $this->_header['get_params'][$key] : null;
        }
    }

    function post($key) {
        return isset($this->_header['post_params'][$key]) ? $this->_header['post_params'][$key] : null;
    }

    function pasrseHead() {
        $head = array();
        $head['client_ip'] = $this->_ipaddr;
        $head['client_port'] = $this->_port;
        if (empty($this->_header_raw)) {
            return false;
        }
        $method = $this->_header_raw[0];
        $m = substr($method, 0, 3);
        $m = strtolower($m);
        if ($m == "get" || $m == 'pos') {
            $method_split = explode(" ", $method);
            $head['method'] = strtolower($method_split[0]);
            $url = explode('?', $method_split[1]);
            if (!empty($url[1])) {
                $head['query_string'] = $url[1];
                $head['get_params'] = array();
                $args = explode('&', $url[1]);
                foreach ($args as $v) {
                    $v = explode('=', $v);
                    if (!empty($v[1])) {
                        $head['get_params'][$v[0]] = $v[1];
                    }
                }
            } else {
                $head['query_string'] = '';
                $head['get_params'] = array();
            }
            $head['path'] = $url[0];

            $head['protocol'] = trim($method_split[2]);
        }
        foreach ($this->_header_raw as $ln => $line) {
            $line = trim($line);
            if (empty($line) || $ln == 0) {
                continue;
            }
            $line_split = explode(": ", $line);
            if (isset($head[$line_split[0]])) {
                if (is_array($head[$line_split[0]])) {
                    $head[$line_split[0]] [] = $line_split[1];
                } else {
                    $head[$line_split[0]] = array($head[$line_split[0]], $line_split[1]);
                }
            } else {
                $head[$line_split[0]] = trim($line_split[1]);
            }
        }
        return $head;
    }

}