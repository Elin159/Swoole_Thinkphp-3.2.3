<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2018/6/10
 * Time: 11:12
 */

namespace Org\Swoole;

trait BaseSwoole {
    //swoole所在目录
    public $swoole_path = __DIR__;
    //存储swoole_task_pid路径
    public $swoole_task_pid_path = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'swoole-task.pid';
    //swoole_task名字进程
    public $swoole_task_name_pre = 'swooleServ';
    //错误报告模式级别
    public $error_level = 0;
    //脚本系统时区
    public $timezone_identifier = 'Asia/Shanghai';

    //可执行命令
    protected $cmds = [
        'start',
        'stop',
        'restart',
        'reload',
        'close',
        'status',
        'list',
    ];
    //短查询选项 -
    protected $shortopts = "dDh:p:n:u:";
    //长查询选项 --
    protected $longopts = [
        'help',
        'daemon',
        'nondaemon',
        'host:',
        'port:',
        'name:',
        'url:'
    ];

    protected $opts = '' , $argc = '' , $argv = '';

    /**
     * 端口绑定
     * @param $port 端口
     * @return array
     */
    public function portBind($port) {
        $ret = [];
        $cmd = "lsof -i :{$port}|awk '$1 != \"COMMAND\"  {print $1, $2, $9}'";
        exec($cmd, $out);
        if ($out) {
            foreach ($out as $v) {
                $a = explode(' ', $v);
                list($ip, $p) = explode(':', $a[2]);
                $ret[$a[1]] = [
                    'cmd' => $a[0],
                    'ip' => $ip,
                    'port' => $p,
                ];
            }
        }

        return $ret;
    }

    /**
     * 服务开启
     * @param $host 服务地址
     * @param $port 服务端口
     * @param $daemon
     * @param $name
     */
    public function servStart($host, $port, $daemon, $name) {
        echo "正在启动 swoole-task 服务" . PHP_EOL;
        if (!is_writable(dirname(SWOOLE_TASK_PID_PATH))) {
            exit("swoole-task-pid文件需要目录的写入权限:" . dirname(SWOOLE_TASK_PID_PATH) . PHP_EOL);
        }
        if (file_exists(SWOOLE_TASK_PID_PATH)) {
            $pid = explode("\n", file_get_contents(SWOOLE_TASK_PID_PATH));
            $cmd = "ps ax | awk '{ print $1 }' | grep -e \"^{$pid[0]}$\"";
            exec($cmd, $out);
            if (!empty($out)) {
                exit("swoole-task pid文件 " . SWOOLE_TASK_PID_PATH . " 存在，swoole-task 服务器已经启动，进程pid为:{$pid[0]}" . PHP_EOL);
            } else {
                echo "警告:swoole-task pid文件 " . SWOOLE_TASK_PID_PATH . " 存在，可能swoole-task服务上次异常退出(非守护模式ctrl+c终止造成是最大可能)" . PHP_EOL;
                unlink(SWOOLE_TASK_PID_PATH);
            }
        }
        $bind = $this->portBind($port);
        if ($bind) {
            foreach ($bind as $k => $v) {
                if ($v['ip'] == '*' || $v['ip'] == $host) {
                    exit("端口已经被占用 {$host}:$port, 占用端口进程ID {$k}" . PHP_EOL);
                }
            }
        }
        unset($_SERVER['argv']);
        $_SERVER['argc'] = 0;
        echo "启动 swoole-task 服务成功" . PHP_EOL;

        $this->setValue($host, $port);
        $this->run();
        //确保服务器启动后swoole-task-pid文件必须生成
//        if (!empty(portBind($port)) && !file_exists(SWOOLE_TASK_PID_PATH)) {
//            exit("swoole-task pid文件生成失败( " . SWOOLE_TASK_PID_PATH . ") ,请手动关闭当前启动的swoole-task服务检查原因" . PHP_EOL);
//        }
    }

