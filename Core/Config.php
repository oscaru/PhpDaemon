<?php

namespace Core ;

class Config
{

    protected static $instance = null;
    protected static $config = array();

    private function __construct() {} // make this private so we can't instanciate

    
    public static function setArray($config){
        if(!empty($config)) foreach($config as $key => $val){
            self::set($key, $val);
        }
    }
    
    public static function set($key, $val)
    {
        self::$config[$key] = $val;
    }

    public static function get($key)
    {
        return self::$config[$key];
    }

}
