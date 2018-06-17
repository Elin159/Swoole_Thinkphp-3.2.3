<?php 
 namespace Job;
use Org\NewJob\MiddleLayer;
class CsController extends MiddleLayer {
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * 处理逻辑
    */
    public function handel()
    {
        file_put_contents('111.txt',date('Y-m-d H:i:s'));
    }
}