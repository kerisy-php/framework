<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午6:49
 */

namespace Kerisy\Server;


interface SocketInterface
{
    /**
     *  数据初始化
     *
     * @return mixed
     */
    public function bootstrap();

    /**
     *  receive 执行函数
     *
     * @param $data
     * @param $serv
     * @param $fd
     * @param $from_id
     * @return mixed
     */
    public function perform($data, $serv, $fd, $from_id);

}