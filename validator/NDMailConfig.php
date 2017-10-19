<?php
class NDMailConfig {
	const MAILER_MAIL = 'mail';
	const MAILER_PHPMAILER = 'phpmailer';
	
	protected $type = self::MAILER_MAIL;
	protected $receiverName;
	protected $receiver;
	protected $sender;
	protected $subject;
	
	protected $host;
	protected $username;
	protected $password;
	protected $secure = 'tls';
	protected $smtp = '';
	protected $port = 587;
	
	public $debug = false;
	public $debugLevel = SMTP::DEBUG_CONNECTION;

	
	public function getReceiver(){
		return $this->receiver;
	}
	
	public function getReceiverName(){
		return $this->receiverName;
	}
	
	public function setReceiver($email, $name=null){
		$this->receiverName = $name;
		$this->receiver = $email;
		$this->sender = $email;
		return $this;
	}
	
	public function getSender(){
		return $this->sender;
	}
	
	public function setSender($email){
		$this->sender = $email;
		return $this;
	}
	
	public function getSubject(){
		return $this->subject;
	}
	
	public function setSubject($subject){
		$this->subject = $subject;
		return $this;
	}
	
	public function getType(){
		return $this->type;
	}
	
	public function setType($type){
		$this->type = $type;
		return $this;
	}
	
	public function setConfig($subject, $receiver, $sender=null){
		$this->subject = $subject;
		$this->receiver = $receiver;
		$this->sender = !empty($sender) ? $sender : $receiver;
		
		return $this;
	}

	public function enableDebug($level=null){
		$this->debug = true;

		if($level){
			$this->debugLevel = $level;
		}
	}
	
	public function disableDebug(){
		$this->debug = true;
	}
	
	//Credentials
	public function getHost(){
		return $this->host;
	}
	
	public function getUsername(){
		return $this->username;
	}
	
	public function getPassword(){
		return $this->password;
	}
	
	public function getSecure(){
		return $this->secure;
	}
	
	public function getSmtp(){
		return $this->smtp;
	}
	
	public function getPort(){
		return $this->port;
	}
	
	public function setCredentials($user, $pass, $smtp, $secure='tls', $port=587){
		$this->type = self::MAILER_PHPMAILER;
		$this->host = $host;
		$this->username = $user;
		$this->password = $pass;
		$this->smtp = $smtp;
		$this->port = $port;
		$this->secure = $secure;
		
		return $this;
	}
}
