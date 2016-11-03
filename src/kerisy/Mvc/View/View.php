<?php
/**
 * 模板
 * 
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Mvc\View;

class View
{
    const DEFAULT_ENGINE = "blade";

    protected static $engine = self::DEFAULT_ENGINE;

    /**
     * 设置引擎
     *
     * @param $engine
     */
    public static function setEngine($engine)
    {
        self::$engine = $engine;
    }

    /**
     * 获取引擎对象
     *
     * @return mixed
     */
    public static function getViewObj()
    {
        $engine = ucfirst(self::$engine);
        $objstr = "Kerisy\\Mvc\\View\\Engine\\" . $engine;
        return $objstr::getInstance();
    }

}