<?php

class Kerisy_BBCode_Tag_Color extends Kerisy_BBCode_Tag
{
	public function __construct()
	{
		$this->_single = false;

		$this->_preg_rules = array(
				array('/<(\w+)\s+[^>]*?style=([\'\"])color:\s*?([^;]+)\\2[^>]*?>(.*)<\/\\1>/isU', '/<font\s+[^>]*?color=([\'\"])?([^\'\"]+)\\1?[^>]*?>(.*)<\/font>/isU'),
				array('[color=\\3]\\4[/color]', '[color=\\2]\\3[/color]')
		);
	}

	public function setParamsFromString($string)
	{
		if ($this->_close_tag)
		{
			$this->_effective = true;
			return $this;
		}
		
		if (!$string)
		{
			$this->_effective = false;
			return $this;
		}
		
		if (preg_match("/=(['\"])?([^'^\"]+)\\1?/", $string, $matches))
		{
			$this->_params['color'] = $matches[2];
			$this->_effective = true;
		}
		else
		{
			$this->_effective = false;
		}
		
		return $this;
	}
	
	public function getBeginHtml()
	{
		return "<span style=\"color:{$this->_params['color']}\">";
	}
	
	public function getEndHtml()
	{
		return "</span>";
	}
}
