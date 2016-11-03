<?php
/**
 *  job 服务初始化
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

use Kerisy\Config\Config;
use Kerisy\Job\Job;

class JobBootstrap extends Job
{
    public function __construct()
    {
        $config = Config::get("server.job");
        parent::__construct($config);
    }
}