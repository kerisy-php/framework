<?php
/**
 *  session handle
 *
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Foundation\Bootstrap;

use Kerisy\Foundation\Storage\Redis;
use Kerisy\Config\Config;
use Kerisy\Http\Session as HttpSession;

class Session extends HttpSession
{
    protected static $instance = null;
    
    public static function getInstance()
    {
      if(self::$instance) return self::$instance;
       return self::$instance = new self();
    }
    
    public function __construct()
    {
        $serverConfig = Config::get("storage.server.redis.servers");

        if($serverConfig){
            $config = Config::get("app.session");
            $server = new Redis();
            parent::__construct($config, $server);
        }
    }

}