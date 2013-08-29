<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

abstract class Kerisy_Exception extends Exception
{
	private $_code = 500;
	
	public static $php_errors = array(
		E_ERROR              => 'Fatal Error',
		E_USER_ERROR         => 'User Error',
		E_PARSE              => 'Parse Error',
		E_WARNING            => 'Warning',
		E_USER_WARNING       => 'User Warning',
		E_STRICT             => 'Strict',
		E_NOTICE             => 'Notice',
		E_RECOVERABLE_ERROR  => 'Recoverable Error',
	);
		
	public function getExceptionCode()
	{
		return $this->_code;
	}
	
	public function __construct($message, $variables = NULL, $code = 0)
	{
		if (defined('E_DEPRECATED'))
		{
			self::$php_errors[E_DEPRECATED] = 'Deprecated';
		}

		if($variables)
		{
			if(is_array($variables))
			{
				foreach ($variables as $key => $value)
				{
					$message = preg_replace("/{$key}/", $value, $message);
				}
			}
		}
		parent::__construct($message, $this->getExceptionCode());		
	}
	
	public function handler(Exception $e)
	{
		try
		{
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();

			$trace = $e->getTrace();

			if ($e instanceof ErrorException)
			{
				if (isset(Kerisy_Exception::$php_errors[$code]))
				{
					$code = Kerisy_Exception::$php_errors[$code];
				}

				if (version_compare(PHP_VERSION, '5.3', '<'))
				{
					for ($i = count($trace) - 1; $i > 0; --$i)
					{
						if (isset($trace[$i - 1]['args']))
						{
							$trace[$i]['args'] = $trace[$i - 1]['args'];
							unset($trace[$i - 1]['args']);
						}
					}
				}
			}

			if ( ! headers_sent())
			{
				$http_header_status = ($e instanceof HTTP_Exception) ? $code : 500;

				header('Content-type:text/html; charset=utf-8', true, $http_header_status);
			}
			
			ob_start();

			Kerisy::import('Kerisy.Debug');
			include KERISY_PATH . 'Kerisy/templates/errors/error.php';
			
			echo ob_get_clean();
		}
		catch (Exception $e)
		{

		}
	}
	
	public function error_handler($code, $error, $file = NULL, $line = NULL)
	{
		if (error_reporting() & $code)
		{
			throw new ErrorException($error, $code, 0, $file, $line);
		}

		return true;
	}
}