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

namespace Kerisy\Foundation\Bootstrap\Facade;


use Kerisy\Support\Facade;

class Session extends Facade
{
    protected static function setFacadeAccessor()
    {
        return "session";
    }
}