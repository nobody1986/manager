<?php
namespace core;
/**
 * 多进程服务器
 *
 * @author ye
 */

class MultiProcessServer {

    const INIT_CHILD_NUM = 10;

    protected $child_num = 0;
    protected $is_parent = true;
    protected $children = array();
    protected $children_load = array();
    protected $children_queue = array();
    protected $die = false;
    protected $group = null;
    protected $user = null;
    protected $gid = null;
    protected $uid = null;
    protected $msg_queue = null;
    protected $name = null;

    function __construct($num = 0,$user = null,$group = null) {
        $this->child_num = $num == 0 ? self::INIT_CHILD_NUM : $num;
        $this->children = array();
        $this->children_queue = array();
        $this->children_load = array('manager');
        $this->die = false;
        $this->is_parent = true;
        $this->msg_queue= msg_get_queue(ftok(dirname(__FILE__),  basename(__FILE__)));
        if(!empty($user)){
            $this->user = $user;
            $uinfo = posix_getpwnam($user);
            $this->uid = $uinfo['uid'];
            $this->gid = $uinfo['gid'];
        }
        if(!empty($group)){
            $this->group = $group;
            $ginfo = posix_getgrnam($group);
            $this->gid = $ginfo['gid'];
        }
    }
    
    function setName($name){
        $this->name = $name;
    }

    function start() {
        declare(ticks = 1);
        //deamon process
        $pid = pcntl_fork();
        if ($pid == -1) {
            die("fork failed!\n");
        } elseif ($pid > 0) {
            exit();
        }
        $sid = posix_setsid();
        if ($sid == -1) {
            die("setsid failed!\n");
        }
        chdir('/');
        umask(0);
        if ($pid == -1) {
            die("fork failed!\n");
        } elseif ($pid > 0) {
            exit();
        }
        $this->bindSig();
        //fork the children
        $this->createChildren($this->child_num);
        while (!$this->die) {
            if ($this->is_parent) {
                $this->parentRun();
            } else {
                $this->childRun();
            }
        }
    }

    function parentRun() {
        usleep(800000);
        $this->checkChildStatus();
    }

    function childRun() {
        $this->job();
    }

    function job() {
        if($this->name == 'manager'){
            $runner = new Manager($this);
        }else{
//            $runner = new \app\$this->name();
        }
        $runner->run();
    }
    
    function childInit() {
        
    }

    function createChildren($num = self::INIT_CHILD_NUM) {
        foreach ($this->children_load as $loadname) {
            $pid = pcntl_fork();
            if ($pid == 0) {
                $this->is_parent = false;
                $this->setName($loadname);
                $this->children = array();
                $this->childInit();
                if(!empty($this->gid)){
                    posix_setgid($this->gid);
                }
                if(!empty($this->uid)){
                    posix_setuid($this->uid);
                }
                break;
            } else {
                $this->children [] = $pid;
            }
        }
    }

    function sigHandler($signo) {

        if ($this->is_parent) {
            switch ($signo) {
                case SIGTERM:
                    // 处理SIGTERM信号
                    $this->log("parent [".  posix_getpid()."] recv SIGTERM");
                    foreach ($this->children as $child) {
                        posix_kill($child, SIGTERM);
                        pcntl_waitpid($child, $status);
                    }
                    $this->parentExit();
                    exit();
                    break;
                case SIGHUP:
                    //处理SIGHUP信号
                    $this->log("parent [".  posix_getpid()."] recv SIGHUP");
                    foreach ($this->children as $child) {
                        posix_kill($child, SIGHUP);
                        pcntl_waitpid($child, $status);
                    }
                    $this->parentExit();
                    $this->die = true;
                    break;
                case SIGUSR1:
                    $this->log("parent [".  posix_getpid()."] recv SIGUSR1");
                    foreach ($this->children as $child) {
                        posix_kill($child, SIGUSR1);
                        pcntl_waitpid($child, $status);
                    }
                    $this->children = array();
                    break;
                default:
                // 处理所有其他信号
            }
        } else {
            switch ($signo) {
                case SIGTERM:
                    // 处理SIGTERM信号
                    $this->log("child [".  posix_getpid()."] recv SIGTERM");
                    $this->childExit();
                    exit();
                    break;
                case SIGHUP:
                    //处理SIGHUP信号
                    $this->log("child [".  posix_getpid()."] recv SIGHUP");
                    $this->die = true;
                    $this->childExit();
                    break;
                case SIGUSR1:
                    $this->log("child [".  posix_getpid()."] recv SIGUSR1");
                    $this->die = true;
                    $this->childExit();
                    break;
                default:
                // 处理所有其他信号
            }
        }
    }

    function childExit(){}
    function parentExit(){}
    
    function checkChildStatus() {
        foreach ($this->children as $k => $child) {
            $ret = pcntl_waitpid($child, $status, WNOHANG );
            if ($ret != -1) {
                if ($ret > 0) {
                    unset($this->children[$k]);
                }
            }
        }
        $current_children_number = sizeof($this->children);
        //children too few
        if ($current_children_number < $this->child_num) {
            $this->createChildren($this->child_num - $current_children_number);
        }
    }

    function bindSig() {
        pcntl_signal(SIGTERM, array($this, "sigHandler"));
        pcntl_signal(SIGHUP, array($this, "sigHandler"));
        pcntl_signal(SIGUSR1, array($this, "sigHandler"));
    }

    function log($str) {
//        file_put_contents("/home/snow/playground/mp.log", $str . "\n", FILE_APPEND);
    }

}


$server = new MultiProcessServer(2,"root","root");
$server->start();

