<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/15 0015
 * Time: 下午 15:22
 */
use Server\AuthService;
use Server\Service;

/**
 * @param $arr
 * @param $key_name
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名
 */
function convert_arr_key($arr, $key_name)
{
    $arr2 = array();
    foreach ($arr as $key => $val) {
        $arr2[$val[$key_name]] = $val;
    }
    return $arr2;
}

/**
 * 手机号显示**
 */
function get_hidden_phone($num, $is_hidden = 'F')
{
    if ($is_hidden == 'F') {
        return substr_replace($num, '****', 3, 4);
    } else {
        return $num;
    }
}

/**
 * 获取数组中的某一列
 * @param type $arr 数组
 * @param type $key_name 列名
 * @return type  返回那一列的数组
 */
function get_arr_column($arr, $key_name)
{
    return array_column($arr, $key_name);
    $arr2 = array();
    foreach ($arr as $key => $val) {
        $arr2[] = $val[$key_name];
    }
    $arr2 = array_unique($arr2);
    return $arr2;
}

function hRandom($len = 6, $is_numeric = false)
{
    $c = $is_numeric ? '0123456789' : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $rand = "";
    srand((double)microtime() * 1000000);
    for ($i = 0; $i < $len; $i++) {
        $rand .= $c[rand() % strlen($c)];
    }
    return $rand;
}

/**
 * 字符串加密、解密函数
 *
 *
 * @param    string $txt 字符串
 * @param    string $operation ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
 * @param    string $key 密钥：数字、字母、下划线
 * @param    string $expiry 过期时间
 * @return    string
 */
function sys_auth($string, $operation = 'ENCODE', $key = '', $expiry = 0)
{
    $key_length = 4;
    $key = md5($key != '' ? $key : C('auth_key'));
    $fixedkey = md5($key);
    $egiskeys = md5(substr($fixedkey, 16, 16));
    $runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), -$key_length) : substr($string, 0, $key_length)) : '';
    $keys = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
    $string = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $egiskeys), 0, 16) . $string : base64_decode(substr($string, $key_length));

    $i = 0;
    $result = '';
    $string_length = strlen($string);
    for ($i = 0; $i < $string_length; $i++) {
        $result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
    }
    if ($operation == 'ENCODE') {
        return $runtokey . str_replace('=', '', base64_encode($result));
    } else {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $egiskeys), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    }
}

/**
 * 设置保存上次访问的链接
 */
function setUrlCookie()
{
    $refer = 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    cookie('referUrl', $refer, C('token_time'));
}

// 检测输入的验证码是否正确，
//$code为用户输入的验证码字符串
function check_verify($code, $id = '')
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 转换SQL关键字
 *
 * @param unknown_type $string
 * @return unknown
 */
function strip_sql($string)
{
    $pattern_arr = array(
        "/\bunion\b/i",
        "/\bselect\b/i",
        "/\bupdate\b/i",
        "/\bdelete\b/i",
        "/\boutfile\b/i",
        "/\bor\b/i",
        "/\bchar\b/i",
        "/\bconcat\b/i",
        "/\btruncate\b/i",
        "/\bdrop\b/i",
        "/\binsert\b/i",
        "/\brevoke\b/i",
        "/\bgrant\b/i",
        "/\breplace\b/i",
        "/\balert\b/i",
        "/\brename\b/i",
        "/\bmaster\b/i",
        "/\bdeclare\b/i",
        "/\bsource\b/i",
        "/\bload\b/i",
        "/\bcall\b/i",
        "/\bexec\b/i",
        "/\bdelimiter\b/i",
    );
    $replace_arr = array(
        'ｕｎｉｏｎ',
        'ｓｅｌｅｃｔ',
        'ｕｐｄａｔｅ',
        'ｄｅｌｅｔｅ',
        'ｏｕｔｆｉｌｅ',
        'ｏｒ',
        'ｃｈａｒ',
        'ｃｏｎｃａｔ',
        'ｔｒｕｎｃａｔｅ',
        'ｄｒｏｐ',
        'ｉｎｓｅｒｔ',
        'ｒｅｖｏｋｅ',
        'ｇｒａｎｔ',
        'ｒｅｐｌａｃｅ',
        'ａｌｅｒｔ',
        'ｒｅｎａｍｅ',
        'ｍａｓｔｅｒ',
        'ｄｅｃｌａｒｅ',
        'ｓｏｕｒｃｅ',
        'ｌｏａｄ',
        'ｃａｌｌ',
        'ｅｘｅｃ',
        'ｄｅｌｉｍｉｔｅｒ',
    );

    return is_array($string) ? array_map('strip_sql', $string) : preg_replace($pattern_arr, $replace_arr, $string);
}

