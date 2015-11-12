<?php

class Kerisy_BBCode_Tag_Quote extends Kerisy_BBCode_Tag
{
	public function __construct()
	{
		$this->_single = false;
	}
	
	public function getBeginHtml()
	{
		return "<div class=\"quote\">";
	}
	
	public function getEndHtml()
	{
		return "</div>";
	}
}
