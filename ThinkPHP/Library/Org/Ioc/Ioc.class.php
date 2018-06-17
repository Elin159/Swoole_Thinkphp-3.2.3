<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2017/8/17
 * Time: 14:51
 */

namespace Org\Ioc;

class Ioc {
    /**
     * 获取对象
     * @param $className
     * @return object
     */
    public static function getObj($className) {
        //获取构造函数方法参数
        $param = self::getMethodParams($className);
        //返回实例构造函数的方法
        return (new \ReflectionClass($className))->newInstanceArgs($param);
    }

    /**
     * 实例对象
     * @param $className 类名称
     * @param $methodName 方法名
     * @param array $params 参数
     */
    public static function make($className, $methodName, $params = []) {
        //获取放入构造函数参数后的对象
        $obj = self::getObj($className);
        //获取对象中实例的方法参数
        $paramArr = self::getMethodParams($className, $methodName);
        dump($paramArr);die();
        //实例对象中实例的方法切加入参数
//        $obj->$methodName(...array_merge($params, $paramArr));
    }

    /**
     * 获取对象方法参数
     * @param $className 类名称
     * @param string $methodName 方法名称
     * @return array
     */
    public static function getMethodParams($className, $methodName = "__construct") {
        $obj = new \ReflectionClass($className);
        //设置参数容器
        $paramsArr = [];

        if($obj->hasMethod($methodName)) {
            //获取对象方法
            $function = $obj->getMethod($methodName);
            //获取对象上的参数
            $params = $function->getParameters();
            //判断方法上是否有参数
            if(count($params) > 0) {
                foreach($params as $param) {
                    //如果是一个类参数的话
                    if($paramClass = $param->getClass()) {
                        $paramClassName = $paramClass->getName();

                        $subParams = self::getMethodParams($paramClassName);
                        $paramsArr[] = (new \ReflectionClass($paramClassName))->newInstanceArgs($subParams);
                    }
                }
            }
        }

        return $paramsArr;
    }
}