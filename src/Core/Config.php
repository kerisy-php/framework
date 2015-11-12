<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 2015/11/10
 * Time: 13:51
 */

namespace Kerisy\Core;

class Config extends Set
{
    public function __construct($config_group)
    {
        $this->data = require CONFIG_PATH . $config_group . '.php';
    }
}
