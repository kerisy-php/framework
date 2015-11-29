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

use GuzzleHttp\Client as Gclient;

class Client {
    
    public function __construct(Gclient $client) {
        $this->client = $client;
    }

    public function get($url , $data = array())
    {
        return $this->request('GET', $url, $data);
    }

    public function post($url , $data = array())
    {    
        return $this->request('POST', $url, $data);
    }

    public function request($type = 'GET', $url, $data = array())
    {
        $fromData = array();

        if ( is_array($data) && count($data) > 0 )
        {
            if ( $type == 'GET' )
            {
                $fromData['query'] = $data;
            }
            else if ( $type == 'POST' )
            {
                $fromData['form_params'] = $data;
            }
           
        }

        $res = $this->client->request($type, $url, $fromData);
        return json_decode($res->getBody(), true);
    }

}

?>