    /**
     * 服务停止
     * @param $host 服务地址
     * @param $port 服务端口
     * @param bool $isRestart 是否重新启动
     */
    public function servStop($host, $port, $isRestart = false) {
        echo "正在停止 swoole-task 服务" . PHP_EOL;
        if (!file_exists(SWOOLE_TASK_PID_PATH)) {
            exit('swoole-task-pid文件:' . SWOOLE_TASK_PID_PATH . '不存在' . PHP_EOL);
        }
        $pid = explode("\n", file_get_contents(SWOOLE_TASK_PID_PATH));
        $bind = $this->portBind($port);
        if (empty($bind) || !isset($bind[$pid[0]])) {
            exit("指定端口占用进程不存在 port:{$port}, pid:{$pid[0]}" . PHP_EOL);
        }
        $cmd = "kill {$pid[0]}";
        exec($cmd);
        do {
            $out = [];
            $c = "ps ax | awk '{ print $1 }' | grep -e \"^{$pid[0]}$\"";
            exec($c, $out);
            if (empty($out)) {
                break;
            }
        } while (true);
        //确保停止服务后swoole-task-pid文件被删除
        if (file_exists(SWOOLE_TASK_PID_PATH)) {
            unlink(SWOOLE_TASK_PID_PATH);
        }
        $msg = "执行命令 {$cmd} 成功，端口 {$host}:{$port} 进程结束" . PHP_EOL;
        if ($isRestart) {
            echo $msg;
        } else {
            exit($msg);
        }
    }

