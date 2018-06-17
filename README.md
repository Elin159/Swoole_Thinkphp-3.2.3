# Thinkphp_queue_Elin

描述

本项目是基于redis自带的队列结构而进行封装，暂时封装了listen模式，以及快捷创建队列业务文件功能，
本队列利用zSet实现队列操作，队列推送成功会返回对应的值，利用对应的值可执行删除队列对应的值的任务

queue:listen 命令

listen 命令：

该命令将会创建一个listen父进程,然后父进程通过 

php artisan /Home/queue make:queue name 

的方式来创建一个子进程来处理消息队列，且限制该进程的执行时间。
 
php artisan /Home/queue listen:name 


## 项目代码 
### 例子:
addJoe::Joe(new SendMail($data['user_id'],$data['email'],$data['content']))->push();

### 执行监听:
php artisan /Home/queue listen:email;

### 延迟执行:
addJoe::Joe(new SendMail($data['user_id'],$data['email'],$data['content']))->delay(3)->push();

### 指定队列执行:
addJoe::Joe(new SendMail($data['user_id'],$data['email'],$data['content']))->delay(3)->onQueue('email')->push();

#### 指定队列执行也可如此
addJoe::Joe(new SendMail($data['user_id'],$data['email'],$data['content']),'email')->delay(3)->push();
队列业务流程

#### 本队列默认设置

redis密码为:password
端口为:6379
地址为:127.0.0.1

#### 如果修改可通过 
php artisan /Home/queue make:queue name 
创建的队列业务文件头部加入属性

public $host = '127.0.0.1'; 
public $port = '6379'; 
public $password = 'password'; 

#### 也可在配置文件Config/config.php 下进行配置

##### redis队列资料

define('REDIS_HOST', '127.0.0.1');

define('REDIS_PASSWORD', '');

define('REDIS_PORT', 6379);

define('REDIS_DATABASE',0);


##### 数据库资料
define('MYSQL_HOST', '127.0.0.1');

define('MYSQL_NAME', '');

define('MYSQL_USER', '');

define('MYSQL_PASSWORD', '');

define('MYSQL_PORT', '3066');

