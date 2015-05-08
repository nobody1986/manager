<?php

namespace lib;

/**
 * 
 * 
 * @author snow
 */
class Bptree {

    protected $root;
    protected $m = 5;
    
    function __construct(){
        $this->root =  $this->mkNode();
    }

    function mkNode(){
        return ['keys' => [],'children' => [],'parent' => NULL ,'vals' => NULL];
    }

    function isRoot($node){
        return empty($node['parent']);
    }
    
    function isFull($node){
        if($this->isRoot($node)){
            return sizeof($node['children']) == $this->m?1:(sizeof($node['children']) <= 0?-1:0);
        }else{
            return sizeof($node['children']) == $this->m?1:(sizeof($node['children']) <= $this->m / 2?-1:0);
        }
    }

    function add($key,$value){
        $point =  $key[0];
        $node = &$this->root;
        while(!empty($node)){
            foreach($node['keys'] as $k => $v){
                if($point <= $v){}
            }
        }
    }
}