/**
 * 返回用户性别名称
 * @param $sex 性别数值
 * @return mixed
 */
function sex_name($sex)
{
    $arr = [0 => '女', 1 => '男', '2' => '保密'];
    return $arr[$sex];
}

/**
 * 返回用户是否拥有该权限
 * @param $role_name 权限名称
 * @param $user_id 用户id
 * @return mixed
 */
function authorization($role_name, $user_id)
{
    if($result = \OtherSdk\RedisProvider::get($user_id.$role_name)) {
        return $result;
    } else {
        $result = AuthService::authorization($role_name, $user_id);
        \OtherSdk\RedisProvider::set($user_id . $role_name, $result, '86400');
        return $result;
    }
}

/**
 * 返回用户是否拥有该角色
 * @param $parts_name 角色名
 * @param $user_id 用户id
 * @return mixed
 */
function isParts($parts_name, $user_id)
{
    if ($result = \OtherSdk\RedisProvider::get($user_id . $parts_name)) {
        return $result;
    } else {
        $result = AuthService::isParts($parts_name, $user_id);
        \OtherSdk\RedisProvider::set($user_id . $parts_name, $result, '86400');
        return $result;
    }
}

/**
 * 计算总数
 * @param $num
 * @return int
 */
function sum($num)
{
    static $count = 0;
    $pos = strpos($num, ':');
    if ($pos) {
        $arr = explode(':', $num);
        if (is_numeric($arr[0]) && is_numeric($arr[1])) {
            $count += $arr[1];
        }
    }
    return $count;
}

/**
 * 返回项目名字
 * @param $pro
 * @return int
 */
function product_name($pro)
{
    if ($pro['type'] == 2) {
        $product = M('product')->field('delete_tag, name')
            ->where('id = ' . $pro['product_id'])->find();
        if ($product['delete_tag'] == 'T')
            $product['name'] .= '(已删除)';
        return $product['name'];
    }
}

/**
 * 返回产品名字
 * @param $pro
 * @return int
 */
function service_name($pro)
{
    if ($pro['type'] != 2) {
        $product = M('product')->field('delete_tag, name')
            ->where('id = ' . $pro['product_id'])->find();
        if ($product['delete_tag'] == 'T')
            $product['name'] .= '(已删除)';
        return $product['name'];
        return $pro['product_name'];
    }
}

function pay_type($type)
{
    $id = strpos($type, ':');
    $pay = M('pay')->select();
    $pay = convert_arr_key($pay, 'id');
    return $pay[$id[0]]['name'];
}

/**
 * 给我美容师
 * @param $admin_id 管理员id
 * @param $store_status 店铺权限
 * @param $isAreaManager 区域经理权限
 * @param $isAdmin 超级管理员权限
 * @return array|mixed
 */
