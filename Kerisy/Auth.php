<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Auth
{
	protected $session_id;
	
	protected $user_id;
	protected $user_name;
	protected $user_hash;
	protected $user_info;
	
	protected $hash_string;
	
	protected $cookie_config;
	
	public function __construct()
	{
		$this->cookie_config = Kerisy::config()->get()->cookie;
		$this->hash_string = $this->cookie_config['hash_string'];
		$this->user_id = $this->getUserId();
		$this->user_name = $this->getUserNickname();
		$this->user_hash = $this->getUserHash();
		$this->session_id = $this->getSessionId();
		$this->user_info = $this->getUserInfo();
		
	}
	//需要在子类中重写
	public function getUserInfo()
    {
    	return $this->user_info;
    }
	public function getUserId()
    {
    	$cookie_key = $this->cookie_config['prefix'] . $this->cookie_config['user_id_key'];
    	return $_COOKIE[$cookie_key];
    }
	public function getUserNickname()
    {
    	$cookie_key = $this->cookie_config['prefix'] . $this->cookie_config['user_name_key'];
    	return $_COOKIE[$cookie_key];
    }
	public function getUserHash()
    {
    	$cookie_key = $this->cookie_config['prefix'] . $this->cookie_config['user_hash_key'];
    	return $_COOKIE[$cookie_key];
    }
	public function getSessionId()
    {
    	$cookie_key = $this->cookie_config['prefix'] . $this->cookie_config['session_id_key'];
    	return $_COOKIE[$cookie_key];
    }
}
