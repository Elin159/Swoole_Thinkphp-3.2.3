<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/19 0019
 * Time: 下午 15:22
 */
namespace Org\NewJob;

class addJoe {

    public $jobName;
    protected $listKey;

    private function __construct(MiddleLayer $jobs, $listKey = 'default') {
        $this->jobName = $jobs;
        $this->listKey = $listKey;
    }

    public function delay($time = 0) {
        $this->jobName->delay_time($time);
        return $this;
    }

    public function push() {
        $message = $this->jobName->assemble(serialize($this->jobName), $this->listKey); //拼装信息
        return $this->jobName->press($this->listKey, json_encode($message));//压入队列
    }

    public function onQueue($listKey = 'default') {
        $this->listKey = $listKey;
        return $this;
    }

    public static function Joe(MiddleLayer $jobs, $listKey = 'default') {
        return new static($jobs, $listKey);
    }
}