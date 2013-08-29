<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Captcha
{
	private $_width = 100;
	private $_height = 28;
	private $_font_size = 18;
	
	private $_captcha_length = 4;
	private $_characters = 'abcdefghkmnprstuvwyzABCDEFGHKLMNPRSTUVWYZ23456789';
	private $_captcha = 'kerisy';
	
	private $_background_color = array('red' => 237, 'green' => 247, 'blue' => 255);
	protected $fonts = array();
	private $_img;
	
	public function __construct()
	{
		$this->setFonts();
	}
	
	public function generateCaptcha($captcha = '')
	{
		$this->_captcha = strlen($captcha) == 0 ? $this->generateCharacters() : $captcha;
		$this->_captcha_length = strlen($this->_captcha);
		$this->generateImage();
	}
	
	private function generateImage()
	{
		$this->_img = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($this->_width, $this->_height) : imagecreate($this->_width, $this->_height);
		$background_color = imagecolorallocate($this->_img, $this->_background_color['red'], $this->_background_color['green'], $this->_background_color['blue']);
		
		imagefilledrectangle($this->_img, 0, 0, $this->_width, $this->_height, $background_color);
		//background
	
		$characters_length = strlen($this->_characters) - 1;
		imageline($this->_img, mt_rand(1, $this->_width), mt_rand(1,$this->_height), mt_rand(1, $this->_width), mt_rand(1,$this->_height), imagecolorallocate($this->_img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255)));

		for ($i=1; $i<=10; $i++)
		{
			imagestring($this->_img, mt_rand(1, 7), mt_rand(1, $this->_width), mt_rand(1, $this->_height),$this->_characters[rand(1, $characters_length)], imagecolorallocate($this->_img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255)));
		} 
		
		//font
		$x = $this->_width / $this->_captcha_length;
		$font_count = count($this->fonts);
		
		for ($j = 0; $j < $this->_captcha_length; $j++) {
			imagettftext($this->_img, $this->_font_size, rand(-30, 30), $x*$j+ rand(0, 5), $this->_height/1.4, imagecolorallocate($this->_img, rand(0, 255), rand(0, 255), rand(0, 255)), $this->fonts[mt_rand(0, $font_count-1)], $this->_captcha[$j]);
			
		}
		//杂点 杂线
		for ($j2 = 0; $j2 < 5; $j2++) {
			$color = imagecolorallocate($this->_img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			imagesetpixel($this->_img, mt_rand(0, $this->_width), mt_rand(0, $this->_height), $color);
			imageline($this->_img, mt_rand(0, $this->_width), mt_rand(0, $this->_height), mt_rand(0, $this->_width), mt_rand(0, $this->_height), $color);
		}
		
		$this->outputImage();
	}
	
	private function outputImage()
	{
		header('Content-type:image/png');
		imagepng($this->_img);
		imagedestroy($this->_img);
	}
	
	private function generateCharacters()
	{
		$tmp_captcha = '';
		$characers_length = strlen($this->_characters);
		
		for ($i = 0; $i < $this->_captcha_length; $i++) {
			
			$tmp_captcha .= $this->_characters[rand(0, $characers_length-1)];	
		}
		
		return $tmp_captcha;
	}
	
	public function getCaptcha()
	{
		return strtolower($this->_captcha);
	}
	
	public function setCaptcha($captcha)
	{
		$this->_captcha = $captcha;
	}
	
	public function setWidth($width = 100)
	{
		$this->_width = $width;
	}

	public function setBackgroundColor($color)
	{
		$this->_background_color = $color;
	}
	
	public function setHeight($height = 28)
	{
		$this->_height = $height;
	}
	
	public function setFontSize($fontSize = 28)
	{
		$this->_font_size = $fontSize;
	}
	
	public function setFonts()
	{
		$this->fonts = array();
		for ($i = 1; $i < 6; $i++) {
			$this->fonts[] = BASE_DIR . DS . 'Library/Kerisy/font/' . $i . '.ttf';
		}
		return $this;
	}
}