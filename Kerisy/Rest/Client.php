<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Rest_Client {
	
	private $_api_server	= null;
	private $_username		= null;
	private $_password		= null;

	private $_curl_handle	= null;
	private $_headers		= array();
	private $_data_encode	= 'json';
	
	private $_send_api		= null;
	private $_send_url		= null;
	private $_send_method	= null;
	
	private $_debugMode		= false;
	private $_error_code	= 0;
	private $_error	= null;

	const POST   = 'POST';
	const GET    = 'GET';
	const PUT    = 'PUT';
	const DELETE = 'DELETE';

	const HTTP_OK		= 200;
	const HTTP_CREATED	= 201;
	const HTTP_ACEPTED	= 202;

	public function connect($api_server, $username = null, $password = null)
	{
		$this->_api_server = $api_server;
		$this->_username = $username;
		$this->_password = $password;
		
		$this->_headers = array(
						'Accept: application/json',
						'Content-Type: application/json',
					);
		return $this;
	}
	
	public function setDataEncode($encode)
	{
		if (in_array($encode, array('json', 'xml')))
		{
			$this->_data_encode = $encode;
		}
		return $this;
	}
	
	public function setDebugMode($mode)
	{
		$this->_debugMode = $mode;
		return $this;
	}
	
	public function post($api, $data)
	{
		return $this->send($api, self::POST, $data);
	}
	
	public function get($api, $data)
	{
		return $this->send($api, self::GET, $data);
	}
	
	public function put($api, $data)
	{
		return $this->send($api, self::PUT, $data);
	}
	
	public function delete($api, $data)
	{
		return $this->send($api, self::DELETE, $data);
	}
	
	public function send($api, $method, $data)
	{
		$data['client_name'] = $this->_username;
		$data['client_key'] = $this->_password;
		
		$this->setApiParam($api, $method);
		
		$this->_send_url = $this->getSendUrl();
		
		return $this->_send($method, $data);
	}
	
	private function setApiParam($api, $method)
	{
		if (empty($api) || empty($method))
		{
    		throw new Kerisy_Exception_500('Missing api\' method argument ..');
		}
		
		$this->_send_api	= $api;
		$thos->_send_method	= $method;
	}
	
	public function getSendUrl()
	{
		//return trim($this->_api_server, '/') . '?r=' . ltrim($this->_send_api, '/') . '&client_name=' . $this->_username. '&client_key='  . $this->_password;
		return trim(trim($this->_api_server, '/') . '/' . ltrim($this->_send_api, '/') , '/') . '?client_name=' . $this->_username. '&client_key='  . $this->_password;
	}
	
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
        return $this;
    }
	
	private function _send($method, $params)
	{
		$this->_curl_handle = curl_init();
		curl_setopt($this->_curl_handle, CURLOPT_URL, $this->_send_url);
		curl_setopt($this->_curl_handle, CURLOPT_HTTPHEADER, $this->_headers);
		curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		
		switch($method)
		{
			case self::GET:
				curl_setopt($this->_curl_handle, CURLOPT_URL, $this->_send_url . '&' . http_build_query($params));
				break;
			
			case self::POST:
				curl_setopt($this->_curl_handle, CURLOPT_POST, true);
				curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, http_build_query($params));
				
				break;
			
			case self::PUT:
				curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, http_build_query($params));
				break;
			
			case self::DELETE:
				curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, http_build_query($params));
				break;
		}
		
		$response	= curl_exec($this->_curl_handle);
        $status		= curl_getinfo($this->_curl_handle, CURLINFO_HTTP_CODE);

        
		curl_close($this->_curl_handle);
        
        switch ($status) {
            case self::HTTP_OK:
            case self::HTTP_CREATED:
            case self::HTTP_ACEPTED:
                break;
            default:
            	$this->setErrorCode($status);
                if ($this->_debugMode)
                {
                    throw new Client_Exception("Rest error: {$status}", $status);
                }
				return false;
				break;
        }

        if ('json' === $this->_data_encode)
        {
        	 if ($data = json_decode($response, true))
	        {
	        	return $data;
	        	
	        }
        }

        if ('pack' === $this->_data_encode)
        {
        	return kerisy_unpack($data);        	
        }
		$this->_error = 'Rest response data decode error!';
		$this->showError();
		return false;
	}

	private function setErrorCode($code)
	{
		$codes = array();
		
		// allowed status codes
		$codes[301] = '301 Moved Permanently';
		$codes[302] = '302 Found';
		$codes[304] = '304 Not Modified';
		$codes[307] = '307 Temporary Redirect';
		$codes[400] = '400 Bad Request';
		$codes[401] = '401 Unauthorized';
		$codes[403] = '403 Forbidden';
		$codes[404] = '404 Not Found';
		$codes[410] = '410 Gone';
		$codes[500] = '500 Internal Server Error';
		$codes[501] = '501 Not Implemented';

		if (isset($codes[$code]))
		{
			$this->_error = $codes[$code];
		}
	}

	public function setError($error)
	{
		$this->_error = $error;
	}
	
	public function getError()
	{
		return $this->_error;
	}
	
	public function showError()
	{
		if ($this->_debugMode)
		{
			if (null === $this->getError())
			{
				show_error($this->_error);
			}
			else
			{
				show_error('unknow error!!');
			}
		}
	}
}