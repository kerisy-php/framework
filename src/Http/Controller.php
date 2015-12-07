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

class Controller
{
    private $_user_id;


//    private $_request;
//    
//    public function before($request)
//    {
//        $this->_request = $request;
//    }
//    
//    public function after($response)
//    {
//        return $request;
//    }
    public function userId()
    {
        return $this->_user_id;
    }

    public function callMiddleware(){}
}
