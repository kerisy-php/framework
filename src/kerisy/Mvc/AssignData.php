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

namespace Kerisy\Mvc;


class AssignData
{
    protected $assignData = [];

    public function getAssignData()
    {
        return $this->assignData;
    }

    public function __set($name, $value)
    {
        $this->assignData[$name] = $value;
    }
}