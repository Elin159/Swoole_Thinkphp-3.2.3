<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/24 0024
 * Time: 上午 9:41
 */

namespace Org\Factory;

use Org\Job\runJoe;
use Org\NewJob\runJoe as Job;

abstract class Architect implements Former{
    private $argv;
    private $makeName = ['queue'];

    /**
     * 启用对应功能
     * @param array $argv 参数
     */
    public function receive(array $argv)
    {
        // TODO: Implement receive() method.
        array_shift($argv);//弹出第一个
        array_shift($argv);//弹出第二个
        if(!count($argv)) {
            exit('Please enter the corresponding parameters');
        }
        if($argv['0'] == 'listen' && count($argv) === 1) {
            $this->listen();
            die();
        }
        $this->_issetColon($argv);
    }


    /**
     * 启动监听队列功能
     * @param string $name 监听名字
     */
    protected function listen($name = 'default') {
        $run = new Job($name);
        $run->handel();
    }


    /**
     * 内部receive逻辑方法
     * @param $argv 参数
     */
    private function _issetColon($argv) {
        $find = strpos(':',$argv[0]);
        if($find >= 7) {
            exit('Please enter listen: or make: parameters');
        }

        $head = explode(':', array_shift($argv));
        $this->argv = $argv;
        switch (strtolower($head['0'])) {
            case 'listen':
                $this->listen($head['1']);//启用监听
                break;
            case 'make':
                $this->make($head['1']);//造物
                break;
            default:
                exit('Please enter listen: or make: parameters');
                break;
        }
    }

    private function make($name)
    {
        if(!in_array($name, $this->makeName)) {
            exit('There is no such parameter');
        }

        switch($name) {
            case 'queue':
                file_put_contents(APP_PATH . "Job/".$this->argv[0].".class.php","<?php ".PHP_EOL." namespace Job;".PHP_EOL."use Org\NewJob\MiddleLayer;".PHP_EOL."class {$this->argv[0]} extends MiddleLayer {".PHP_EOL."    public function __construct()".PHP_EOL."    {".PHP_EOL."        parent::__construct();".PHP_EOL."    }".PHP_EOL.PHP_EOL."    /**".PHP_EOL."    * 处理逻辑".PHP_EOL."    */".PHP_EOL."    public function handel()".PHP_EOL."    {".PHP_EOL."    }".PHP_EOL."}");
                exit('Queue successfully created');
                break;
        }
    }
}