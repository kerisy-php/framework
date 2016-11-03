<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 上午9:46
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