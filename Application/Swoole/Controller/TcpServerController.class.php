<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2018/6/10
 * Time: 12:17
 */

namespace Swoole\Controller;

use Org\Swoole\Swoole;

class TcpServerController extends Swoole {


    protected $_serv = null;
    protected $host = '0.0.0.0';

    public function __construct() {

        //swoole所在目录
        $this->swoole_path = __DIR__;
        parent::__construct();
    }

    /**
     * 运行swoole服务
     */
    public function run() {
        $this->_serv = new \swoole_server($this->_setting['host'], $this->_setting['port']);
        $this->_serv->set([
            'worker_num'        => $this->_setting['worker_num'],
            'task_worker_num'   => $this->_setting['task_worker_num'],
            'task_ipc_mode '    => $this->_setting['task_ipc_mode'],
            'task_max_request'  => $this->_setting['task_max_request'],
            'daemonize'         => $this->_setting['daemonize'],
            'max_request'       => $this->_setting['max_request'],
            'dispatch_mode'     => $this->_setting['dispatch_mode'],
            'log_file'          => $this->_setting['log_file']
        ]);
        $this->_serv->on('Start', array($this, 'onStart'));
        $this->_serv->on('Connect', array($this, 'onConnect'));
        $this->_serv->on('WorkerStart', array($this, 'onWorkerStart'));
//        $this->_serv->on('ManagerStart', array($this, 'onManagerStart'));
        $this->_serv->on('WorkerStop', array($this, 'onWorkerStop'));
        $this->_serv->on('Receive', array($this, 'onReceive'));
        $this->_serv->on('Task', array($this, 'onTask'));
        $this->_serv->on('Finish', array($this, 'onFinish'));
//        $this->_serv->on('Shutdown', array($this, 'onShutdown'));
        $this->_serv->on('Close', array($this, 'onClose'));
        $this->_serv->start();
    }
}