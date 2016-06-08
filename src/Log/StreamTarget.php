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

use Monolog\Handler\StreamHandler;

/**
 * Class StreamTarget
 *
 * @package Kerisy\Log
 */
class StreamTarget extends Target
{
    /**
     * The stream to logging into.
     *
     * @var resource|string
     */
    public $stream;

    protected $handler;

    public function getUnderlyingHandler()
    {
        if (!$this->handler) {
            $this->handler = new StreamHandler($this->stream, $this->level, true, null, true);
        }
        return $this->handler;
    }
}
