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
class Toml {

    static function isNewline($toml, $len, &$index) {
        return $toml[$index] == '\n' || ($toml[$index] == '\r' && $toml[$index] == '\n' );
    }

    static function isSpace($toml, $len, &$index) {
        return $toml[$index] == ' ' || ($toml[$index] == '\t' );
    }

    static function parseInlineTable($toml, $len, &$index) {
        
    }

    static function parseTable($toml, $len, &$index) {
        
    }

    static function parseTableArray($toml, $len, &$index) {
        
    }

    static function parseInlineArray($toml, $len, &$index) {
        for ($i = $index; $i < $len; ++$i) {
            if ($toml[$i] == '=') {
                
            }
            elseif ($toml[$i] == ' ') {
                continue;
            }else{
                break;
            }
        }
    }

    static function parseHash($toml, $len, &$index) {
        $key = self::parseKey($toml, $len, $index);
        for ($i = $index; $i < $len; ++$i) {
            if ($toml[$i] == '=') {
                
            }
            elseif ($toml[$i] == ' ') {
                continue;
            }else{
                break;
            }
        }
        $index = $i;
        $val = self::parseItem($toml, $len, $index);
        return [$key => $val];
    }

    static function parseItem($toml, $len, &$index) {
        if ($toml[$index] == '{') {
            return self::parseInlineTable($toml, $len, $index);
        } if ($toml[$index] == '[') {
            return self::parseInlineArray($toml, $len, $index);
        }
        return self::parseAtom($toml, $len, $index);
    }

    static function parseKey($toml, $len, &$index) {
        if ($toml[$index] == '\'' || $toml[$index] == '"') {
            return self::parseString($toml, $len, $index);
        }
        for ($i = $index; $i < $len; ++$i) {
            if (($toml[$i] >= 'a' && $toml[$i] <= 'z') ||
                    ($toml[$i] >= 'A' && $toml[$i] <= 'A') ||
                    ($toml[$i] >= '0' && $toml[$i] <= '9')) {
                
            } else {
                $ret = substr($toml, $index, $i - $index);
                $index = $i - 1;
                return $ret;
            }
        }
    }

    static function parseAtom($toml, $len, &$index) {
        if ($toml[$index] == '\'' || $toml[$index] == '"') {
            return self::parseString($toml, $len, $index);
        }
        if (toml[$index] >= '0' && $toml[$index] <= '9') {
            return self::parseNumber($toml, $len, $index);
        }
        throw new Exception();
    }

    static function parseString($toml, $len, &$index) {
        $quote = $toml[$index];
        for ($i = $index + 1; $i < $len; ++$i) {
            if ($toml[$i] == $quote && $toml[$i - 1] != '\\') {
                $ret = substr($toml, $index + 1, $i - $index - 1);
                $index = $i - 1;
                return $ret;
            }
        }
    }

    static function parseNumber($toml, $len, &$index) {
        for ($i = $index; $i < $len; ++$i) {
            if (!($toml[$i] >= '0' && $toml[$i] <= '9')) {
                $ret = substr($toml, $index, $i - $index);
                $index = $i - 1;
                return $ret;
            }
        }
        $ret = substr($toml, $index, $i - $index);
        $index = $i - 1;
        return $ret;
    }

    static function parse($toml) {
        $len = strlen($toml);
        for ($i = 0; $i < $len; ++$i) {
            
        }
    }

}
