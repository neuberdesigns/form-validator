<?php
session_start();
//session_destroy();exit('destroyed');

class NDSession {
	const FLASH = 'flash';
	const OLD = 'old';
	
	protected $session;
	
	public function __construct(){
		$this->init();
	}
	
	protected function init(){
		//$this->session = (object)$_SESSION;
	}
	
	public function deInit(){
		/*$props = get_object_vars($this->session);
		foreach($props as $key=>$value){
			$_SESSION[$key] = $value;
		}*/
	}
	
	public function __destruct(){
		$this->deInit();
	}
	
	public function old($name){
		return $this->get($name);
	}
	
	public function get($name, $default=null){
		if($this->has($name)){
			//return $this->session->$name;
			return $_SESSION[$name];
		}else{
			return $default;
		}
	}
	
	public function set($name, $value){
		$_SESSION[$name] = $value;
		//$this->session->$name = $value;
	}
	
	public function forget($name){
		if($this->has($name)){
			unset($_SESSION[$name]);
		}
	}
	
	public function put($name, $value){
		array_push($_SESSION[$name], $value);
	}
	
	public function has($name){
		return isset($_SESSION[$name]);
	}
	
	public function clear(){
		//session_destroy();
		//session_start();
		$_SESSION = array();
		$this->init();
	}
} 
