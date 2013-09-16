<?php

class Kerisy_BBCode_Tag_Code extends Kerisy_BBCode_Tag
{
	public function __construct()
	{
		$this->_single = false;
		
		$this->_ignore_bbcode = true;
		
		$this->_preg_rules = array(
			array('/<div\s+[^>]*?bbcode=([\'\"])code\\1[^>]*?>(.*)<\/div>/isU'),
			array('[code]\\3[/code]')
		);
	}

	public function parseContent($content)
	{
		return $content;
	}
	
	public function getBeginHtml()
	{
		return "<ol class=\"code\">";
	}
	
	public function getEndHtml()
	{
		return "</ol>";
	}
	
	public function getTagContent()
	{
		$content = '';
		$tag_content = preg_replace('/<br[^>]*?>/U', "\n", $this->_tag_content);
		
		foreach (explode("\n", htmlspecialchars(trim($tag_content))) as $line)
		{
			$content .= "<li>{$line}</li>\n";
		}
		
		return $content;
	}
}
