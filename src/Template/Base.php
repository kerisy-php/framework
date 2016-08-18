<?php
/**
 * 基础类
 *
 * User: wangkaihui
 * Date: 16/8/10
 * Time: 下午8:20
 */

namespace Kerisy\Template;


abstract class Base
{
    /**
     * 返回html
     *
     * @param $data array
     * @return String
     */
    public abstract function render($data);

    /**
     * 模板路径处理
     *
     * @param $tplPath string
     * @param  $viewObj \Kerisy\Http\View
     * @return String
     */
    public abstract function path($tplPath, $viewObj = null);
}