function getService($admin_id)
{
    $field = 'id,is_promoter,nickname,store_id';
    $parts = Service::getPartsStatus($admin_id);
    $isAdmin = $parts['is_ceo'];
    $store_status = !!(($parts['is_store'] || $parts['is_reception'] || $parts['is_manager'] || $parts['is_service']));
    $isAreaManager = $parts['is_manager'];
    $isSubAreaManager = $parts['is_sub_manager'];
//    if ($parts['is_service']) {
//        return [];
//    }

    $Model = M('sys_users');
    $service = M('parts')->where('name = "service"')->find();
    $partsModel = M('part_sys_user');
    $services = $partsModel->field('user_id')->where('part_id = ' . $service['id'])->select();
    if (!$services) {
        $admin_ids = 0;
    } else {
        $admin_ids = get_arr_column($services, 'user_id');
        $admin_ids = join(',', $admin_ids);
    }
    $users = [];
    if ($store_status) {//如果是店铺或前台
        $user = $Model->where('id = ' . $admin_id)->find();
        $users = $Model->field($field)->where('store_id = ' . $user['store_id'] . ' and id in (' . $admin_ids . ') and delete_tag = "F"')->select();
    }

    if ($isAreaManager || $isSubAreaManager) {//如果是区域经理
        $store_ids = M('store_sys_users')->where('user_id = ' . $admin_id)->select();
        if ($store_ids) {
            $store_ids = get_arr_column($store_ids, 'store_id');
            $ids = join(',', $store_ids);

            $where['store_id'] = ['in', $ids];
            $where['delete_tag'] = 'F';
            if ($admin_ids == 0) {
                $users = [];
            } else {
                $where['id'] = ['in', $admin_ids];
                $users = $Model->field($field)->where($where)->select();
                $users = convert_arr_key($users, 'id');
            }
        } else {
            $users = [];
        }
    }

    if ($isAdmin) {
        $where['id'] = ['in', $admin_ids];
        $where['delete_tag'] = 'F';
        $users = $Model->field('nickname,id')->where($where)->select();
    }

    if (($store_status || $isAdmin || $isAreaManager || $isSubAreaManager)) {
        $users = convert_arr_key($users, 'id');
    }
    return $users;
}

/**
 * 请求一个url
 * @param $url
 * @param array $params 附带参数
 * @param int $expire 处理超时时间
 * @param array $extend 额外的配置
 * @param string $hostIp 请求的host的地址
 * @return array
 */
function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '')
{
    if (empty($url)) {
        return array('code' => '100');
    }

    $_curl = curl_init();
    $_header = array(
        'Accept-Language: zh-CN',
        'Connection: Keep-Alive',
        'Cache-Control: no-cache'
    );
    // 方便直接访问要设置host的地址
    if (!empty($hostIp)) {
        $urlInfo = parse_url($url);
        if (empty($urlInfo['host'])) {
            $urlInfo['host'] = substr(DOMAIN, 7, -1);
            $url = "http://{$hostIp}{$url}";
        } else {
            $url = str_replace($urlInfo['host'], $hostIp, $url);
        }
        $_header[] = "Host: {$urlInfo['host']}";
    }

    // 只要第二个参数传了值之后，就是POST的
    if (!empty($params)) {
        if ($params['post_format'] == 'json') {
            curl_setopt($_curl, CURLOPT_POSTFIELDS, json_encode($params));
        } else {
            curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($_curl, CURLOPT_POST, true);
    }

    if (substr($url, 0, 8) == 'https://') {
        curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    curl_setopt($_curl, CURLOPT_URL, $url);
    curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($_curl, CURLOPT_USERAGENT, 'API PHP CURL');
    curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);

    if ($expire > 0) {
        curl_setopt($_curl, CURLOPT_TIMEOUT, $expire); // 处理超时时间
        curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire); // 建立连接超时时间
    }

    // 额外的配置
    if (!empty($extend)) {
        curl_setopt_array($_curl, $extend);
    }

    $result['result'] = curl_exec($_curl);
    $result['code'] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
    $result['info'] = curl_getinfo($_curl);
    if ($result['result'] === false) {
        $result['result'] = curl_error($_curl);
        $result['code'] = -curl_errno($_curl);
    }

    curl_close($_curl);
    return $result;
}


/**
 * 加密
 * @param $arr 需加密的数组
 * @return string 返回加密后字符串
 */
function encryptToken($arr)
{
    $auth_key = md5(C('auth_key'));
    //$token_time = C('token_time');
    return sys_auth(json_encode($arr), 'ENCODE', $auth_key);
}

/**
 * 解密
 * @param $token
 * @return array|bool
 */
function decryptToken($token)
{
    if (strlen($token) == 0) {
        return false;
    }
    $auth_key = md5(C('auth_key'));
    $data = sys_auth($token, 'DECODE', $auth_key);
    if (!$data) {
        return false;
    }
    $data = json_decode($data,true);
    return $data;
}

/**
 * 计算两个经纬度之间的距离，返回单位米
 * @param $lng1 A经度
 * @param $lat1 A纬度
 * @param $lng2 B经度
 * @param $lat2 B纬度
 * @return float
 */
