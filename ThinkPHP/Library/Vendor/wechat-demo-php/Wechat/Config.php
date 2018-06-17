<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 8/20/16
 * Time: 16:54
 */

namespace Wechat;


class Config
{
    const VERSION = "v1.0";
    const OPENAPI_VERSION = "1.0.0";
    const SERVER_BASE_URL = "https://xly.xiarikui.cn/";
    const ENVIRONMENT = "";


	//const PROXY = "119.29.195.110:8080";
	const PROXY = NULL;
    const PROXY_TYPE = "http"; // http, socks4, socks5
    // 设置代理用户名密码，为 NULL 则不使用
    const PROXY_USER = NULL;
    const PROXY_PASSWORD = NULL;


	//const APP_ID = "WXXXXXX";
    //const APP_SECRET = "XXXXXXXXXXXXXXX";

    const SERVER_CERT = "";

    // 推荐 Linux 使用 PEM 格式，低版本的 libcurl 只支持该格式
    // Mac 上自带的 libcurl 是苹果修改过的，只能使用 P12 格式，并且会存储在 KeyChain 中
    const CLIENT_CERT_TYPE = "PEM";
    // 需要写绝对路径。低版本的 libcurl 只支持绝对路径
//    const CLIENT_CERT = "E:\\php_demo\\wallet-demo-php\\test.crt";
    const CLIENT_CERT = "/P5850001_default.PEM";
    const CLIENT_KEY = "/P5850001_private.PEM";
//    const CLIENT_KEY = "E:\\php_demo\\wallet-demo-php\\test.key";
    const CLIENT_KEY_PASS = "qq616125903";

    const SIGN_TICKET_TYPE = "SIGN";

    const ACCESS_TOKEN_CACHE_KEY = "access_token";
    const SIGN_TICKET_CACHE_KEY = "sign_ticket";


    // 提前 300 秒刷新 Token 和 Ticket
    const PRE_FRESH_TIME_DELTA = 300;

    public static function getUA()
    {
        return 'openapi-sdk-' . self::VERSION;
    }

    public static function getBaseUrl()
    {
        if (self::ENVIRONMENT) {
            return sprintf(self::SERVER_BASE_URL, self::ENVIRONMENT);
        } else {
            return self::SERVER_BASE_URL;
        }
    }

}