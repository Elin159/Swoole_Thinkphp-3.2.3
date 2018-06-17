<?php

/**
 * Created by PhpStorm.
 * User: John
 * Date: 8/20/16
 * Time: 17:21
 */
ini_set('display_errors', '1');
require(__DIR__ . DIRECTORY_SEPARATOR .'include.php');

class import_merchant
{
    /**
     * 微信商家入驻
     * @param $data
     */
    public function import($data)
    {
        $result = Wechat\WechatMch::importMrch($data);
        var_dump($result);
    }

    /**
     * 阿里支付宝入驻
     * @param $data
     */
    public function ali_import($data, $nonce, $sign, $version = '1.0.0')
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        file_put_contents('ali_pay.txt', $data);
        $result = Wechat\Alipay::importMrch($data, $nonce, $sign, $version);
        var_dump($result);
    }

    /**
     * 获取阿里access_token
     * @param $app_id
     * @param $secret
     * @param string $grant_tye
     * @param string $version
     * @return array
     */
    public function get_ali_access_token($app_id, $secret, $grant_tye = 'client_credential', $version = '1.0.0')
    {
        $result = Wechat\AliAccessToken::execute($app_id, $secret, $grant_tye, $version);
        return $result;
    }

    /**
     * 获取阿里ticket
     * @param $app_id
     * @param $access_token
     * @param string $type
     * @param string $version
     * @return array
     */
    public function get_ali_ticket($app_id, $access_token, $type = 'SIGN', $version = '1.0.0')
    {
        $result = Wechat\AliTicket::execute($app_id, $access_token, $type, $version);
        return $result;
    }

    /**
     * 阿里支付宝入驻
     * @param $data
     */
    public function aliQRCode($data, $nonce, $sign, $version = '1.0.0')
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        file_put_contents('ali_pay_json.txt', $data);
        $result = Wechat\AliQRCode::execute($data, $nonce, $sign, $version);
        return $result;
    }

    /**
     * 微信二维码支付
     * @param $data
     * @return array
     */
    public function wxQRCode($data)
    {
//        $data = json_encode($data);
//        $data = json_encode($data, JSON_UNESCAPED_SLASHES);
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        file_put_contents('pay_code.txt', $data);
        $result = Wechat\WechatQRCode::execute($data);
        return $result;
    }

    /**
     * 微信公众号支付
     * @param $data
     * @return array
     */
    public function wxOPCode($data)
    {
//        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        file_put_contents('pay_code.txt', $data);
        $result = Wechat\WechatOPCode::execute($data);
        return $result;
    }

    public function selectMac($data) {
        $data = json_encode($data, JSON_UNESCAPED_SLASHES);
        file_put_contents('pay_code.txt', $data);
        $result = Wechat\WechatSelectMac::execute($data);
        return $result;
    }


}

	

