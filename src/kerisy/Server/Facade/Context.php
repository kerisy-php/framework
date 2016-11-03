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

namespace Kerisy\Server\Facade;


use Kerisy\Support\Facade;

class Context extends Facade
{
    protected static function setFacadeAccessor()
    {
        return "context";
    }
}