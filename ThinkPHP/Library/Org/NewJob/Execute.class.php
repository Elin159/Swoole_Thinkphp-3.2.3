<?php
/**
 * Created by PhpStorm.
 * User: MACHENIKE
 * Date: 2018/3/26
 * Time: 21:44
 */

namespace Org\NewJob;

use OtherSdk\RedisProvider;
use Think\Exception;

abstract class Execute implements Queue{

    public $host = REDIS_HOST;
    public $port = REDIS_PORT;
    public $auth = REDIS_PASSWORD;
    public $database = REDIS_DATABASE;

    function __construct() {
        RedisProvider::Connect($this->host, $this->port, $this->auth, $this->database);
    }

    /**
     * 压入队列
     * @param string $listen_key 队列名称
     * @param $message 队列内容
     * @param string $id 队列id
     * @return bool|string
     */
    public function press($listen_key = 'default', $message, $id = '')
    {
        // TODO: Implement press() method.
        if(strlen($id) < 1 || !is_numeric($id) || $id == 0) {
            $id = time()+microtime().rand(0,9);
        }

        $result = RedisProvider::zAdd($listen_key, $id, $message);
        if(!$result) {
            return false;
        }
        return $id;
    }

    /**
     * 删除指定队列的指定内容
     * @param string $listen_key 消息队列
     * @param $message 对应的消息内容
     * @throws Exception
     */
    public function flush($listen_key = 'default', $message) {
        // TODO: Implement flush() method.

        $result = RedisProvider::zRem($listen_key, $message);
        if(!$result) {
            throw new Exception('删除失败');
        }
    }

    /**
     * 根据消息id在有序集合key中查找对应的元素
     * @param string $listen_key 有序集合key
     * @param $id 消息id
     * @return mixed
     */
    public function gain($listen_key = 'default', $id) {
        // TODO: Implement gain() method.
        $result = RedisProvider::zRangeByScore($listen_key, $id, $id);
        return $result[0];
    }

    /**
     * 核心执行代码
     * @param string $list_key 执行的队列
     */
    public function realize($list_key = 'default')
    {
        // TODO: Implement realize() method.
        $count = RedisProvider::zCount($list_key, 0, 9000000000);
        $redisInfo = RedisProvider::zRange($list_key,0,-1);

        if($count > 0) {
            $time = time();
            foreach(yieldArray($redisInfo) as $key => $value) {
                $id = RedisProvider::zScore($list_key, $value);
                $content = json_decode($value, true);
                if($content['delay'] > 0) {
                    $delayTime = $content['add_time'] + $content['delay'] > $time;
                    if($delayTime) {

                        continue;
                    }
                    $content['delay'] = 0;
                }

                if($content['delay'] < 0) {
                    $content['delay'] = 0;
                }

                if( $content['listKey'] == $list_key && $content['delay'] == 0 ) {
                    if($content['attempts'] == 0 || $content['attempts'] > $content['run']) {
                        $content['run']++;
                        $job = unserialize($content['job']);
                        try {
                            $job->handel();
                            $this->flush($list_key, $value);
                        } catch (Exception $e) {
                            file_put_contents($list_key.'_job_.log', '['.date('Y-m-d H:i:s', time()).'] -- '.$e->getMessage().PHP_EOL, FILE_APPEND);
                            //只有尝试次数大于0时，执行出错才会重新放进队列
                            $this->flush($list_key, $value);
                            if(($content['attempts'] > $content['run'])) {
                                $this->press($list_key, json_encode($content), $id);
                            }
                        }
                    }
                    else { //运行次数等于可尝试次数，这时候就丢弃
                        $this->flush($list_key, $value);
                    }
                }
            }
        }
    }

}