    /**
     * 服务重启
     * @param $host 服务地址
     * @param $port 服务端口
     * @param bool $isRestart 是否重启
     */
    public function servReload($host, $port, $isRestart = false) {
        echo "正在平滑重启 swoole-task 服务" . PHP_EOL;
        try {
            $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
            $ret = $client->connect($host, $port);
            if (empty($ret)) {
                exit("{$host}:{$port} swoole-task服务不存在或者已经关闭" . PHP_EOL);
            } else {
                $client->send(json_encode(array('action' => 'reload')));
            }
            $msg = "执行命令reload成功，端口 {$host}:{$port} 进程重启" . PHP_EOL;
            if ($isRestart) {
                echo $msg;
            } else {
                exit($msg);
            }
        } catch (Exception $e) {
            exit($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * 服务关闭
     * @param $host 服务地址
     * @param $port 服务端口
     * @param bool $isRestart 是否重启
     */
    public function servClose($host, $port, $isRestart = false) {
        echo "正在关闭 swoole-task 服务" . PHP_EOL;
        try {
            $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
            $ret = $client->connect($host, $port);
            if (empty($ret)) {
                exit("{$host}:{$port} swoole-task服务不存在或者已经关闭" . PHP_EOL);
            } else {
                $client->send(json_encode(array('action' => 'close')));
            }
            //确保停止服务后swoole-task-pid文件被删除
            if (file_exists(SWOOLE_TASK_PID_PATH)) {
                unlink(SWOOLE_TASK_PID_PATH);
            }
            $msg = "执行命令close成功，端口 {$host}:{$port} 进程结束" . PHP_EOL;
            if ($isRestart) {
                echo $msg;
            } else {
                exit($msg);
            }
        } catch (\Exception $e) {
            exit($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * 服务状态
     * @param $host 服务地址
     * @param $port 服务端口
     */
    public function servStatus($host, $port) {
        echo "swoole-task {$host}:{$port} 运行状态" . PHP_EOL;
        $pid = explode("\n", file_get_contents(SWOOLE_TASK_PID_PATH));
        $bind = $this->portBind($port);
        if (empty($bind) || !isset($bind[$pid[0]])) {
            exit("指定端口占用进程不存在 port:{$port}, pid:{$pid[0]}" . PHP_EOL);
        }
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $ret = $client->connect($host, $port);
        if (empty($ret)) {
            exit("{$host}:{$port} swoole-task服务不存在或者已经停止" . PHP_EOL);
        } else {
            $client->send(json_encode(array('action' => 'status')));
            $out = $client->recv();
            $a = json_decode($out);
            $b = array(
                'start_time' => '服务器启动的时间',
                'connection_num' => '当前连接的数量',
                'accept_count' => '接受的连接数量',
                'close_count' => '关闭的连接数量',
                'tasking_num' => '当前正在排队的任务数',
                'request_count' => '请求的连接数量',
                'worker_request_count' => 'worker连接数量',
                'task_process_num' => '任务进程数量'
            );
            foreach ($a as $k1 => $v1) {
                if ($k1 == 'start_time') {
                    $v1 = date("Y-m-d H:i:s", $v1);
                }
                echo $b[$k1] . ":\t$v1" . PHP_EOL;
            }
        }
        exit();
    }

    /**
     * 服务列表
     */
    public function servList() {
        echo "本机运行的swoole-task服务进程" . PHP_EOL;
        $cmd = "ps aux|grep " . SWOOLE_TASK_NAME_PRE . "|grep -v grep|awk '{print $1, $2, $6, $8, $9, $11}'";
        exec($cmd, $out);
        if (empty($out)) {
            exit("没有发现正在运行的swoole-task服务" . PHP_EOL);
        }
        echo "USER PID RSS(kb) STAT START COMMAND" . PHP_EOL;
        foreach ($out as $v) {
            echo $v . PHP_EOL;
        }
        exit();
    }

    /**
     * 基础命令，入口命令
     */
    final protected function base() {
//获取cli 命令
        $this->opts = getopt($this->shortopts, $this->longopts);
//命令检查
        $this->argc = $_SERVER['argc'];
        $this->argv = $_SERVER['argv'];

        if (isset($this->opts['help']) || $this->argc < 2) {
            echo $this->help();
            exit;
        }
//参数检查
        $this->checkOptions();

//监听ip 127.0.0.1，空读取配置文件
        $host = $this->listenHost($this->opts);
//监听端口，9501 读取配置文件
        $port = $this->listenPort($this->opts);
//进程名称 没有默认为 SWOOLE_TASK_NAME_PRE;
        $name = $this->listenName($this->opts);
//是否守护进程 -1 读取配置文件（暂时没使用）
        $isdaemon = -1;
        if (isset($this->opts['D']) || isset($this->opts['nondaemon'])) {
            $isdaemon = 0;
        }
        if (isset($this->opts['d']) || isset($this->opts['daemon'])) {
            $isdaemon = 1;
        }

        $cmd = $this->argv[$this->argc - 1];
        if (!in_array($cmd, $this->cmds)) {
            exit("输入命令有误 : {$cmd}, 请查看帮助文档\n");
        }

        switch ($cmd) {
            case "start"://启动swoole-task服务
                return $this->servStart($host, $port, $isdaemon, $name);
                break;
            case "stop"://强制停止swoole-task服务
                if (empty($port)) {
                    exit("停止swoole-task服务必须指定port" . PHP_EOL);
                }
                return $this->servStop($host, $port);
                break;
            case "close"://关闭swoole-task服务
                if (empty($port)) {
                    exit("停止swoole-task服务必须指定port" . PHP_EOL);
                }
                return $this->servClose($host, $port);
                break;
            case "restart"://强制重启swoole-task服务
                if (empty($port)) {
                    exit("重启swoole-task服务必须指定port" . PHP_EOL);
                }
                echo "重启swoole-task服务" . PHP_EOL;
                $this->servStop($host, $port, true);
                return $this->servStart($host, $port, $isdaemon, $name);
                break;
            case "reload"://平滑重启swoole-task服务
                if (empty($port)) {
                    exit("平滑重启swoole-task服务必须指定port" . PHP_EOL);
                }
                echo "平滑重启swoole-task服务" . PHP_EOL;
                return $this->servReload($host, $port, true);
                break;
            case "status"://查看swoole-task服务状态
                if (empty($host)) {
                    $host = '127.0.0.1';
                }
                if (empty($port)) {
                    exit("查看swoole-task服务必须指定port(host不指定默认使用127.0.0.1)" . PHP_EOL);
                }
                return $this->servStatus($host, $port);
                break;
            case "list"://查看swoole-task服务进程列表
                return $this->servList();
                break;
        }
    }

    /**
     * 检查cli 输入的属性及参数
     */
    private function checkOptions() {
        foreach ($this->opts as $k => $v) {
            if (($k == 'h' || $k == 'host')) {
                if (empty($v)) {
                    exit("参数 -h --host 必须指定值\n");
                }
            }
            if (($k == 'p' || $k == 'port')) {
                if (empty($v)) {
                    exit("参数 -p --port 必须指定值\n");
                }
            }
            if (($k == 'n' || $k == 'name')) {
                if (empty($v)) {
                    exit("参数 -n --name 必须指定值\n");
                }
            }
        }
    }

    /**
     * 监听主机地址
     * @param $opts
     * @return mixed
     */
    private function listenHost($opts) {
        $host = property_exists($this, 'host') ? $this->host :'127.0.0.1';
        if (!empty($opts['h'])) {
            $host = $opts['h'];
            if (!filter_var($host, FILTER_VALIDATE_IP)) {
                exit("输入host有误:{$host}");
            }
        }
        if (!empty($opts['host'])) {
            $host = $opts['host'];
            if (!filter_var($host, FILTER_VALIDATE_IP)) {
                exit("输入host有误:{$host}");
            }
        }

        return $host;
    }

    /**
     * 监听端口
     * @param $opts cmd传输上来的值
     * @return int
     */
    private function listenPort($opts) {
        $port = property_exists($this, 'port') ? $this->port : 9501;
        if (!empty($opts['p'])) {
            $port = (int)$opts['p'];
            if ($port <= 0) {
                exit("输入port有误:{$port}");
            }
        }
        if (!empty($opts['port'])) {
            $port = (int)$opts['port'];
            if ($port <= 0) {
                exit("输入port有误:{$port}");
            }
        }
        return $port;
    }

    /**
     * 监听进程名称
     * @param $opts cli模式传输的值
     * @return string
     */
    private function listenName($opts) {
        $name = SWOOLE_TASK_NAME_PRE;
        if (!empty($opts['n'])) {
            $name = $opts['n'];
        }
        if (!empty($opts['name'])) {
            $name = $opts['n'];
        }
        return $name;
    }

    /**
     * 帮助文档
     * @return string
     */
    private function help() {
        return <<<HELP
用法：php swoole.php 选项 ... 命令[start|stop|restart|reload|close|status|list]
管理swoole-task服务,确保系统 lsof 命令有效
如果不指定监听host或者port，使用配置参数

参数说明
    --help  显示本帮助说明
    -d, --daemon    指定此参数,以守护进程模式运行,不指定则读取配置文件值
    -D, --nondaemon 指定此参数，以非守护进程模式运行,不指定则读取配置文件值
    -h, --host  指定监听ip,例如 php swoole.php -h127.0.0.1
    -p, --port  指定监听端口port， 例如 php swoole.php -h127.0.0.1 -p9520
    -n, --name  指定服务进程名称，例如 php swoole.php -ntest start, 则进程名称为SWOOLE_TASK_NAME_PRE-name
启动swoole-task 如果不指定 host和port，读取默认配置
强制关闭swoole-task 必须指定port,没有指定host，关闭的监听端口是  *:port,指定了host，关闭 host:port端口
平滑关闭swoole-task 必须指定port,没有指定host，关闭的监听端口是  *:port,指定了host，关闭 host:port端口
强制重启swoole-task 必须指定端口
平滑重启swoole-task 必须指定端口
获取swoole-task 状态，必须指定port(不指定host默认127.0.0.1), tasking_num是正在处理的任务数量(0表示没有待处理任务)

HELP;
    }
}
