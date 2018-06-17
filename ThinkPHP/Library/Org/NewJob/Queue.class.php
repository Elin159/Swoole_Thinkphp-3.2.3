<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2018/3/26
 * Time: 21:17
 */

namespace Org\NewJob;

interface Queue {
    //把内容压入指定队列
    public function press($listen_key = 'default', $message, $id);
    //删除指定队列中某条内容
    public function flush($listen_key = 'default', $message);
    //获取指定队列中的某条内容
    public function gain($listen_key = 'default', $id);
    //内部遍历队列(进行遍历)
    public function realize($list_key = 'default');
}