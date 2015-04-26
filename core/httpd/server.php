<?php

/**
 * 基于libevent的多进程服务器
 *
 * @author ye
 */

namespace http;

class Server {

    protected $ip = '0.0.0.0';
    protected $port = 80;
    protected $connections = array();
    protected $buffers = array();
    protected $server_socket = null;
    protected $event_base = null;
    protected $accept_handle = null;
    protected $error_handle = null;
    protected $read_handle = null;
    protected $write_handle = null;

    function __construct($port = 80, $ip = "0.0.0.0") {
        $this->ip = $ip;
        $this->port = $port;
        $this->connections = array();
        $this->buffers = array();
        $this->bindHandle();
    }

    function start() {
        $this->server_socket = stream_socket_server("tcp://{$this->ip}:{$this->port}", $errno, $errstr,STREAM_SERVER_BIND );
        $this->server_socket = stream_socket_server("tcp://{$this->ip}:{$this->port}", $errno, $errstr,STREAM_SERVER_BIND|STREAM_SERVER_LISTEN  );
        stream_set_blocking($this->server_socket, 0);
        $this->event_base = event_base_new();
        $event = event_new();
        event_set($event, $this->server_socket, EV_READ | EV_WRITE | EV_PERSIST, $this->accept_handle, $this->event_base);
        event_base_set($event, $this->event_base);
        event_add($event);
        event_base_loop($this->event_base);
    }

    function accept($socket, $flag, $base) {
        static $id = 0;
        $connection = stream_socket_accept($socket);
        if (!$connection) {
            for ($i = 0; $i < 10; ++$i) {
                $connection = stream_socket_accept($socket);
                if ($connection) {
                    break;
                }
            }
        }
        stream_set_blocking($connection, 0);
        $id += 1;
//        $buffer = event_buffer_new($connection, $this->read_handle, $this->write_handle, $this->error_handle, $id);
        $buffer = event_buffer_new($connection, $this->read_handle, $this->write_handle, $this->error_handle, $id);
        event_buffer_base_set($buffer, $base);
        event_buffer_timeout_set($buffer, 30, 30);
        event_buffer_watermark_set($buffer, EV_READ | EV_WRITE, 0, 0xffffff);
        event_buffer_priority_set($buffer, 10);
        event_buffer_enable($buffer, EV_READ | EV_WRITE | EV_PERSIST);

        $this->connections[$id] = $connection;
        $this->buffers[$id] = $buffer;
    }

    function error($buffer, $error, $id) {
        event_buffer_disable($this->buffers[$id], EV_READ | EV_WRITE);
        event_buffer_free($this->buffers[$id]);
        fclose($this->connections[$id]);
        unset($this->buffers[$id], $this->connections[$id]);
    }

    function close($id) {
        event_buffer_disable($this->buffers[$id], EV_READ | EV_WRITE);
        event_buffer_free($this->buffers[$id]);
        fclose($this->connections[$id]);
        unset($this->buffers[$id], $this->connections[$id]);
    }

    function read($buffer, $id) {
        $head = '';
        while ($read = event_buffer_read($buffer, 512)) {
            $head .= $read;
        }
    }

    function write($buffer, $id) {
        
    }

    function bindHandle() {
        $self = $this;
        $this->accept_handle = function($socket, $flag, $base) use ($self) {
                    $self->accept($socket, $flag, $base);
                };
        $this->error_handle = function($buffer, $error, $id) use ($self) {
                    $self->error($buffer, $error, $id);
                };
        $this->write_handle = function($buffer, $id) use ($self) {
                    $self->write($buffer, $id);
                };
        $this->read_handle = function($buffer, $id) use ($self) {
                    $self->read($buffer, $id);
                };
    }

}