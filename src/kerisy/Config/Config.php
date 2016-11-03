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

namespace Kerisy\Config;

use Kerisy\Config\Exception\DirNotFoundException;
use Kerisy\Config\Exception\InvalidArgumentException;
use Kerisy\Support\Arr;
use Kerisy\Support\Dir;
use Kerisy\Support\RunMode;

class Config implements ConfigInterface
{

    protected static $configPath = null;
    protected static $allConfig = [];

    /**
     *  设置配置路径
     * @param $path
     */
    public static function setConfigPath($path)
    {
        self::$configPath = Dir::formatPath($path);
    }

    /**
     * 获取所有配置
     * @return array
     * @throws DirNotFoundException
     */
    public static function getAll()
    {
        if (self::$allConfig) return self::$allConfig;

        if (!self::$configPath) {
            return [];
        }

        self::$allConfig = self::getDirAll(self::$configPath);

        return self::$allConfig;
    }


    /**
     * 根据dir获取配置
     * @param $dir
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected static function getDirAll($dir)
    {
        $dir = Dir::formatPath($dir);
        $sharePath = $dir . "share";
        $shareConfig = self::getDirConfig($sharePath);

        $env = RunMode::getEnv();
        $envPath = $dir . $env;
        $envConfig = self::getDirConfig($envPath);

        $config = Arr::merge($shareConfig, $envConfig);

        return $config;
    }

    /**
     * 获取目录配置
     *
     * @param $dir
     * @return array
     */
    protected static function getDirConfig($dir)
    {
        $dir = Dir::formatPath($dir);
        $config = [];

        if (is_dir($dir)) {
            $configFiles = Dir::glob($dir, '*.php', Dir::SCAN_BFS);
            foreach ($configFiles as $file) {
                $keyString = substr($file, strlen($dir), -4);
                if (preg_match("/_\w*/", $keyString)) continue;
                $loadedConfig = require_once($file);
                if($loadedConfig === true) continue;
                if (!is_array($loadedConfig)) {
                    throw new InvalidArgumentException("syntax error find in config file: " . $file);
                }

                $loadedConfig = Arr::createTreeByList(explode('/', $keyString), $loadedConfig);
                $config = Arr::merge($config, $loadedConfig);
            }
        }
        return $config;
    }

    /**
     * 设置配置
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {

        if (!self::$allConfig) {
            self::getAll();
        }

        Arr::set(self::$allConfig, $key, $value);
    }

    /**
     * 获取配置
     * @param $key
     * @param $default
     * @return array
     */
    public static function get($key, $default = null)
    {
        if (!self::$allConfig) {
            self::getAll();
        }
        if (!$key) {
            return $default;
        }
        $result = Arr::get(self::$allConfig, $key, $default);
        return $result;
    }

    public function __destruct()
    {
        self::$configPath = null;
    }
}