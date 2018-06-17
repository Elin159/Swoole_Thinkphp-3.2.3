<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2018/6/4
 * Time: 17:06
 */

namespace OtherSdk;


trait SingerProvider {
    static $class;
    public static function __callStatic($name, $arguments)
    {
        self::singer();
        if(!is_callable( [self::$class, $name] )) {
            throw new Exception('无法找到 "'.$name.'" 这个方法, 请检查该方法是否存在');
        }

        // TODO: Implement __callStatic() method.
        return call_user_func_array(array(self::$class, $name), $arguments);
    }

    private static function singer() {
        if(is_null(self::$class)) {
            self::$class = new static();
        }
        return self::$class;
    }
}