<?php
return array(
    //'配置项'=>'配置值'
    'token_time' => '7200', // token过期时间
    'auth_key'  => '_*/-.\\;',
    'live_time' => '7300',
    'URL_MODEL' => 2, // 如果需要 隐藏 base.html  打开这行"URL_MODEL"注释 同时在apache环境下 开启 伪静态模块，  如果在nginx 下需要另外配置，参考thinkphp官网手册
    'DEFAULT_FILTER' => 'htmlspecialchars,strip_sql',   // 系统默认的变量过滤机制

    'APP_SUB_DOMAIN_DEPLOY'   =>    1, // 开启子域名配置
    'APP_SUB_DOMAIN_RULES'    =>    array(
//        'www'        => 'Home',  // admin子域名指向Admin模块
//        'api'         => 'Test',  // test子域名指向Test模块
    ),
    'LOG_RECORD' => true, // 开启日志记录
    'LOG_TYPE'   => 'File', // 日志记录类型 默认为文件方式

//    //redis配置
//    'REDIS_HOST' => '10.42.198.125',//redis主机
//    'REDIS_PASSWORD' => 'password',//redis密码
//    'REDIS_PORT'    => '6879',//redis端口号
//
//    //打印机用户设置
//    'MACHINE_USER_CODE' => '13034',
//
//    //极光推送设置
//    'JPUSH_APPKEY' => '8b45d60e2c54fdb6d15ebf11',
//    'JPUSH_MASTER_SECRET' => '588f1c5e10b9029fa0130a2f',

//'配置项'=>'配置值'
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  MYSQL_HOST, // 服务器地址
    'DB_NAME'               =>  MYSQL_NAME,          // 数据库名
    'DB_USER'               =>  MYSQL_USER,      // 用户名
//    'DB_USER'               =>  'root',      // 用户名
//    'DB_PWD'                =>  'xrk2016',          // 密码
    'DB_PWD'                =>  MYSQL_PASSWORD,          // 密码
    'DB_PORT'               =>  MYSQL_PORT,        // 端口
    'DB_PREFIX'             =>  'xrk_',    // 数据库表前缀
    'DB_DEBUG'  			=>  TRUE, // 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>  1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>  '', // 指定从服务器序号
);