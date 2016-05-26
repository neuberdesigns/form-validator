<?php
use Respect\Validation\Validator as Validator;
use Respect\Validation\Exceptions\NestedValidationExceptionInterface;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Exceptions\ExceptionInterface;

class NDValidator {
	protected $rules;
	protected $inputs;
	protected $errors;
	
	public function __construct(){
		/*Validator::alnum();
		exit('done');*/
	}
	
	public function getErrors(){
		return $this->errors;
	}
	
	public function hasErrors(){
		return count($this->errors)>0;
	}
	
	public function getReadableName($input) {
		$readableName = str_replace('_', ' ', $input);
		$readableName = ucwords($readableName);
		
		return $readableName;
	}
	public function validate($rules, $inputs){
		$this->errors = array();
		$this->rules = $this->parseRules($rules);
		$this->inputs = $inputs;
		
		$this->doValidation();
		return count($this->getErrors())==0;
	}
	
	public function doValidation(){
		foreach($this->rules as $param=>$rules){
			if( isset($this->inputs[$param]) ){
				$input = $this->inputs[$param];
				if(is_array($rules)){
					foreach ($rules as $rule) {
						$this->validateField($rule, $param);
					}
				}else{
					$rule = $rules;
					$this->validateField($rule, $param);
				}
			}
		}
	}
	
	protected function validateField($rule, $input){
		$value = $this->inputs[$input];
		
		try {
			$rule->setName($this->getReadableName($input))->assert($value);
		}catch(ValidationException $exception) {
			$exception->findMessages($this->getErrorsString());
			//var_dump($exception->getFullMessage());
			$this->errors[$input][] = str_replace(array($exception->getMainMessage(), '\\-'), '', $exception->getFullMessage() );
		}
	}
	
	protected function parseRules($rules){
		$funcRules = array();
		foreach($rules as $key=>$ruleSet){
			if(is_string($ruleSet))
				$ruleSet = explode('|', $ruleSet);
			
			foreach($ruleSet as $rule){
				$validator = $this->getValidator($rule);
				if($validator){
					$funcRules[$key][] = $validator;
				}
			}
		}
		
		return $funcRules;
	}
	
	protected function getValidator($rule){
		$info = explode(':', $rule);
		$params = array();
		
		$validatorName = $info[0];
		if(count($info)>1){
			$params = explode(',', $info[1]);
		}
		
		$validator = call_user_func_array(array('Respect\Validation\Validator', $validatorName), $params);
		return $validator;
	}
	
	protected function getErrorsString(){
		$map = array(
			'notEmpty' => 'Campo {{name}} é obrigatório',
			'email' => 'Campo {{name}} deve ser um e-mail valido',
			'length' => 'Campo {{name}} é muito curto',
			'date'=>'Campo {{name}} deve ser uma data válida'
		);
		
		return $map;
	}
} 
