<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 上午9:46
 */

namespace Trendi\Foundation\Bootstrap\Facade;


use Trendi\Support\Facade;

class Job extends Facade
{
    protected static function setFacadeAccessor()
    {
        return "job";
    }
}