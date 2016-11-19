<?php
/**
 * i18n 支持
 *
 */
namespace Kerisy\Support;

use Kerisy\Config\Config;

class Lang
{
    protected static $lang = "zh_cn";
    protected static $config=[];

    public static function setLang($lang)
    {
        self::$lang = $lang;
    }

    public static function get($string, $args=null){
        $key = "lang.".self::$lang.".".$string;
        $realStr = Config::get($key);
        if(!$realStr || is_array($realStr)) return ;

        $args = is_array($args)?$args:[$args];
        return vsprintf($realStr, $args);
    }
}

