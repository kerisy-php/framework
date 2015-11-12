<?php

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
}