function getDistance($lng1, $lat1, $lng2, $lat2)
{
    $earthRadius = 6378137;//6367000; //approximate radius of earth in meters
    $lat1 = ($lat1 * pi()) / 180;
    $lng1 = ($lng1 * pi()) / 180;
    $lat2 = ($lat2 * pi()) / 180;
    $lng2 = ($lng2 * pi()) / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}


/**
 * 发送邮件
 * @param $fromEmail 发送者邮件
 * @param $fromName 发送者名称
 * @param $receiveArr 接收者邮件数组
 * @param $subject 标题
 * @param $body 邮件内容
 * @param $ccArr cc邮件数组
 * @param $bccArr bcc邮件数组
 * @param $attachmentArr 附件数组
 * @return bool
 */
function sendEmail($fromName, $receiveArr, $subject, $body, $ccArr = array(), $bccArr = array(), $attachmentArr = array())
{
    vendor('phpmailer/classphpmailer');
    $mail = new \PHPMailer(true);
    try {
        $mail->SMTPDebug = false;
        $mail->isSMTP();                                      // Set mailer to use SMTP
//        $mail->Host = 'smtp.exmail.qq.com';  // Specify main and backup SMTP servers
        $mail->Host = 'smtp.163.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'm17190446349@163.com';                 // SMTP username
        $mail->Password = 'ceshi123';                           // SMTP password
        $mail->SMTPSecure = 'ssl'; //'tls'
        $mail->Port = 465; //25
        //Recipients
        $mail->setFrom('m17190446349@163.com', $fromName);
        foreach ((array)$receiveArr as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($email, '');
            }
        }
        foreach ((array)$ccArr as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->AddCC($email, '');
            }
        }
        foreach ((array)$bccArr as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->AddBCC($email, '');
            }
        }
        foreach ((array)$attachmentArr as $attach) {
            $mail->AddAttachment($attach['path'], $attach['name']);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $body;

        $mail->send();
    } catch (Exception $e) {
        //echo 'Mailer Error: ' . $mail->ErrorInfo;
        return false;
    }
    return true;
}

/**
 * 生成二维码
 * @param  string $url url连接
 * @param  integer $size 尺寸 纯数字
 */
function qrcode($url, $size = 4)
{
    vendor('phpqrcode/phpqrcode');
    QRcode::png($url, false, QR_ECLEVEL_L, $size, 2, false, 0xFFFFFF, 0x000000);
}

/**
 * 获取微信支付返回通知结果
 * @param $data 回调数据
 * @param $handler 处理验证的支付对象
 * @return array|bool|void
 */
function getWechatPayNotify($data, $handler)
{
    vendor('wechatpay/wechatpay');
    if ($handler == 'halong') {
        $wechat = new \WeChatPay(WECHATPAY_HALONG_APPID, WECHATPAY_HALONG_APPSECRET, WECHATPAY_HALONG_MCHID, WECHATPAY_HALONG_KEY);
    } elseif ($handler == 'group') {
        $wechat = new \WeChatPay(GROUP_APPKEY, GROUP_APPSECRET, WECHATPAY_GROUP_MCHID, WECHATPAY_GROUP_KEY);
    } else {
        return;
    }
    $data = $wechat->toArray($data);
    // 保存原sign
    $data_sign = $data['sign'];
    // sign不参与签名
    unset($data['sign']);
    $sign = $wechat->makeSign($data);
    // 判断签名是否正确  判断支付状态
    if ($sign === $data_sign && $data['return_code'] == 'SUCCESS' && $data['result_code'] == 'SUCCESS') {
        $result = $data;
    } else {
        $result = false;
    }
    // 返回状态给微信服务器
    if ($result) {
        $str = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    } else {
        $str = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[数据有误]]></return_msg></xml>';
    }
    echo $str;
    return $result;
}

/**
 * 拼团小程序的微信支付
 * @param $order 订单数据
 * @param $openid 用户openid
 * @return json数据 授权验证数据，发起支付
 */
function startWechatPayForGroup($order, $openid)
{
    vendor('wechatpay/wechatpay_mobile');
    $wechat = new \WeChatPayMobile(GROUP_APPKEY,GROUP_APPSECRET,WECHATPAY_GROUP_MCHID,WECHATPAY_GROUP_KEY);
    return $wechat->getJSAPI($order, $openid);
}

