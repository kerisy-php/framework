<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Exception_401 extends Kerisy_Exception
{
	private $_code = 401;
	
	public function getExceptionCode()
	{
		return $_code;
	}

	/*public function __construct($message, $code = '10007')
	{
		request_Error($code, $message);
	}*/
}