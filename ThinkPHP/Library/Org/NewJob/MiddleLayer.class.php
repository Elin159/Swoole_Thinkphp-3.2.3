<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2018/3/27
 * Time: 14:45
 */

namespace Org\NewJob;

use OtherSdk\RedisProvider;

abstract class MiddleLayer extends Execute implements JobBasic {

    protected     $time     = 0; //延迟多少秒后执行
    protected   $attempts   = 0; //尝试多少次后放弃执行 0为不放弃

    /**
     * 组装数据压进队列
     * @param $class_obj 运行的类
     * @param string $listen_key 划分给哪个有序集合
     * @return array
     */
    public function assemble($class_obj, $listen_key = 'default')
    {
        // TODO: Implement assemble() method.
        $message = [
            'job'       => $class_obj,
            'attempts'  => $this->attempts,
            'delay'     => $this->time,
            'add_time'  => time(),
            'listKey'   => $listen_key,
            'run'       => 0
        ];

        return $message;
    }

    /**
     * 延时执行时间
     * @param int $time
     */
    public function delay_time($time = 0)
    {
        // TODO: Implement delay_time() method.
        $this->time = $time;
    }

    /**
     * 尝试多少次后放弃执行 0为不放弃
     * @param int $num
     */
    public function fail_num($num = 1)
    {
        // TODO: Implement fail_num() method.
        $this->attempts = $num;
    }

    /**
     * 外部循环执行方法
     * @param string $listen_key
     */
    public function run($listen_key = 'default')
    {
        // TODO: Implement run() method.
        $i = 0;
        RedisProvider::pConnect();
        while(1) {
            $i++;
            if($i%500) {
                ob_flush();
                sleep(3);
            }
            $this->realize($listen_key);

            if($i > 10000) {
                RedisProvider::close();
                RedisProvider::pConnect();
                $i = 1;
            }
        }
    }

    /**
     * 核心执行方法
     * @return mixed
     */
    abstract public function handel();

}