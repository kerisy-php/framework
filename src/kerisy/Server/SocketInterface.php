<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
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