<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei<haoyf@putao.com>
 * Date: 2015/12/1
 * Time: 21:15
 */

namespace Kerisy\Contracts\Auth;


interface Authorizable
{
    public function can($ability, $arguments = []);
} 