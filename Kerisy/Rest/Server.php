<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Rest_Server
{
	private $_user_id		= 0;
	private $_client_id		= 0;
	private $_rest_object	= null;
	private $_rest_method	= null;
	private $_encode_format	= 'pack';
	private $_request_data	= null;
	private $_allowd_ip		= array('127.0.0.1');
	
	public function __construct()
	{
	}

	public function setUserId($user_id)
	{
		$this->_user_id = $user_id;
		return $this;
	}

	public function userId()
	{
		return $this->_user_id;
	}

	public function setClientId($client_id)
	{
		$this->_client_id = $client_id;
	}

	public function clientId()
	{
		return $this->_client_id;
	}
	
	public function setRestObject(&$object)
	{
		$this->checkObject($object);

		$this->_rest_object =& $object;
		$this->_rest_object->setServerObject($this);
		return $this;
	}
	
	public function setAllowIpList($ip_array = array())
	{
		$this->_allowd_ip = array_merge($this->_allowd_ip, $ip_array);
	}

	public function checkIpAllowd()
	{
		if (!in_array(ip(), $this->_allowd_ip))
		{
			$this->returnError('不允许的IP调用');
		}
	}
	
	public function checkObject($object)
	{
		if (!is_object($object))
		{
			$this->returnError('缺少REST接口对象');
		}
	}
	
	public function setRestMethod($method)
	{
		$this->checkObject($this->_rest_object);
		
		if (!method_exists($this->_rest_object, $method . '_rest_method'))
		{
			$this->returnError('接口(' . $method . ')不存在');
		}
		
		$this->_rest_method = $method . '_rest_method';
		return $this;
	}
	
	public function handle()
	{
	}
	
	public function run()
	{
		$this->_rest_object->setParams($this->getRequestData());
		$result = $this->_rest_object->{$this->_rest_method}();
		
		$this->returnSuccess($result);
	}
	
	public function setEncodeFormat($format)
	{
		if (in_array($format, array('json', 'pack')))
		{
			$this->_encode_format = $format;
			return $this;
		}
	}
		//$this->returnError('API目前不支持' . $format . '格式的返回数据。');
	
	private function getRequestData()
	{
		switch ($_SERVER['REQUEST_METHOD'])
		{
			case 'GET':
				$this->_request_data = $_GET;
				break;
			case 'PUT':
			case 'POST':
			case 'DELETE':
			default:
				parse_str(file_get_contents('php://input'), $this->_request_data);
		}
		return $this->_request_data;
	}
	
	private function returnSuccess($data, $message = null)
	{
		array_unshift($data, '0');
		$this->returnData($data);
	}
	
	public function returnError($error, $error_code = 10000, $http_code = 400)
	{
		$return_data = array();
		
		if ('pack' === $this->_encode_format)
		{
			$return_data[]	= $error_code;			
			$this->returnData($return_data);
		}
	
		if ('json' === $this->_encode_format)
		{
			$return_data['status']		= 'error';
			$return_data['code']		= $error_code;
			$return_data['message']		= $error;
			$return_data['request']		= preg_replace('/\?.*+$/', '', $_SERVER['REQUEST_URI']);
			$this->returnData($return_data);
		}
		//$return_data['error']		= $error;
		//$return_data['request']		= preg_replace('/\?.*+$/', '', $_SERVER['REQUEST_URI']);

		// 发送HTTP代码给客户端
		//header_httpcode($http_code);

	}
	
	private function returnData($data)
	{
		if ('pack' === $this->_encode_format)
		{
			echo kerisy_pack($data);
			exit();
		}
		
		if ('json' === $this->_encode_format)
		{
			echo json_encode($data);
			exit();
		}
	}
}