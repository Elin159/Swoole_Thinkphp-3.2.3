<?php

/**
 * Created by PhpStorm.
 * User: John
 * Date: 1/6/17
 * Time: 17:40
 */

namespace Wechat;

date_default_timezone_set('PRC');
header("Content-Type: text/html; charset=utf-8");

function my_curl_reset($handler)
{
    curl_setopt($handler, CURLOPT_URL, '');
    curl_setopt($handler, CURLOPT_HTTPHEADER, array());
    curl_setopt($handler, CURLOPT_POSTFIELDS, array());
    curl_setopt($handler, CURLOPT_TIMEOUT, 0);
    curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($handler, CURLOPT_SSLCERT, '');
    curl_setopt($handler, CURLOPT_SSLKEY, '');
    curl_setopt($handler, CURLOPT_SSLKEYPASSWD, '');
}

class HttpClient
{
    public static $_httpInfo = '';
    public static $_curlHandler;


    /**
     * 封装方法，用来发送请求并处理返回
     * @param array $request 请求相关信息
     * @param boolean $needCert 是否需要客户端证书
     * @return array|mixed 处理过的请求返回信息
     */
    public static function sendRequest($request, $needCert = true)
    {
        if ($needCert) {
            $request = self::setClientCert($request);
            dump($request);
        }

        $rsp = self::send($request);
        if ($rsp === false) {
            self::logCurlInfo();
            return array(
                'code' => OPENAPI_NETWORK_ERROR . '|' . curl_errno(self::$_curlHandler),
                'msg' => 'network error',
            );
        }
        $info = self::info();
        $ret = json_decode($rsp, true);
        if ($ret === NULL) {
            self::logCurlInfo();
            return array(
                'code' => OPENAPI_NETWORK_ERROR,
                'msg' => $rsp,
                'data' => array()
            );
        }
//        var_dump($ret);
        return $ret;
    }

