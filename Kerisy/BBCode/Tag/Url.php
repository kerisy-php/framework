<?php

class Kerisy_BBCode_Tag_Url extends Kerisy_BBCode_Tag
{
	
	public function __construct()
	{
		$this->_single = false;
		$this->_effective = false;
		
		$this->_preg_rules = array(
			array('/<a\s+[^>]*?href=([\'\"])([^\'^\"]+)\\1[^>]*?>(.*)<\/a>/isU'),
			array('[url=\\2]\\3[/url]')
		);
	}

	public function convertToBBCode($content)
	{
		return preg_replace($this->_preg_rules[0], $this->_preg_rules[1], $content);
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
		
		if (preg_match("/=(['\"])?((https?|ftp|telnet|mms|rtsp|thunder|ed2k)?:\/\/[^'^\"]+)\\1?/", $string, $matches))
		{
			$this->_params['url'] = $matches[2];
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
		return "<a href=\"{$this->_params['url']}\" target=\"_blank\">";
	}
	
	public function getEndHtml()
	{
		return "</a>";
	}
}
