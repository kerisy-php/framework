<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Rest_Object
{
	public $_params = null;
	private $rest_server = null;

	public function setServerObject(&$object)
	{
		$this->rest_server = $object;
	}

	public function rest()
	{
		return $this->rest_server;
	}

	public function setParams($params)
	{
		$this->_params = $params;
		return $this;
	}
}