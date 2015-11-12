<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

abstract class Kerisy_Application
{
	public function __construct()
	{
		$this->init();
	}
	
	public abstract function init();
	
	public abstract function run();
}