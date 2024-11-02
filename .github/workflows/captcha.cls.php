<?php



define("CAPTCHA_COOKIE", "xz_captcha_code");


class captcha
{
	var $image_width = 133;
	var $image_height = 28;
	
	var $code_length = 6;
	var $grid_spacing = 7;

	var $bg_color		= "FF FF FF";
	var $text_color		= "00 00 00";
	var $grid_color		= "D0 D0 D0";
	var $border_color	= "00 00 00";

	var $font = 5;

	var $bad_chars = "OD0";
	var $bad_char_replacement = "X";

	//-------------------------------------------

	var $name;
	var $code;
	var $code_enc;
	var $img;
	var $instance;
	var $cookie_name;

	var $clrBg;
	var $clrTxt;
	var $clrGrd;
	var $clrBdr;

	//-------------------------------------------

	function captcha($name="default")
	{
		$this->name = $name;
		$this->bad_chars = strtoupper($this->bad_chars);
	}

	function verify($code, $resetAfterVerify = TRUE)
	{
		if($this->_enc($code) == $_COOKIE[CAPTCHA_COOKIE]) $result = TRUE;
		else $result = FALSE;
		
		if ($resetAfterVerify) {
			$this->resetCookie();
		}
		
		return $result;
	}

	function image()
	{
		$this->generateImage();
		$this->outputImage();
		$this->destroyImage();
	}
	
	function outputImage()
	{
		header("Content-type: image/png");
		imagepng($this->img);
	}

	function generateImage()
	{
		// Create blank image
		$this->img = imagecreate($this->image_width, $this->image_height);


		// Allocate colors
		$this->clrBg	= $this->_allocColor($this->bg_color);
		$this->clrTxt	= $this->_allocColor($this->text_color);
		$this->clrGrd	= $this->_allocColor($this->grid_color);
		$this->clrBdr	= $this->_allocColor($this->border_color);


		// Fill background
		imagefill($this->img, 0, 0, $this->clrBg);


		// Draw border
		imagerectangle($this->img, 0, 0, $this->image_width-1, $this->image_height-1, $this->clrBdr);


		// Draw grid
		for($i=$this->grid_spacing; $i<$this->image_height-2; $i=$i+$this->grid_spacing)
			imageline($this->img, 1, $i, $this->image_width-2, $i, $this->clrGrd);

		for($i=$this->grid_spacing; $i<$this->image_width-2; $i=$i+$this->grid_spacing)
			imageline($this->img, $i, 1, $i, $this->image_height-2, $this->clrGrd);


		// Generate code and calculate position for text
		$this->_generateCode();

		$codetoprint = $this->_spaceText($this->code);
		$x = ($this->image_width - imagefontwidth($this->font)*strlen($codetoprint))/2;
		$y = ($this->image_height - imagefontheight($this->font))/2;


		// Draw Text
		imagestring($this->img, $this->font, $x, $y, $codetoprint, $this->clrTxt);

	}

	function destroyImage()
	{
		imagedestroy($this->img);

		$this->img		= "";
		$this->code		= "";
		$this->code_enc = "";
	}

	function resetCookie()
	{
		clearSalt($this->name);
		setcookie(CAPTCHA_COOKIE, "", 0, "/");
	}

	//-------------------------------------------

	function _generateCode()
	{
		$code = strtoupper(substr(md5(uniqid($_SERVER['REMOTE_ADDR'])), 10, $this->code_length));

		// Remove bad chars
		$badcharcount = strlen($this->bad_chars);
		for ($i=0; $i<$badcharcount; $i++)
			$code = str_replace($this->bad_chars{$i}, $this->bad_char_replacement, $code);

		$this->code = $code;
		$this->code_enc = $this->_enc($code, true);

		setcookie(CAPTCHA_COOKIE, $this->code_enc, 0, "/");

		return $code;
	}

	function _spaceText($str)
	{
		return trim(preg_replace("/(.)/", "\\1 ", $str));
	}

	function _allocColor($spacedhex)
	{
		$rgb = explode(" ", $spacedhex);
		$rgb[0] = hexdec($rgb[0]);
		$rgb[1] = hexdec($rgb[1]);
		$rgb[2] = hexdec($rgb[2]);
		return imagecolorallocate($this->img, $rgb[0], $rgb[1], $rgb[2]);
	}

	function _enc($str, $generateMode = false)
	{
		return encryptForCookie(strtoupper($str), $this->name, $generateMode);
	}
}

?>