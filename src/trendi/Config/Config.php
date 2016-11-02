<?php
/**
 * User: Peter Wang
 * Date: 16/9/9
 * Time: 上午11:44
 */

namespace Trendi\Config;

use Trendi\Config\Exception\DirNotFoundException;
use Trendi\Config\Exception\InvalidArgumentException;
use Trendi\Support\Arr;
use Trendi\Support\Dir;
use Trendi\Support\RunMode;

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