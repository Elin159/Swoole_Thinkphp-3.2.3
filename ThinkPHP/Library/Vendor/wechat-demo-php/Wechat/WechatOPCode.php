<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/18 0018
 * Time: 下午 17:26
 */

namespace Wechat;
header("Content-Type: text/html; charset=utf-8");
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'error_code.php');
date_default_timezone_set('PRC');

class WechatOPCode
{
//    const SYNC_COMPANY_USER_INFO_PATH = "wbap-bbfront/ngos";
    const SYNC_COMPANY_USER_INFO_PATH = "Home/RsaCheck/verify";

    //HTTP请求超时时间
    private static $timeout = 60;

    /**
     * 设置HTTP请求超时时间
     * @param  int $timeout 超时时长
     * @return bool
     */
    public static function setTimeout($timeout = 60)
    {
        if (!is_int($timeout) || $timeout < 0) {
            return false;
        }
        self::$timeout = $timeout;
        return true;
    }

    /**
     * 执行
     * @param array $jsonData 包含入驻数据
     * @return array 包含 code msg 的返回信息。当 code 为 0 时表示成功。
     */
    public static function execute($jsonData)
    {
        $header = ['Content-Type: application/json; charset=utf-8'];
//        dump(Config::getBaseUrl() . self::SYNC_COMPANY_USER_INFO_PATH);die();
//        $header = ['Content-Type: text/xml'];
        $request = array(
            'url' => Config::getBaseUrl() . self::SYNC_COMPANY_USER_INFO_PATH ,
            'method' => 'post',
            'timeout' => self::$timeout,
            'data' => $jsonData,
            'header' => $header,
        );

        $result = HttpClient::sendRequest($request);
        return $result;
    }

    public static function getCompanyMainUrl($userId)
    {
        if (!$userId) {
            echo "UserId 为空！";
            return false;
        }
        $app_id = Config::APP_ID;
        $nonce = OAuth2::getNonce();

        $sign = OAuth2::getSsoLoginSign($userId, $nonce);
        $version = "1.0.0";

        $url = sprintf(self::COMPANY_MAIN_URL, $version, $nonce, $sign, $app_id);
        return $url;
    }


}