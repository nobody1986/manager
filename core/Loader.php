<?php
namespace Core;

/**
 * Description of Loader
 *
 * @author snow
 */
class Loader {
    protected  static $_loadeds = array();
    static function autoLoad(){
        spl_autoload_register(function($classname){
            if(empty(self::$_loadeds[$classname])){
                $classname = str_replace('\\', DIRECTORY_SEPARATOR, $classname);
                $classpath = MANAGER_ROOT . $classname;
                if(file_exists($classpath)){
                    require($classpath);
                    self::$_loadeds[$classname] = true;
                }
            }
        });
    }
}
