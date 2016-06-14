<?php
/**
 * Kerisy Framework
 * 
 * PHP Version 7
 * 
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Log
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Log;

use Monolog\Handler\SyslogUdpHandler;

/**
 * Class StreamTarget
 *
 * @package Kerisy\Log
 */
class SyslogUdpTarget extends Target
{
    /**
     * The stream to logging into.
     *
     * @var resource|string
     */
    public $host;
    public $port;

    protected $handler;

    public function getUnderlyingHandler()
    {
        if (!$this->handler) {
            $this->handler = new SyslogUdpHandler($this->host, $this->port, LOG_USER, $this->level, true);
        }
        return $this->handler;
    }
}
