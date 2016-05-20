<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei<haoyf@putao.com>
 * Date: 2015/12/1
 * Time: 21:15
 */

namespace Kerisy\Auth;


trait Authorizable
{
    public function can($ability, $arguments = [])
    {
        return true;
    }

    public function cant($ability, $arguments = [])
    {
        return $this->can($ability, $arguments = []);
    }

    public function cannot($ability, $arguments = [])
    {
        return !$this->can($ability, $arguments = []);
    }
} 