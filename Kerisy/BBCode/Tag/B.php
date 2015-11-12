<?php

class Kerisy_BBCode_Tag_B extends Kerisy_BBCode_Tag
{
	public function __construct()
	{
		$this->_single = false;
		
		$this->_preg_rules = array(
			array('/<strong[^>]*?>(.*)<\/strong>/isU'),
			array('[b]\\1[/b]')
		);
	}

	public function getBeginHtml()
	{
		return "<strong>";
	}
	
	public function getEndHtml()
	{
		return "</strong>";
	}
}
