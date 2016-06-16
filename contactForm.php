<?php
include_once "validator/vendor/autoload.php";
class ContactForm extends NDBaseForm {
	protected function setMailConfig($config){
		$config->setConfig('Contato', 'receiver@email.com.br');
	}
	
	protected function setRules(){
		$this->addRule('nome', 'notEmpty');
		$this->addRule('email', 'notEmpty|email');
		$this->addRule('mensagem', 'notEmpty');
	}
}
$form = new ContactForm(true);
