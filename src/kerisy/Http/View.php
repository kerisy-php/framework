<?php
/**
 *  模板变量保存
 *
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午6:51
 */

namespace Kerisy\Http;


class View
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