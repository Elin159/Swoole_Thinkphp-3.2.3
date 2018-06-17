<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2018/6/15
 * Time: 17:16
 */

namespace Swoole\Controller;

use Org\Swoole\BaseSwoole;
use Org\Swoole\Swoole;

class UdpServerController extends Swoole {
    use BaseSwoole;

    protected $_serv = null;
    protected $host = '0.0.0.0';

    public function __construct() {

        //swoole所在目录
        $this->swoole_path = __DIR__;
        $this->swoole_task_name_pre = 'swooleUdp';
        $this->swoole_task_pid_path = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'swooleUdp-task.pid';
        parent::__construct();
        return $this->base();
    }

    /**
     * 运行逻辑
     */
    public function run()
    {
        // TODO: Implement run() method.
        $this->_serv = new \swoole_server(
            $this->_setting['host'], $this->_setting['port'],
            SWOOLE_PROCESS, SWOOLE_SOCK_UDP
        );
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
        $this->_serv->on('Packet', array($this, 'onPacket'));
        $this->_serv->on('Task', array($this, 'onTask'));
        $this->_serv->on('Finish', array($this, 'onFinish'));
//        $this->_serv->on('Shutdown', array($this, 'onShutdown'));
        $this->_serv->on('Close', array($this, 'onClose'));
        $this->_serv->start();
    }

    /**
     * 监听数据发送事件
     * @param $serv 服务端数据
     * @param $fd 客户端数据
     * @param $from_id
     * @param $data 服务端接收客户端数据
     */
    public function onReceive($serv, $fd, $from_id, $data) {
        if (!$this->_setting['daemonize']) {
            echo "Get Message From Client {$fd}:{$data}\n\n";
        }
        $result = json_decode($data, true);
        $serv->send($fd, 12355588);
    }

    /**
     * udp监听接收数据
     * @param \Org\Swoole\服务器数据 $serv
     * @param \Org\Swoole\服务端接收客户端数据 $data
     * @param \Org\Swoole\客户端信息 $fd
     */
    public function onPacket($serv, $data, $fd)
    {
        $add_data = [
            'name' => uniqid('face_'),
            'is_on' => 'F'
        ];
        $save = $serv->face->data($add_data)->add();
        $serv->sendto($fd['address'],$fd['port'],'Server: '.json_encode($add_data));
    }
}