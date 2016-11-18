<?php
/**
 * i18n 支持
 *
 */
namespace Kerisy\Lang;

class Lang
{
    protected static $lang = "zh_cn";
    protected static $path=APPLICATION_PATH . 'config/lang/';
    protected static $config=[];

    public static function setPath($path)
    {
        self::$path = $path;
    }

    public static function setLang($lang)
    {
        self::$lang = $lang;
    }

    public function __construct()
    {
        $this->init();
        class_alias(\Kerisy\Lang\Lang::class,"Lang");
    }

    protected function init()
    {
        if(!self::$path) return ;
        $file = glob(self::$path."*.php");
        if(!$file) return ;
        foreach ($file as $v){
            $pathinfo = pathinfo($v);
            $fileName = $pathinfo["filename"];
            self::$config[$fileName] = include_once($v);
        }
    }

    public static function get($string, $args){
        $arr = explode(".",$string);
        $realStr = self::$config[self::$lang];
        foreach ($arr as $v){
            $realStr = isset($realStr[$v])?$realStr[$v]:null;
            if(!$realStr) break;
        }
        if(!$realStr) return null;
        $args = is_array($args)?current($args):$args;
        return vsprintf($realStr, $args);
    }
}

