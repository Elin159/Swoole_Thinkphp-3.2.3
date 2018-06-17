<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件
header('Access-Control-Allow-Origin:*');//允许跨域

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);

// 定义应用目录
define('APP_PATH','./Application/');

define('HTML_PATH', './Application/Runtime/Html/'); //静态缓存文件目录，HTML_PATH可任意设置，此处设为当前项目下新建的html目录
define('UPLOAD_PATH', 'Public/upload/'); // 编辑器图片上传路径

//又拍云上传数据常量
//define('USER_NAME', 'elin');
//define('PWD', '159wxmusb1130');
//define('BUCKET', 'cs-image');
//define('PIC_PATH', dirname(__FILE__) . '/assets/sample.jpeg');
//define('PIC_SIZE', filesize(PIC_PATH));



require './Config/config.php';
require './vendor/autoload.php';
// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单
