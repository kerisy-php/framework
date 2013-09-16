<?php

class Kerisy_BBCode_Tag_I extends Kerisy_BBCode_Tag
{
	public function __construct()
	{
		$this->_single = false;
		
		$this->_preg_rules = array(
			array('/<em[^>]*?>(.*)<\/em>/isU', '/<i[^>]*?>(.*)<\/i>/isU'),
			array('[i]\\1[/i]', '[i]\\1[/i]')
		);
	}
	
	public function getBeginHtml()
	{
		return "<em>";
	}
	
	public function getEndHtml()
	{
		return "</em>";
	}
}
