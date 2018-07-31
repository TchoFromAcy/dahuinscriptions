<?php

/*
 * use Gregwar\Captcha\CaptchaBuilder; class Default_Controller_Helpers_Captcha extends CaptchaBuilder { public function __construct($phrase = null, $width = 150, $height = 40, $font = null, $fingerprint = null) { parent::__construct($phrase); $this->setBackgroundColor(240, 243, 240); $this->build($width, $height, $font, $fingerprint); $session = new Zend_Session_Namespace('captcha'); $session->time = time(); $session->phrase = $this->getPhrase(); } public static function check($captcha) { if (!$captcha) { return false; } $session = new Zend_Session_Namespace('captcha'); if (!isset($session->phrase) || $session->phrase != $captcha) { return false; } if (!isset($session->time) || ($session->time + 60 * 5) < time()) { return false; } return true; } }
 */
include 'Captcha/Image.php';
include 'Captcha/Imagick.php';
class Default_Controller_Helpers_Captcha extends Zend_Captcha_Imagick {
	public function __construct($width = 150, $height = 40, array $fontColor = [0, 0, 0], array $bgColor = [255, 0, 0], $length = 6, $fontSize = 22, $dotColor = '#000000', $noiseLevel = 0, $transparent = true) {
		parent::__construct ( [ 
				'name' => 'captcha',
				'wordLen' => $length,
				'fontSize' => $fontSize,
				'timeout' => 900,
				'font' => APPLICATION_PATH . '/fonts/arial.ttf',
				'imgDir' => APPLICATION_PATH . '/../public/captcha' 
		] );
		
		
		$this->setWidth ( $width )->setHeight ( $height );
		$this->setDotNoiseLevel ( $noiseLevel )->setLineNoiseLevel ( 0 );
		$this->setDotColor ( $dotColor );
		// $this->setStartImage(APPLICATION_PATH . '/captcha/bgCaptcha.png');
		$this->setFontColor ( $fontColor );
		$this->setTransparent($transparent);
		$this->setBackgroundColor ( $bgColor );
		//$this->setWord ($word);
		
		$this->generate ();
	}
}