<?php

/**
 * Created by PhpStorm.
 * User: John
 * Date: 1/6/17
 * Time: 17:39
 */

namespace Wechat;
header("Content-Type: text/html; charset=utf-8");
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'error_code.php');
date_default_timezone_set('PRC');

class WechatMch
{
//    const SYNC_COMPANY_USER_INFO_PATH = "wbap-bbfront/ngos";
    const SYNC_COMPANY_USER_INFO_PATH = "wbap-bbfront/ImportMrch";

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
     * 商家入驻
     * @param array $jsonData 包含入驻数据
     * @return array 包含 code msg 的返回信息。当 code 为 0 时表示成功。
     */
    public static function importMrch($jsonData)
    {
       /* $app_id = Config::APP_ID;
        $version = Config::OPENAPI_VERSION;
        $nonce = OAuth2::getNonce();
        // 强制转为 String 类型，否则排序出错
        $timestamp = (string)time();

        $params = array($app_id, $version, $nonce, $jsonData);

        $sign = OAuth2::getSign($params);
        if (!$sign) {
            error_log("Sign is empty!");
//            echo "Sign 为空！计算签名失败！\n\n";
            return array(
                'code' => OPENAPI_GET_SIGN_ERROR,
                'msg' => '签名计算失败！'
            );
        }

        $url_params = sprintf(OAuth2::COMMON_SIGN_FORMAT, $app_id, $nonce, $version, $sign, $timestamp);
		*/
        $header = ['Content-Type: application/json'];
        $header = ['Content-Type: text/xml'];
        $request = array(
            'url' => Config::getBaseUrl() . self::SYNC_COMPANY_USER_INFO_PATH ,
            'method' => 'post',
            'timeout' => self::$timeout,
            'data' => $jsonData,
            'header' => $header,
        );

        $result = HttpClient::sendRequest($request);
//        var_dump($result);

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

class Alipay {
//    const SYNC_COMPANY_USER_INFO_PATH = "api/acq/server/alipay/regmch";
    const SYNC_COMPANY_USER_INFO_PATH = "api/aap/server/wepay/merchantregister";
//    const SYNC_COMPANY_USER_INFO_PATH = "api/aap/server/wepay/merchantregister";

//https://l.test-svrapi.webank.com/api/acq/server/alipay/regmch
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
     * 商家入驻
     * @param array $jsonData 包含入驻数据
     * @return array 包含 code msg 的返回信息。当 code 为 0 时表示成功。
     */
    public static function importMrch($jsonData, $nonce, $sign, $version)
    {
        /* $app_id = Config::APP_ID;
         $version = Config::OPENAPI_VERSION;
         $nonce = OAuth2::getNonce();
         // 强制转为 String 类型，否则排序出错
         $timestamp = (string)time();

         $params = array($app_id, $version, $nonce, $jsonData);

         $sign = OAuth2::getSign($params);
         if (!$sign) {
             error_log("Sign is empty!");
 //            echo "Sign 为空！计算签名失败！\n\n";
             return array(
                 'code' => OPENAPI_GET_SIGN_ERROR,
                 'msg' => '签名计算失败！'
             );
         }

         $url_params = sprintf(OAuth2::COMMON_SIGN_FORMAT, $app_id, $nonce, $version, $sign, $timestamp);
         */
        $header = ['Content-Type: application/json'];
//        $header = ['Content-Type: text/xml'];
        $request = array(
            'url' => Config::getBaseUrl() . self::SYNC_COMPANY_USER_INFO_PATH. '?app_id='.C('ALI.APP_ID').'&nonce='.$nonce.'&version='.$version.'&sign='.$sign  ,
            'method' => 'post',
            'timeout' => self::$timeout,
            'data' => $jsonData,
            'header' => $header,
        );

        $result = HttpClient::sendRequest($request);
//        var_dump($result);

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

