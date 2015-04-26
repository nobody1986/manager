<?php

/**
 * 响应类封装
 *
 * @author ye
 */

namespace core\httpd;

class Response {

    protected $_caller;
    protected $_request;
    protected $_responseHandler;
    protected $_code = 200;
    protected $_protocol = 'HTTP/1.1';
    protected $_output = '';
    protected $_cookies = array();
    protected $_response = array(
        'Server' => 'Egaplay',
        'Content-Type' => 'text/html',
    );

    function __construct(Request $request) {
        try {
            $this->_request = $request;
        } catch (Exception $e) {
            $output = '';
            $This->_code = 500;
        }
    }

    function redirect($url) {
        $this->_code = 301;
        $this->_response['Location'] = $url;
    }

    function notFound($content = '') {
        $this->_code = 404;
        $this->write($content);
    }

    function setHeader($head) {
        $this->_response = array_merge($this->_response, $head);
    }

    function getMimeType($path) {
        global $mime_types;
        $pos = strrpos($path, '.');
        $extend = substr($path, $pos + 1);
        if (isset($mime_types[$extend])) {
            return $mime_types[$extend];
        }
    }

    function setMimeTypeByPath($path) {
        $mime = $this->getMimeType($path);
        if (!empty($mime)) {
            $this->_response['Content-Type'] = $mime;
        }
    }

    function setCode($code) {
        $this->_code = $code;
    }

    function toString() {
        try {
            $output = ''; //call_user_func($this->_caller);
        } catch (Exception $e) {
            $output = '';
            $This->_code = 500;
        }
        $output = $this->_output . $output;
        $this->_response['Content_Length'] = strlen($output);
        $this->_response['Date'] = date('D, d M Y H:i:s e');
        $header_array = array();
        foreach ($this->_response as $key => $value) {
            $header_array [] = "{$key}: {$value}";
        }
        $header = "{$this->_protocol} {$this->_code} OK\r\n";
        $header .= implode("\r\n", $header_array);
        $header .= "\r\n";
        foreach ($this->_cookies as $key => $value) {
            $cookie_str = array();
            if (!empty($value['expires'])) {
                $value['expire'] = date('D, d M Y H:i:s e', $value['expire']);
                $cookie_str [] = "expires={$value['expire']}";
            }
            if (!empty($value['path'])) {
                $cookie_str [] = "path={$value['path']}";
            }
            if (!empty($value['domain'])) {
                $cookie_str [] = "domain={$value['domain']}";
            }
            $header .= sprintf("Set-Cookie: %s=%s; %s\r\n", $key, $value['value'], implode("; ", $cookie_str));
        }
        $header .= "\r\n\r\n";
        return $header . $output;
    }

    function write($str) {
        $this->_output .= $str;
    }

    /**
     * Set-Cookie:H_PS_PSSID=1437_2976_2980_3090_3225; path=/; domain=.baidu.com
     * Set-Cookie:toolid=4tQjrluOK1x6u5%2b1KHZ%2fGEyOutgEiuop; domain=chinaz.com; expires=Sat, 07-Sep-2013 08:43:53 GMT; path=/
     */
    function setCookie($key, $value, $expire = null, $path = null, $domain = null) {
        $this->_cookies[$key] = array(
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
        );
    }

}