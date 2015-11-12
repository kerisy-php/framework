<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Autoload
{
	private $_classes = array();
	
    public function __construct()
    {
    	ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array($this, 'loader'));
    }

    private function loader($class)
    {
        $file = preg_replace('/_+/', '/', $class) . '.php';
		echo $file ,'<br />';
   		if(!@include_once($file))
		{
			throw new Kerisy_Exception_404('Not found class: ' . $class);
		}
        include_once $file;
    }
}
