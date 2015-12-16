<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei<haoyf@putao.com>
 * Date: 2015/11/29
 * Time: 18:24
 */

namespace Kerisy\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

class PTCapsule extends Capsule
{

    public function __call($method, $parameters)
    {
        return call_user_func_array([static::connection(), $method], $parameters);
    }

}