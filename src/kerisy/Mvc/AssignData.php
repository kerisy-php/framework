<?php
/**
 * User: Peter Wang
 * Date: 16/9/30
 * Time: 下午5:07
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