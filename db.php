<?php
if (!defined('MONK_VERSION')) exit('Access is no allowed.');

class db {

    private static $maps = array();

    protected static function validator($tables,$data){
        $_data = array();
        $table_array = explode(',',$tables);
        $table_map_finish = array();
        if(is_array($table_array)){
            foreach($table_array as $table){
                $table_map = self::getMap($table);
                foreach($table_map['field'] as $key=>$type){
                    $table_map_finish[$table_map['table'].'.'.$key] = $type;
                }
            }
        }else{
            $table_map = self::getMap($tables);
            $table_map_finish =  $table_map['field'];
        }

        $_data = array_intersect_key($data, $table_map_finish);
        foreach($_data as $key => $value){
            if(is_array($value)){
                foreach($value as $k=>$v)
                    $value[$k] = self::validateAtrribute($v, $table_map_finish[$key]);
                $_data[$key] = $value;
            }else
                $_data[$key] = self::validateAtrribute($value, $table_map_finish[$key]);
        }
        
        return $_data;
    }

    private static function validateAtrribute($value, $typeName){
        $func = validator::$function_array[$typeName];
        return validator::$func($value);
    }

    private static function getMap($name,$driver = 'mysql'){
        if(!isset(self::$maps[$name]) || empty(self::$maps[$name])){
            $app_map = MONK_APP.MONK::getConfig('app').DS.'conf'.DS.MONK::getConfig('map_path').DS.$driver.DS.$name.'.php';
            if(is_file($app_map))
                self::$maps[$name] = include($app_map);
            else
                throw new Exception('映射文件路径`'.$app_map.'`不存在',CORE_MODEL_EC_MAP_FILE_CONNOT_FOUND);
        }
            
        return self::$maps[$name];
    }
}