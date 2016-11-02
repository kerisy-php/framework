<?php
/**
 *  session handle
 *
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午5:40
 */

namespace Trendi\Foundation\Bootstrap;

use Trendi\Foundation\Storage\Redis;
use Trendi\Config\Config;
use Trendi\Http\Session as HttpSession;

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
        $config = Config::get("app.session");
        $serverConfig = Config::get("storage.redis.servers");
        if($serverConfig){
            $server = new Redis();
            parent::__construct($config, $server);
        }
    }

}