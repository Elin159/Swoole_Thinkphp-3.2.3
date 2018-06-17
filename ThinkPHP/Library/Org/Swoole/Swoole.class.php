<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2018/6/10
 * Time: 12:37
 */
namespace Org\Swoole;

abstract class Swoole implements SI {

    use BaseSwoole;

    protected $_setting = array();

    public function __construct()
    {

        /**
         * 默认时区定义
         */
        date_default_timezone_set($this->timezone_identifier);

        /**
         * 设置错误报告模式
         */
        error_reporting($this->error_level);

        /**
         * 设置默认区域
         */
        setlocale(LC_ALL, "zh_CN.utf-8");

        /**
         * 检测 PDO_MYSQL
         */
        if (!extension_loaded('pdo_mysql')) {
            exit('PDO_MYSQL extension is not installed' . PHP_EOL);
        }
        /**
         * 检查exec 函数是否启用
         */
        if (!function_exists('exec')) {
            exit('exec function is disabled' . PHP_EOL);
        }
        /**
         * 检查命令 lsof 命令是否存在
         */
        exec("whereis lsof", $out);
        if ($out[0] == 'lsof:') {
            exit('lsof is not found' . PHP_EOL);
        }

        /**
         * 定义项目根目录&swoole-task pid
         */
        define('SWOOLE_PATH', $this->swoole_path);
        define('SWOOLE_TASK_PID_PATH', SWOOLE_PATH . $this->swoole_task_pid_path);
        define('SWOOLE_TASK_NAME_PRE', $this->swoole_task_name_pre);

        //定义默认参数
        $this->_setting = array(
            'host'  => property_exists($this, 'host') ? $this->host :'127.0.0.1',
            'port'  => property_exists($this, 'port') ? $this->port : 9501,
            'env'   => 'dev', //环境 dev|test|prod
            'process_name'  => SWOOLE_TASK_NAME_PRE,  //swoole 进程名称
            'worker_num'    => 6, //一般设置为服务器CPU数的1-4倍
            'task_worker_num'   => 6,  //task进程的数量
            'task_ipc_mode'     => 3,  //使用消息队列通信，并设置为争抢模式
            'task_max_request'  => 5000,  //task进程的最大任务数
            'daemonize'     => 1, //以守护进程执行
            'max_request'   => 20000,
            'dispatch_mode' => 2,
            'log_file'      => SWOOLE_PATH . DIRECTORY_SEPARATOR . 'Swoole' . date('Ymd') . '.log',  //日志
        );

        $this->base();
    }

    /**
     * 设置默认值
     * @param string $host
     * @param int $port
     */
    public function setValue($host = '127.0.0.1', $port = 9501)
    {
        // TODO: Implement setValue() method.
        $this->_setting['host'] = $host;
        $this->_setting['port'] = $port;
    }


