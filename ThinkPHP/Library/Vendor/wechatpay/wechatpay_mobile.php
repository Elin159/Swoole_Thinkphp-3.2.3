<?php

/**
 * 微信移动端支付插件 逻辑定义
 * Class
 * @package Home\Payment
 */
class WeChatPayMobile
{

    /**
     * 析构流函数
     */

    public function __construct($appid, $appsecret, $mchid, $key)
    {

        require_once("lib/WxPay.Api.php"); // 微信扫码支付demo 中的文件         
        require_once("lib/WxPay.NativePay.php");
        require_once("lib/WxPay.JsApiPay.php");

        WxPayConfig::$appid = $appid; // * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
        WxPayConfig::$mchid = $mchid; // * MCHID：商户号（必须配置，开户邮件中可查看）
        WxPayConfig::$key = $key; // KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
        WxPayConfig::$appsecret = $appsecret; // 公众帐号secert（仅JSAPI支付的时候需要配置)，
    }

    /**
     * 微信小程序支付
     * @param $order 订单数据
     * @param $openid 用户openid
     * @return json数据 发起支付凭证
     */
    function getJSAPI($order, $openid)
    {

        try {
            $tools = new JsApiPay();
            //②、统一下单
            $input = new WxPayUnifiedOrder();
            $input->SetBody("蕾粉团订单：" . $order['order_sn']);
            //$input->SetAttach($order['attach']);
            $input->SetOut_trade_no($order['order_sn']);
            $input->SetTotal_fee($order['order_amount'] * 100);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag("xrk_wx_pay");
            $input->SetNotify_url($order['notify_url']);
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($openid);
            $order2 = WxPayApi::unifiedOrder($input);

            /*if(strlen($order2['prepay_id']) > 0){
                //prepay_id可发送 3次小程序通知
                M('user_group_order')->where("order_sn = '".$order['order_sn']."'")->setField('form_id',$order2['prepay_id']);
            }*/

            $jsApiParameters = $tools->GetJsApiParameters($order2);

        }catch (WxPayException $e){
            throw new \Think\Exception($e->getMessage());
        }
        return $jsApiParameters;
    }

    /**
     * 微信退款
     * @param $order 订单数组
     * @return bool|mixed
     */
    function wxRefund($order)
    {
        /*$ref= md5("appid=".WxPayConfig::$appid."&mch_id=".WxPayConfig::$mchid."&nonce_str=".hRandom(6)."&op_user_id=646131"
            . "&out_refund_no=D2131231&out_trade_no=1231jaf&refund_fee=0.01&total_fee=0.01"
            . "&transaction_id=1293102u4slfk14&key=asduaisd12739182");//sign加密MD5
*/
        try {
            /*$refund = array(
                'appid' => WxPayConfig::$appid,
                'mch_id' => WxPayConfig::$mchid,//商户号
                'nonce_str' => hRandom(6),//随机字符串
                'out_refund_no' => $order['order_sn'],
                'out_trade_no' => $order['order_sn'],
                'refund_fee' => $order['order_amount'] * 100,
                'total_fee' => $order['order_amount'] * 100,
                'transaction_id' => $order['trade_sn'],
                'key' => WxPayConfig::$key,
            );

            $sign = md5(http_build_query($refund));
            $refund['sign'] = $sign;

            $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";//微信退款地址，post请求
            $data = $this->curl_post_ssl($url, http_build_query($refund));
            */

            $input = new WxPayRefund();
            $input->SetOut_trade_no($order['order_sn']);
            $input->SetTransaction_id($order['trade_sn']);
            $input->SetTotal_fee($order['order_amount'] * 100);
            $input->SetRefund_fee($order['order_amount'] * 100);
            $input->SetOut_refund_no($order['order_sn']);
            $input->SetOp_user_id(WxPayConfig::$mchid);
            $data = WxPayApi::refund($input);

        }catch (WxPayException $e){
            throw new \Think\Exception($e->getMessage());
        }

        return $data;
    }

    function curl_post_ssl($url, $vars, $second = 30, $aHeader = array())
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //以下两种方式需选择一种

        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        //curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        //curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/cert.pem');
        //默认格式为PEM，可以注释
        //curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        //curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/private.pem');

        //第二种方式，两个文件合成一个.pem文件
        curl_setopt($ch, CURLOPT_SSLCERT, getcwd().'/ThinkPHP/Library/Vendor/wechatpay/cert/all.pem');

        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode: $error\n";
            curl_close($ch);
            return false;
        }
    }

}
