<?php
date_default_timezone_set('America/Sao_Paulo');
abstract class NDBaseForm {
	const MAILER_MAIL = 'mail';
	const MAILER_PHPMAILER = 'phpmailer';
	
	const SESSION_ERRORS = 'errors';
	const SESSION_SENDED = 'sended';
	
	protected $validator;
	protected $session;
	protected $inputs;
	protected $passes;
	
	protected $subject = '';
	protected $receiver = '';
	protected $sender = '';
	protected $errorsName = 'errors';
	protected $errorsSulfix = null;
	protected $sended = false;
	protected $jsonResponse = false;
	protected $rules = array();
	
	protected abstract function init();
	protected abstract function successCallback();
	protected abstract function failCallback();
	
	public function __construct(){
		$this->validator = new NDValidator();
		$this->session = new NDSession();
		
		$this->init();
		
		$this->handleRequest();
	}
	
	protected function configMail($subject, $receiver, $sender=null){
		$this->subject = $subject;
		$this->receiver = $receiver;
		$this->sender = !empty($sender) ? $sender : $receiver;
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
		if($this->isPost() || $this->isAjax()){
			$this->validate($this->getRequestInputs());
			
			foreach($this->inputs as $input=>$value){
				$this->session->set($input, $value);
			}
			
			if($this->fail()){
				$this->session->set($this->getErrorName(), $this->validator->getErrors());
				$this->failCallback();
			}else{
				$send = $this->sendMail();
				if($send){
					$this->successCallback();
				}else{
					$this->failCallback();
				}
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
	
	public function sendMail($transport=self::MAILER_MAIL){
		if($transport==self::MAILER_MAIL){
			return $this->sendMailDefault();
		}else if($transport==self::MAILER_PHPMAILER){
			return $this->sendMailPHPMailer();
		}
	}
	
	public function sendMailDefault(){
		$headers = array();
		$message = array();
		foreach($this->inputs as $key=>$value){
			$field = $this->validator->getReadableName($key);
			array_push($message, "$field: $value");
		}
		
		array_push($headers, 'From:'.$this->sender);
		array_push($headers, 'Return-Path:'.$this->sender);
		array_push($headers, 'Reply-To:'.$this->old('email'));
		array_push($headers, 'X-Mailer: PHP/'.phpversion());
		array_push($headers, 'Content-type:text/plain');
		
		$send = mail($this->receiver, $this->subject , implode("\n", $message), implode("\r\n", $headers), '-r'.$this->sender );
		//$send = true;
		
		if( $send ){
			$this->getSession()->set('form_sended', true);
		}
		return $send;
	}
	
	public function sendMailPHPMailer(){}
	
	protected function getTextResponse(){}
	
	protected function getJsonResponse(){}
	
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
