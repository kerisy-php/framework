<?php
/**
 * Kerisy Framework
 * 
 * PHP Version 7
 * 
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Server
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Server;

use Kerisy\Core\Object;
use Kerisy\Core\Application;

/**
 * The base class for Application Server.
 *
 * @package Kerisy\Server
 */
abstract class Base extends Object
{
    public $host = '0.0.0.0';
    public $port = 8888;

    public $name = 'kerisy-server';
    public $pidFile;

    /**
     * A php file that application will boot from.
     *
     * @var string
     */
    public $bootstrap;

    public function startApp()
    {
        if ($this->bootstrap instanceof Application) {
            $app = $this->bootstrap;
        } else if (is_array($this->bootstrap)) {
            $app = new Application($this->bootstrap);
        } else {
            $app = require $this->bootstrap;
        }

        $app->bootstrap();
    }

    public function stopApp()
    {
        app()->shutdown();
    }

    public function handleRequest($request)
    {
        return app()->handleRequest($request);
    }

    abstract public function run();
}
