<?php

namespace Swoole\Controller;


use Think\Controller;

class ClientController extends Controller {

    public $client;

    public function __construct()
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP);
//        $client = new \swoole_client(SWOOLE_SOCK_UDP);
        if (!$client->connect('120.78.50.220', 9501, -1))
        {
            exit("connect failed. Error: {$client->errCode}\n");
        }
        $this->client = $client;
    }

    public function getMessage() {
        $this->sendTcp();
//        $this->sendUdp();
        $this->close();
    }

    private function sendTcp() {
        $client = $this->client;
        $count = 0;
//$client->set(array('open_eof_check' => true, 'package_eof' => "\r\n\r\n"));
//$client = new swoole_client(SWOOLE_SOCK_UNIX_DGRAM, SWOOLE_SOCK_SYNC); //同步阻塞
//if (!$client->connect(dirname(__DIR__).'/server/svr.sock', 0, -1, 1))
        var_dump($client->getsockname());
        $client->send(json_encode(['action'=>'呵呵呵呵哈哈哈']));
//for($i=0; $i < 3; $i ++)
//        {
//            echo ($client->recv(8192,1)) ;
//            sleep(1);
//        }

        $this->client = $client;
        echo ($client->recv()) ;
    }

    private function sendUdp() {
        $client = $this->client;
        $count = 0;
//$client->set(array('open_eof_check' => true, 'package_eof' => "\r\n\r\n"));
//$client = new swoole_client(SWOOLE_SOCK_UNIX_DGRAM, SWOOLE_SOCK_SYNC); //同步阻塞
//if (!$client->connect(dirname(__DIR__).'/server/svr.sock', 0, -1, 1))
        var_dump($client->getsockname());
        $client->sendTo('120.78.50.220',9502,'呵呵呵呵');
//for($i=0; $i < 3; $i ++)
//        {
//            echo ($client->recv(8192,1)) ;
//            sleep(1);
//        }

        $this->client = $client;
        echo ($client->recv()) ;
    }

    private function close() {
        $this->client->close();
    }


}