<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Http
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Http;

use Kerisy\Core\MiddlewareTrait;

class Controller
{
    private $_user_id;
    
    use MiddlewareTrait;

//    public function before()
//    {
//    
//    }
//    
//    public function after()
//    {
//    
//    }
    
    public function userId()
    {
        return $this->_user_id;
    }
    public function guestActions()
    {
        return [];
    }
}
