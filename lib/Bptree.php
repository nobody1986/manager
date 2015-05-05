<?php

namespace lib;

/**
 * Description of Toml
 *
 * doc = unit +
 * unit = table_define \nhash\n*
 * table_define =  [ tablepath ] | [[ tablepath ]]
 * hash = key = item\n  
 * item = list | map |atom
 * list = [(atom | list | map),]
 * map = {hash,}
 * atom = number | string 
 * 
 * @author snow
 */
class Bptree {

    protected $root;
    protected $m = 5;
    
    
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
}