    /**
     * 设置swoole进程名称
     * @param string $name swoole进程名称
     */
    protected function setProcessName($name) {
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($name);
        } else {
            if (function_exists('swoole_set_process_name')) {
                swoole_set_process_name($name);
            } else {
                trigger_error(__METHOD__ . " failed. require cli_set_process_title or swoole_set_process_name.");
            }
        }
    }

    /**
     * Server启动在主进程的主线程回调此函数
     * @param $serv
     */
    public function onStart($serv) {
        if (!$this->_setting['daemonize']) {
            echo 'Date:' . date('Y-m-d H:i:s') . "\t swoole_server master worker start\n";
        }
        $this->setProcessName($this->_setting['process_name'] . '-master');
        //记录进程id,脚本实现自动重启
        $pid = "{$serv->master_pid}\n{$serv->manager_pid}";
        file_put_contents(SWOOLE_TASK_PID_PATH, $pid);
    }

    /**
     * worker start 加载业务脚本常驻内存
     * @param $server
     * @param $workerId
     */
    public function onWorkerStart($serv, $workerId) {
        if ($workerId >= $this->_setting['worker_num']) {
            $this->setProcessName($this->_setting['process_name'] . '-task');
        } else {
            $this->setProcessName($this->_setting['process_name'] . '-event');
        }
    }

    /**
     * 监听连接进入事件
     * @param $serv
     * @param $fd
     */
    public function onConnect($serv, $fd) {
        if (!$this->_setting['daemonize']) {
            echo 'Date:' . date('Y-m-d H:i:s') . "\t swoole_server connect[" . $fd . "]\n";
        }
    }

    /**
     * worker 进程停止
     * @param $server
     * @param $workerId
     */
    public function onWorkerStop($serv, $workerId) {
        if (!$this->_setting['daemonize']) {
            echo 'Date:' . date('Y-m-d H:i:s') . "\t swoole_server[{$serv->setting['process_name']}  worker:{$workerId} shutdown\n";
        }
    }

    /**
     * 当管理进程启动时调用
     * @param $serv
     */
    public function onManagerStart($serv) {
        if (!$this->_setting['daemonize']) {
            echo 'Date:' . date('Y-m-d H:i:s') . "\t swoole_server manager worker start\n";
        }
        $this->setProcessName($this->_setting['process_name'] . '-manager');
    }

    /**
     * 此事件在Server结束时发生
     */
    public function onShutdown($serv) {
        if (file_exists(SWOOLE_TASK_PID_PATH)) {
            unlink(SWOOLE_TASK_PID_PATH);
        }
        if (!$this->_setting['daemonize']) {
            echo 'Date:' . date('Y-m-d H:i:s') . "\t swoole_server shutdown\n";
        }
    }

    /**
     * 监听数据发送事件
     * @param $serv
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive($serv, $fd, $from_id, $data) {
        if (!$this->_setting['daemonize']) {
            echo "Get Message From Client {$fd}:{$data}\n\n";
        }
        $result = json_decode($data, true);
        switch ($result['action']) {
            case 'reload':  //重启
                $serv->reload();
                break;
            case 'close':  //关闭
                $serv->shutdown();
                break;
            case 'status':  //状态
                $serv->send($fd, json_encode($serv->stats()));
                break;
            default:
				$serv->task($data);
                $serv->send($fd, 'hello world');
                break;
        }
    }

    /**
     * 监听连接Task事件
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     */
    public function onTask($serv, $task_id, $from_id, $data) {
//        $result = json_decode($data, true);
        //用TP处理各种逻辑
        $serv->finish($data);
    }

    /**
     * 监听连接Finish事件
     * @param $serv
     * @param $task_id
     * @param $data
     */
    public function onFinish($serv, $task_id, $data) {
        if (!$this->_setting['daemonize']) {
            echo "Task {$task_id} finish\n\n";
            echo "Result: {$data}\n\n";
        }
    }

    /**
     * 监听连接关闭事件
     * @param $serv
     * @param $fd
     */
    public function onClose($serv, $fd) {
        if (!$this->_setting['daemonize']) {
            echo 'Date:' . date('Y-m-d H:i:s') . "\t swoole_server close[" . $fd . "]\n";
        }
    }

    //udp方法

    /**
     * udp接收客户端数据方法，如果不设置，默认出发onReceive方法
     * @param $serv 服务器数据
     * @param $data 服务端接收客户端数据
     * @param $fd 客户端信息
     */
    public function onPacket($serv,$data,$fd) {
        $serv->sendTo($fd['address'], $fd['port'], "Server:$data");
    }

    //webSocket

    /**
     * @param $serv 服务端数据
     * @param $request 客户端提交数据
     */
    public function onOpen($serv, $request) {
        if (!$this->_setting['daemonize']) {
            echo 'Date:' . date('Y-m-d H:i:s') . "\t swoole_server master worker start\n";
        }
        $this->setProcessName($this->_setting['process_name'] . '-master');
        //记录进程id,脚本实现自动重启
        $pid = "{$serv->master_pid}\n{$serv->manager_pid}";
        file_put_contents(SWOOLE_TASK_PID_PATH, $pid);
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调这个函数
     * @param $serv 服务端信息
     * @param $cli 客户端信息
     * $cli->fd 客户端的socket_id,使用$ser->push推送数据时候需要用到
     * 如果是文本类型。编码格式必然是UTF-8,这是WebSocket协议规定的
     * $cli->data, 数据内容,可以是文本内容也可以是二进制数据,可以通过opcode的值来判断
     * $cli->opcode, WebSocket的OpCode类型
     * $cli->finish, 表示数据帧是否完整，
     */
    public function onMessage($serv, $cli) {
//        $serv->push($cli->fd, "this is server");
    }

    /**
     * 响应客户端数据
     * @param $request 客户端对服务器的请求数据
     * @param $response
     */
    public function onRequest($request, $response) {
        foreach ($this->_serv->connections as $fd) {
            $this->serv->push($fd, $request->get['message']);
        }
    }

    abstract public function run();
}