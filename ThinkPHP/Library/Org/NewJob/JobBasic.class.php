<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2018/3/26
 * Time: 21:38
 */

namespace Org\NewJob;

interface JobBasic {
    //将需要执行的对象组装
    public function assemble($class_obj, $listen_key = 'default');
    //系统延时多少秒开始执行
    public function delay_time($time = 0);
    //失败多少次后移除出队列
    public function fail_num($num = 1);
    //外部运行队列方法
    public function run($listen_key = 'default');
    //执行方法
    public function handel();
}