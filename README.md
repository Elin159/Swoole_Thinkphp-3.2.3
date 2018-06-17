# Thinkphp_swoole

## 描述

本项目在基于Thinkphp_queue_zSet基础上增加对swoole 调用进行封装, 目前封装了tcp,udp,webSocket,有兴趣可以自行封装其它类型

#### 参数说明

    --help  显示本帮助说明
    -d, --daemon    指定此参数,以守护进程模式运行,不指定则读取配置文件值 （待完善）
    -D, --nondaemon 指定此参数，以非守护进程模式运行,不指定则读取配置文件值 （待完善）
    -h, --host  指定监听ip,例如 php artisan -u /Swoole/Server -h127.0.0.1
    -p, --port  指定监听端口port， 例如 php artisan -u /Swoole/Server -h127.0.0.1 -p9520
    -n, --name  指定服务进程名称，例如 php artisan -u /Swoole/Server -ntest start, 则进程名称为SWOOLE_TASK_NAME_PRE-name
  
 ### 其它说明
 
启动swoole 如果不指定 host和port，读取默认配置

用法：php -u /Swoole/TcpServer 选项 ... 命令[start|stop|restart|reload|close|status|list]

stop 强制关闭swoole 必须指定port,没有指定host，关闭的监听端口是  *:port,指定了host，关闭 host:port端口

close 平滑关闭swoole 必须指定port,没有指定host，关闭的监听端口是  *:port,指定了host，关闭 host:port端口 (暂时只支持tcpSocket)

restart 强制重启swoole 必须指定端口

reload 平滑重启swoole 必须指定端口 (暂时只支持tcpSocket)

status 获取swoole 状态，必须指定port(不指定host默认127.0.0.1), tasking_num是正在处理的任务数量(0表示没有待处理任务)

## TcpSocket 
### 例子:
php artisan -u /Swoole/TcpServer -h0.0.0.0 -p9501 -d start