    /**
     * send http request
     * @param  array $request http请求信息
     *                   url             : 请求的url地址
     *                   method          : 请求方法，'get', 'post', 'put', 'delete', 'head'
     *                   data            : 请求数据，如有设置，则method为post
     *                   header          : 需要设置的http头部
     *                   host            : 请求头部host
     *                   timeout         : 请求超时时间
     *                   cert            : ca文件路径
     *                   ssl_version     : SSL版本号
     *                   client_cert     : 客户端证书路径
     *                   client_key      : 客户端证书私钥路径
     *                   client_key_pass : 客户端证书密码
     * @return string    http请求响应
     */
    private static function send($request)
    {
        if (self::$_curlHandler) {
            if (function_exists('curl_reset')) {
                curl_reset(self::$_curlHandler);
            } else {
                my_curl_reset(self::$_curlHandler);
            }
        } else {
            self::$_curlHandler = curl_init();
        }

//        curl_setopt(self::$_curlHandler, CURLOPT_VERBOSE, true);
        curl_setopt(self::$_curlHandler, CURLOPT_URL, $request['url']);
        switch (true) {
            case isset($request['method']) && in_array(strtolower($request['method']), array('get', 'post', 'put', 'delete', 'head')):
                $method = strtoupper($request['method']);
                break;
            case isset($request['data']):
                $method = 'POST';
                break;
            default:
                $method = 'GET';
        }

        $header = isset($request['header']) ? $request['header'] : array();
        $header[] = 'Method:' . $method;
        $header[] = 'User-Agent:' . Config::getUA();
        $header[] = 'Connection: keep-alive';

        if ('POST' == $method) {
            $header[] = 'Expect: ';
        }
//        dump($header);
        isset($request['host']) && $header[] = 'Host:' . $request['host'];
//        curl_setopt(self::$_curlHandler, CURLOPT_HTTPHEADER, $header);
        curl_setopt(self::$_curlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(self::$_curlHandler, CURLOPT_CUSTOMREQUEST, $method);
        isset($request['timeout']) && curl_setopt(self::$_curlHandler, CURLOPT_TIMEOUT, $request['timeout']);

        isset($request['data']) && in_array($method, array('POST', 'PUT')) && curl_setopt(self::$_curlHandler, CURLOPT_POSTFIELDS, $request['data']);

        if (isset($request['proxy'])) {
            curl_setopt(self::$_curlHandler, CURLOPT_PROXY, $request['proxy']);
        }

        if (isset($request['proxy_auth'])) {
            curl_setopt(self::$_curlHandler, CURLOPT_PROXYUSERPWD, $request['proxy_auth']);
        }

        if (isset($request['proxy_type'])) {
            switch ($request['proxy_type']) {
                case 'http':
                    curl_setopt(self::$_curlHandler, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    break;
                case 'socks4':
                    curl_setopt(self::$_curlHandler, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
                    break;
                case 'socks5':
                    curl_setopt(self::$_curlHandler, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                    break;
                default:
                    curl_setopt(self::$_curlHandler, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    break;
            }
        }

        $ssl = substr($request['url'], 0, 8) == "https://" ? true : false;
        if (isset($request['cert'])) {
            curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt(self::$_curlHandler, CURLOPT_CAINFO, $request['cert']);
            curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYHOST, 2);
            if (isset($request['ssl_version'])) {
                curl_setopt(self::$_curlHandler, CURLOPT_SSLVERSION, $request['ssl_version']);
            } else {
                curl_setopt(self::$_curlHandler, CURLOPT_SSLVERSION, 4);
            }
        } else {
            if ($ssl) {
                 curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYPEER, true);   //true any ca
//                curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYHOST, 1);       //check only host
                if (isset($request['ssl_version'])) {
                    curl_setopt(self::$_curlHandler, CURLOPT_SSLVERSION, $request['ssl_version']);
                } else {
                    curl_setopt(self::$_curlHandler, CURLOPT_SSLVERSION, 4);
                }
            }
        }
        if ($ssl && isset($request['client_cert_type'])) {
            switch ($request['client_cert_type']) {
                case 'P12':
                    if (isset($request['client_cert']) && isset($request['client_key_pass'])) {
                        curl_setopt(self::$_curlHandler, CURLOPT_SSLCERTTYPE, $request['client_cert_type']);
                        curl_setopt(self::$_curlHandler, CURLOPT_SSLCERT, $request['client_cert']);
                        curl_setopt(self::$_curlHandler, CURLOPT_SSLKEYPASSWD, $request['client_key_pass']);
                    }
                    break;
                case 'PEM':
                    if (isset($request['client_cert']) && isset($request['client_key']) && isset($request['client_key_pass'])) {
                        curl_setopt(self::$_curlHandler, CURLOPT_SSLCERTTYPE, $request['client_cert_type']);
                        curl_setopt(self::$_curlHandler, CURLOPT_SSLCERT, $request['client_cert']);
                        curl_setopt(self::$_curlHandler, CURLOPT_SSLKEY, $request['client_key']);
                        curl_setopt(self::$_curlHandler, CURLOPT_SSLKEYPASSWD, $request['client_key_pass']);
                    }
            }
        };

        $ret = curl_exec(self::$_curlHandler);
        self::$_httpInfo = curl_getinfo(self::$_curlHandler);
        return $ret;
    }

    private static function info()
    {
        return self::$_httpInfo;
    }

    /**
     * 添加证书信息到 Http Req 参数数组
     * @param $request array 构造好的 Http Req 参数数组
     * @return mixed 添加了证书信息的 Http Req 参数数组
     */
    private static function setClientCert($request)
    {
        if (Config::PROXY) {
            $request['proxy'] = Config::PROXY;
            if (Config::PROXY_TYPE) {
                $request['proxy_type'] = strtolower(Config::PROXY_TYPE);
            }
            if (Config::PROXY_USER) {
                $request['proxy_auth'] = Config::PROXY_USER . ':' . Config::PROXY_PASSWORD;
            }
        }

        $request['client_cert_type'] = Config::CLIENT_CERT_TYPE;
        $request['client_cert'] = getcwd() . Config::CLIENT_CERT;
        $request['client_key'] = getcwd() . Config::CLIENT_KEY;
        $request['client_key_pass'] = Config::CLIENT_KEY_PASS;
        return $request;
    }

    private static function logCurlInfo()
    {
        error_log(var_export(curl_error(self::$_curlHandler), true));
        $return_code = curl_getinfo(self::$_curlHandler, CURLINFO_HTTP_CODE);
        switch ($return_code) {
            case 400:
                error_log("返回码为 400，可能是未带证书或证书不正确！");
                break;
            case 403:
                error_log("返回码为 403，IP 未在白名单内！请加白名单或使用代理！");
                break;
        }
        error_log($return_code);
        error_log(var_export(self::info(), true));
    }
}