/**
 * 拼团小程序微信退款
 * @param $order 订单数组
 * @return bool|mixed
 */
function wechatPayRefundForGroup($order)
{
    vendor('wechatpay/wechatpay_mobile');
    $wechat = new \WeChatPayMobile(GROUP_APPKEY,GROUP_APPSECRET,WECHATPAY_GROUP_MCHID,WECHATPAY_GROUP_KEY);
    return $wechat->wxRefund($order);
}

/**
 * 计算时间时分秒
 * @param $num 秒数
 * @return array
 */
function cacTime($num){
    $hour = floor($num/3600);
    $minute = floor(($num-3600*$hour)/60);
    $second = floor((($num-3600*$hour)-60*$minute)%60);
    return array(
        'hour' => $hour,
        'minute' => $minute,
        'second' => $second,
    );
}

/**
 * 共公函数库
 * @author yu
 */
function p($array)
{
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

/**
 * 统计二维数组中某个字段的总和
 * @param $new_array 新数组
 * @param $old_array 需要求和的数据
 * @return mixed
 */
function array_sum_column($new_array, $old_array)
{
    foreach ($old_array as $item => $value) {
        foreach ($value as $key => $val) {
            if (isset($new_array[$key]))
                $new_array[$key] += $val;
        }
    }
    return $new_array;
}

/**
 * 快速排序
 * @param $arr
 * @param $field
 * @return array
 */
function quickSort(&$arr, $field = 'results')
{
    if (count($arr) > 1) {
        $k = $arr[0][$field];
        $x = array();
        $y = array();
        $_size = count($arr);
        for ($i = 1; $i < $_size; $i++) {
            if ($arr[$i][$field] <= $k) {
                $x[] = $arr[$i];
            } elseif ($arr[$i][$field] > $k) {
                $y[] = $arr[$i];
            }
        }
        $x = quickSort($x, $field);
        $y = quickSort($y, $field);
        return array_merge($x, array($arr[0]), $y);
    } else {
        return $arr;
    }
}

function rsaSign($data, $private_key_path)
{
    $priKey = file_get_contents($private_key_path);
    $res = openssl_get_privatekey($priKey);
    openssl_sign($data, $sign, $res);
    openssl_free_key($res);
    //base64编码
    $sign = base64_encode($sign);
    return $sign;
}

//获取毫秒时间
function getMillisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}

//协程数组
function yieldArray($array)
{
    foreach ($array as $item => $value) {
        yield $item => $value;
    }
}

function bubbleSort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){
    if(is_array($arrays)){
        foreach ($arrays as $array){
            if(is_array($array)){
                $key_arrays[] = $array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
    return $arrays;
}

/**
 * 返回唯一小数
 * @return string
 */
function uniqid_num_float() {
    return sprintf('%01.4f',time()+microtime(true).rand(1,9));
}

/**
 * 手机号码加*隐藏
 * @param $data 集合
 * @return mixed
 */
function hidden_phone($data) {
    $len = strlen($data['phone']);
    $data['phone'] = substr_replace($data['phone'],"****",$len-($len-3),$len > 10 ? 4 : 0);
    return $data;
}

/*function bubbleSort($arr, $field = 'results', $direction = SORT_DESC)
{
    $arrSort = array();
    foreach($arr AS $uniqid => $row){
        foreach($row AS $key=>$value){
            $arrSort[$key][$uniqid] = $value;
        }
    }
//    return $arrSort['number'];
    return array_multisort($arrSort[$field], SORT_DESC,SORT_NUMERIC, $arr);
//    $len=count($arr);
//    //该层循环控制 需要冒泡的轮数
//    for($i=1;$i<$len;$i++)
//    { //该层循环用来控制每轮 冒出一个数 需要比较的次数
//        for($k=0;$k<$len-$i;$k++)
//        {
//            if($arr[$k][$field]>$arr[$k+1][$field])
//            {
//                $tmp=$arr[$k+1];
//                $arr[$k+1]=$arr[$k];
//                $arr[$k]=$tmp;
//            }
//        }
//    }
//    return $arr;
}*/

