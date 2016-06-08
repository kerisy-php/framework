<?php
/**
 * Kerisy Framework
 * 
 * PHP Version 7
 * 
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Core
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Core;

class Config extends Set
{
    public function __construct($config_group)
    {
        $ext_name = '.php';
        
        $config_file = CONFIG_PATH . $config_group . $ext_name;
        /* 环境变量加载不同扩展名的配置文件 */
        $env_ext_name = (KERISY_ENV == 'development' ? '.dev' : (KERISY_ENV == 'test' ? '.test' : '')) . $ext_name;
        $env_config_file = CONFIG_PATH . $config_group . $env_ext_name;
        
        /* ENV配置文件不存在的情况下默认加载正式环境配置文件 */
        $config_file = file_exists($env_config_file) ? $env_config_file : $config_file;
        
        $this->data = require $config_file;
    }
}
