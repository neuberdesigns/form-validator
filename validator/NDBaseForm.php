<?php
date_default_timezone_set('America/Sao_Paulo');
abstract class NDBaseForm {	
	protected $validator;
	protected $session;
	protected $mailConfig;
	protected $inputs;
	protected $passes;
	
	protected $subject = '';
	protected $receiver = '';
	protected $sender = '';
	protected $errorsName = 'errors';
	protected $errorsSulfix = null;
	protected $sended = false;
	protected $useAjax = false;
	protected $rules = array();
	protected $captcha = null;
	
	protected abstract function setMailConfig($config);
	protected abstract function setRules();
	protected function setCaptchaConfig(){}
	protected function validateCaptcha($response){ return true; }
	
	protected function successCallback(){}
	protected function failCallback(){}
	
	public function __construct($useAjax=false){
		$this->validator = new NDValidator();
		$this->session = new NDSession();
		$this->session = new NDSession();
		$this->mailConfig = new NDMailConfig();
		
		$this->useAjax = $useAjax;
		$this->setMailConfig($this->mailConfig);
		$this->setRules();
		$this->setCaptchaConfig();
		$this->handleRequest();
	}
	
	public function addRule($key, $validation){
		$this->rules[$key] = $validation;
	}
	
	public function validate($inputs){
		$this->getSession()->clear();
		$this->inputs = $inputs;
		$this->passes = $this->validator->validate($this->rules, $inputs);
				
		return $this->passes();
	}
	
	protected function handleRequest(){
		$isPost = $this->isPost();
		$isAjax = $this->isAjax();
		$status = 400;
		$json = array();
		
		if( $isPost || $isAjax ){
			$this->validate($this->getRequestInputs());
			
			foreach($this->inputs as $input=>$value){
				$this->session->set($input, $value);
			}
			
			if($this->fail()){
				$this->session->set($this->getErrorName(), $this->validator->getErrors());
				$errors = $this->validator->getErrors();
				if($this->hasCaptcha() && !$this->hasCaptchaInput()){
					$cap = $this->getCaptcha();
					//$capError = $errors[$cap];
					$errors['captcha'] = array("\n  O preenchimento do captcha é obrigatório");
					unset($errors[$cap]);
				}
				$status = 400;
				$json = array(
					'errors'=>$errors,
				);
				$this->failCallback();
			}else{
				$captchaResponse = @$this->inputs[$this->getCaptcha()];
				
				if($this->validateCaptcha($captchaResponse)){
					$send = $this->sendMail();
					if($send){
						$status = 200;
						$json = array(
							'success'=>array('mail'=>'E-Mail enviado com sucesso'),
						);
						$this->successCallback();
					}else{
						$status = 406;
						$json = array(
							'errors'=>array('mail'=>array('Erro ao enviar o email')),
						);
						$this->failCallback();
					}
				}else{
					$status = 400;
					$json = array(
						'errors'=>array('Erro ao validar o captcha'),
					);
				}
			}
			
			if($this->useAjax){
				$this->displayJson($json, $status);
			}
		}
	}
	
	protected function sendRequest($url, $params=array(), $method="post"){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, $method=='post');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$output = curl_exec ($ch);
		curl_close ($ch);
		
		return $output;
	}
	
	protected function displayJson($json, $status=200){
		header(' ', true, $status);
		//header(' ', true, 200);
		header('Content-Type: application/json');
		echo json_encode($json);
	}
	
	public function getSession(){
		return $this->session;
	}
	
	public function old($name, $default=null){
		return $this->getSession()->old($name, $default);
	}
	
	public function clear(){
		return $this->getSession()->clear();
	}
	
	public function hasError(){
		return $this->getSession()->has($this->getErrorName());
	}
		
	public function getErrors($plain=false){
		//$errs = $this->validator->getErrors();
		$errs = $this->getSession()->get($this->getErrorName(), array());
		
		$errsPlain = array();
		
		if( !$plain ){
			return $errs;
		}else{
			foreach ($errs as $errors) {
				foreach($errors as $err){
					$errsPlain[] = $err;
				}
			}
			
			return $errsPlain;
		}
	}
	
	public function sendMail(){
		$messageLines = array();
		$message = '';
		
		foreach($this->inputs as $key=>$value){
			if( $this->hasCaptcha() && $key==$this->getCaptcha())
				continue;
			
			$field = $this->validator->getReadableName($key);
			array_push($messageLines, "$field: $value");
		}
		$message = implode("\n", $messageLines);
		
		$send = false;
		if($this->mailConfig->getType()==NDMailConfig::MAILER_MAIL){
			$send = $this->sendMailDefault($message);
			
		}else if($this->mailConfig->getType()==NDMailConfig::MAILER_PHPMAILER){
			$send = $this->sendMailPHPMailer($message);
		}
		
		if( $send ){
			$this->getSession()->set('form_sended', true);
		}
		
		return $send;
	}
	
	public function sendMailDefault($message){
		$headers = array();
		array_push($headers, 'From: '.$this->mailConfig->getReceiverName().' <'.$this->mailConfig->getReceiver().'>');
		array_push($headers, 'Return-Path:'.$this->mailConfig->getSender());
		array_push($headers, 'Reply-To:'.$this->old('email'));
		array_push($headers, 'X-Mailer: PHP/'.phpversion());
		array_push($headers, 'Content-type:text/plain');
				
		return mail($this->mailConfig->getReceiver(), $this->mailConfig->getSubject() , $message, implode("\r\n", $headers), '-r'.$this->mailConfig->getSender());
	}
	
	public function sendMailPHPMailer($message){
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->SMTPAuth = true;
		$mail->Charset   = 'utf8_decode()';
		$mail->Host = $this->mailConfig->getSmtp();
		$mail->Port = $this->mailConfig->getPort();
		$mail->Username = $this->mailConfig->getUsername();
		$mail->Password = $this->mailConfig->getPassword();
		$mail->SMTPSecure = $this->mailConfig->getSecure();

		$mail->setFrom($this->mailConfig->getSender(), $this->mailConfig->getReceiverName());
		$mail->addAddress($this->mailConfig->getReceiver());
		$mail->addReplyTo($this->old('email'));
		$mail->isHTML(false);
		
		$mail->Subject = $this->mailConfig->getSubject();
		$mail->Body    = $message;
		
		if($this->mailConfig->debug){
			$mail->SMTPDebug = $this->mailConfig->debugLevel;
		}

		return $mail->send();
	}
	
	public function setCaptcha($name){
		$this->captcha = $name;
	}
	
	public function getCaptcha(){
		return $this->captcha;
	}
	
	public function hasCaptchaInput(){
		$resp = false;
		if(isset($this->inputs[$this->getCaptcha()])){
			$capResp = trim($this->inputs[$this->getCaptcha()]);
			$resp = !empty($capResp);
		}
		return $resp;
	}
	
	public function hasCaptcha(){
		return !empty($this->captcha);
	}
	
	public function getRequestInputs(){
		return $_POST;
	}
	
	protected function getErrorName(){
		return $this->errorsName.($this->errorsSulfix?'_'.$this->errorsSulfix:'');
	}
	
	protected function redirect($to){
		header('Location: '.$to);
	}
	
	public function passes(){
		return $this->passes;
	}
	
	public function success(){
		return $this->passes();
	}
	
	public function fail(){
		return !$this->passes();
	}
	
	public function isSend(){
		return $this->getSession()->has('form_sended');
	}
	
	protected function isPost(){
		return $_SERVER['REQUEST_METHOD']=='POST';
	}
	
	protected function isAjax(){
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}
} 
