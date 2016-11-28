<?php
/**
 * runmode 环境切换
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Support;


class RunMode
{
    const RUN_MODE_TEST = "test";
    const RUN_MODE_ONLINE = "online";

    private static $runMode = null;
    private static $env = null;


    /**
     * 获取执行模式,影响调试
     * @return int
     */
    public static function getRunMode()
    {
        return self::$runMode?self::$runMode:self::RUN_MODE_ONLINE;
    }

    /**
     * 获取执行环境,影响config
     * @return string
     */
    public static function getEnv()
    {
        return self::$env?self::$env:self::RUN_MODE_ONLINE;
    }

    /**
     *  初始化
     * @return string
     * @throws \EnvInvalidException
     */
    public static function init()
    {
        if (self::$runMode) return self::$runMode;
        $env = getenv("KERISY_RUNMODE");//test.dev
        if (!$env) {
            $env = get_cfg_var("KERISY_RUNMODE");
        }

        if(defined("KERISY_RUNMODE")){
            $env = KERISY_RUNMODE;
        }

        if ($env) {
            $envArr = explode(".", $env);

            if (count($envArr) < 2) {
                throw new \EnvInvalidException(" 环境设置错误, 需要用test.developer 类似设置~");
            }

            if (!in_array($envArr[0], [self::RUN_MODE_TEST, self::RUN_MODE_ONLINE])) {
                throw new \EnvInvalidException(" 运行模式只能是 " . self::RUN_MODE_TEST . "," . self::RUN_MODE_ONLINE . "~");
            }

            self::$runMode = $envArr[0];
            self::$env = $envArr[1];
        }

        !self::$runMode && self::$runMode= self::RUN_MODE_ONLINE;
        !self::$env && self::$env= self::RUN_MODE_ONLINE;
    }
}