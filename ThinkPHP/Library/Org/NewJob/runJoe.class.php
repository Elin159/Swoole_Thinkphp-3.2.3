<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/19 0019
 * Time: 下午 15:36
 */

namespace Org\NewJob;
if (!IS_CLI)  die('The file can only be run in cli mode!');
class runJoe extends MiddleLayer {

    public $listKey;

    public function __construct($listKey = 'default')
    {
        $this->listKey = $listKey;
    }

    public function handel()
    {
        // TODO: Implement execute() method.
        $this->run($this->listKey);
    }
}