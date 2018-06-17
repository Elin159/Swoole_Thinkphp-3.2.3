<?php
/**
 * Redis 操作类
 * author: linhuanan
 */
namespace OtherSdk;

use Redis;
use Think\Exception;
use OtherSdk\SingerProvider;

class RedisProvider {
    private static $redisObj;
    private $host;
    private $port;
    private $password;
    private $select;
    use SingerProvider;

    private function __construct()
    {
        if(!is_object(self::$redisObj)) {
            $this->host     = REDIS_HOST;
            $this->port     = REDIS_PORT;
            $this->password = REDIS_PASSWORD;
            $this->select   = REDIS_DATABASE;
            $this->connect($this->host, $this->port, $this->password, $this->select);
        }
    }
    //------------字符串操作
    /**
     * 设置键值
     * @param $key 键
     * @param $value 值
     * @param int $existence 生存时间
     */
    private function set($key, $value, $existence = 0)
    {
        if(is_numeric($existence) && ($existence > 0)) {
            self::$redisObj->setex($key, $existence, $value);
        } else {
            self::$redisObj->set($key, $value);
        }
    }

    /**
     * 获取值
     * @param $key 键
     * @return mixed
     */
    private function get($key)
    {
        return self::$redisObj->get($key);
    }

    /**
     * 删除
     * @param int $key 键值
     * @return mixed
     */
    private function delete($key)
    {
        return self::$redisObj->delete($key);
    }

    /**
     * 判断键值是否存在
     * @param $key 键名
     * @return mixed
     */
    private function exists($key) {
        return self::$redisObj->exists($key);
    }
//------------------bit操作------------------

    /**
     * 设置位操作
     * @param $key string 位键值
     * @param $offset int 偏移量
     * @param $value 值
     * @return mixed
     */
    private function setBit(string $key,int $offset, $value) {
        return self::$redisObj->setBit($key, $offset, $value);
    }

    /**
     * 获取位操作
     * @param string $key 位键值
     * @param int $offset 偏移量
     * @return mixed
     */
    private function getBit(string $key,int $offset) {
        return self::$redisObj->getBit($key, $offset);
    }

    /**
     * 获取位范围为1的个数
     * @param $key 位键值
     * @param int $start 开始字节数
     * @param int $end 结束字节数
     * @return mixed
     */
    private function bitCount($key, $start, $end) {
        if(strlen($start) < 1 && strlen($end) < 1) {
            return self::$redisObj->bitCount($key);
        } else {
            return self::$redisObj->bitCount($key, $start, $end);
        }
    }

    /**
     * 符合操作位
     * @param $operation and(交集) or(并集) not(非) xor(异或)
     * @param $retKey 重新定义的键值
     * @param array $keys 需要比对的key数组
     * @return mixed
     */
    private function bitOp($operation, $retKey, array $keys) {
        $arr = [];
        $arr[] = $operation;
        $arr[] = $retKey;
        $arr = array_merge($arr,$keys) ;
        return call_user_func_array(array(self::$redisObj, bitOp),$arr);
//        return self::$redisObj->bitOp('XOR', 'cs_or', 'ranking1','ranking2');
    }

    /**
     * 根据查找bit的值，获取对应的用户
     * @param $cache_key 查找bit的值  redis::get('bit_key')
     * @return array
     */
    private function find_user_id($cache_key) {
        $get_value = $this->get($cache_key);
        $bitmap = unpack('C*', $get_value);
        $bit_user_id_array = [];
        $count = 0;
        foreach(yieldArray($bitmap) as $key => $number) {
            for($i = 7; $i >= 0; $i--) {
                if(($number >> $i & 1) == 1) {
//                    dump($number >> $i);
                    $bit_user_id_array[] = (8-($i+1)) + ($key-1) * 8;
                    $count++;
                }
            }
        }
        return $bit_user_id_array;
    }
//------------------有序集合操作--------------
    /**
     * 添加有序集合
     * @param string $key 键(列表)
     * @param int|double $score id
     * @param $member 值
     * @return mixed
     */
    private function zAdd($key,$score,$member) {
        return self::$redisObj->zAdd($key,$score,$member);
    }

    /**
     * 获取升序指定范围集合
     * @param string $key 有序集合key
     * @param int $start 开始
     * @param int $stop 结束
     * @param null $withScores 是否成员与core值返回
     * @return mixed
     */
    private function zRange($key, $start, $stop, $withScores = null) {
        return self::$redisObj->zRange($key, $start, $stop, $withScores);
    }

    /**
     * 返回有序集合key中,所有id值介于start和stop之间的元素
     * @param $key 有序集合Key
     * @param $start 开始区间
     * @param $stop 结束区间
     * @return mixed
     */
    private function zRangeByScore($key, $start, $stop) {
        return self::$redisObj->zRangeByScore($key, $start, $stop);
    }

    /**
     * 返回指定有序集合中对应元素的id
     * @param $key 有序集合key
     * @param $member 元素
     * @return mixed
     */
    private function zScore($key, $member) {
        return self::$redisObj->zScore($key, $member);
    }

    /**
     * 计算有序集合key中指定消息id区间的成员数量
     * @param $key 有序集合Key
     * @param $start 消息id 开始
     * @param $stop 结束
     * @return mixed
     */
    private function zCount($key, $start, $stop) {
        return self::$redisObj->zCount($key, $start, $stop);
    }

    /**
     * 删除指定元素
     * @param $key 有序集合key值
     * @param $member 需要删除的元素
     * @return mixed
     */
    private function zRem($key, $member)
    {
        return self::$redisObj->zRem($key, $member);
    }

    /**
     * 删除指定升序区间的元素
     * @param $key 有序集合key
     * @param $start 开始
     * @param $stop 结束
     * @return mixed
     */
    private function zRemRangeByRank($key, $start, $stop) {
        return self::$redisObj->zRemRangeByRank($key, $start, $stop);
    }

    /**
     * 链接redis
     * @param string $host 主机
     * @param int $port 端口
     * @param string $password 校验密码
     * @param int $database 数据库
     * @return Redis
     * @throws Exception
     */
    public function connect($host = REDIS_HOST, $port = REDIS_PORT, $password = REDIS_PASSWORD, $database = REDIS_DATABASE) {
        if(!is_object(self::$redisObj)) {
            $redis = new Redis();
            $redis->connect($host, $port);
            $result = $redis->auth($password);
            $redis->select($database);
            if($result) {
                self::$redisObj = $redis;
                return;
//                return self::$redisObj;
            } else {
                throw new Exception('链接错误');
            }
        }
//        else {
//            return self::$redisObj;
//        }
    }

    /**
     * 链接redis
     * @param string $host 主机
     * @param int $port 端口
     * @param string $password 校验密码
     * @param int $database 数据库
     * @return Redis
     * @throws Exception
     */
    public function pConnect($host = REDIS_HOST, $port = REDIS_PORT, $password = REDIS_PASSWORD, $database = REDIS_DATABASE) {
        if(!is_object(self::$redisObj)) {
            $redis = new Redis();
            $redis->pconnect($host, $port, 0);
            $result = $redis->auth($password);
            $redis->select($database);

            if($result) {
                self::$redisObj = $redis;
                return;
//                return self::$redisObj;
            } else {
                throw new Exception('链接错误');
            }
        }
//        else {
//            return self::$redisObj;
//        }
    }

    /**
     * 断开redis连接
     */
    private function close() {
        self::$redisObj->close();
    }
}