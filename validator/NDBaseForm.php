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
	
	protected abstract function setMailConfig($config);
	protected abstract function setRules();
	
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
				$status = 400;
				$json = array(
					'errors'=>$this->validator->getErrors(),
				);
				$this->failCallback();
			}else{
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
			}
			
			if($this->useAjax){
				header(' ', true, $status);
				//header(' ', true, 200);
				header('Content-Type: application/json');
				echo json_encode($json);
			}
		}
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
		$mail->Host = $this->mailConfig->getHost();
		$mail->Username = $this->mailConfig->getUsername();
		$mail->Password = $this->mailConfig->getPassword();
		$mail->SMTPSecure = $this->mailConfig->getSecure();
		$mail->Port = $this->mailConfig->getPort();

		$mail->setFrom($this->mailConfig->getSender(), $this->mailConfig->getReceiverName());
		$mail->addAddress($this->mailConfig->getReceiver());
		$mail->addReplyTo($this->old('email'));
		$mail->isHTML(false);
		
		$mail->Subject = $this->mailConfig->getSubject();
		$mail->Body    = $message;
		
		return $mail->send();
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
