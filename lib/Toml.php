<?php

namespace lib;

/**
 * Description of Toml
 *
 * @author snow
 */
class Toml {

    static function parseInlineTable(){}
    static function parseTable(){}
    static function parseTableArray(){}
    static function parseInlineArray(){}
    static function parseHash(){}
    static function parseString(){}
    static function parseNumber(){}
    
    static function parse($toml) {
        $len = strlen($toml);
        for ($i = 0; $i < $len;  ++$i) {
            
        }
    }

}
