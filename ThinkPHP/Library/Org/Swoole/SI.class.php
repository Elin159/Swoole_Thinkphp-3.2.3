<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2018/6/10
 * Time: 12:33
 */
namespace Org\Swoole;

interface SI {
    public function setValue($host = '127.0.0.1', $port=9501);
    public function run();

}