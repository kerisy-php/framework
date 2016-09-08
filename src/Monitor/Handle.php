<?php
/**
 * 监控数据发送工具
 *
 * User: wangkaihui
 * Date: 16/8/11
 * Time: 下午2:22
 */

namespace Kerisy\Monitor;

use Kerisy\Tool\RunTime;
use Kerisy\Core\Config;

class Handle
{

    /**
     * 发送监控数据
     *
     * @param $tag
     * @param $group
     * @param string $msg
     * @return bool
     */
    public function send($tag, $group, $msg = '', $diffTime='')
    {
        $configObj = new Config("config");
        $config = $configObj->get("monitor");

        if (!$config) return false;

        $obj = new Client($config['server']['host'], $config['server']['port']);

        if(!$diffTime){
            $diffTime = RunTime::runtime();
        }

        $str = [];
        $str['name'] = $config['name'];
        $str['group'] = $group;
        $str['tag'] = $tag;
        $str['diff_time'] = $diffTime;
        $str['time'] = time();
        $str['msg'] = $msg;

        $obj->send(json_encode($str));
    }

}