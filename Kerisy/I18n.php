<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

abstract class Kerisy_I18n
{
	protected $language = '1';
	protected $package = '1';
	protected static $language_table = array();
	
	public function __construct()
	{
		
	}
	
	public abstract function get($key, $package, $language = 'zh-CN');
}