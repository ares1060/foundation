<?php

namespace at\foundation\core\Mail;
use at\foundation\core;

class Mail extends core\AbstractService implements core\IService  {

	function __construct(){
		$this->name = 'Mail';
        $this->ini_file = $GLOBALS['to_root'].'_service/Mail/Mail.ini';
        parent::__construct();
	}

	public function render($args) {
		return '';
	}

	/**
	* Sends a mail with the given content to the given recipient
	* @param string $to A single or multiple comma seperated email addresses. 
	* @param string $subject The subject of the mail
	* @param string $text The (html) content of the mail. Will be utf8 encoded.
	* @param string $from The email and name of the sender. e.g. "Mailer <mailer@yourdomain.com>"
	*/
	public function send($to, $subject, $text, $from=''){
		if($from == '') $from = $this->settings->default_sender_adress.'@'.$_SERVER['SERVER_NAME'];
		
		//set headers
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= 'From: ' .$from. "\r\n";
	
		//send
		return mail($to, $subject, utf8_encode($text), $headers);
	}
}

?>