<?php
include_once "../validator/vendor/autoload.php";
class ContactForm extends NDBaseForm {
	protected function init(){
		$this->addRule('nome', 'notEmpty');
		$this->addRule('email', 'notEmpty|email');
		$this->addRule('mensagem', 'notEmpty');
		
		$this->configMail('Contato', 'receiver@email.com.br');
	}
}
