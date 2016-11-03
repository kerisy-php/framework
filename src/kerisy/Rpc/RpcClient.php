<?php
/**
 * rpc client
 * User: Peter Wang
 * Date: 16/9/19
 * Time: 下午2:13
 */

namespace Kerisy\Rpc;


use Kerisy\Server\SocketClient;
use Kerisy\Support\Arr;
use Kerisy\Support\Serialization\Serialization;

class RpcClient
{

    private $client = null;

    public function __construct($host, $port, $serialization = 1, $diyConfig = [])
    {
        $config = [
            "host" => "127.0.0.1",
            "port" => "9000",
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 2000000,
            "serialization" => 1,
            "timeout" => 3,
            "alway_keep" => false,
        ];

        $config = Arr::merge($config, $diyConfig);

        $config['host'] = $host;
        $config['port'] = $port;
        $config['serialization'] = $serialization;

        $serialization = Serialization::get($config['serialization']);

        $serialization->setBodyOffset($config['package_body_offset']);
        $client = new \swoole_client($config['alway_keep'] ? SWOOLE_SOCK_TCP | SWOOLE_KEEP : SWOOLE_TCP);
        $this->client = new SocketClient($client, $config, $serialization);
    }

    /**
     * 获取数据
     * 
     * @param $url
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function get($url, $params = [])
    {
        $result = [$url, $params];
        return $this->client->sendAndRecvice($result);
    }

    public function close()
    {
        $this->client->close();
    }

    public function __destruct()
    {
        $this->close();
    }
}