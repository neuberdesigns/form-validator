var Validator = function(){
	var forms = [];
	var successCallback;
	var failCallback;
	
	var onSuccess = function(cb){
		if(typeof cb == 'function')
			successCallback = cb;
	};
	
	var onFail = function(cb){
		if(typeof cb == 'function')
			failCallback = cb;
	};
	
	var addFormElement = function(id, url){
		forms.push({
			form: $(id),
			url: url,
		});
	};
	
	var init = function(){
		var $form;
		for(var i=0 in forms){
			form = forms[i];
			$form = form.form;
			$form.on('submit', handleFormSubmit);
			//$form.attr('action', form.url);
			//$form.attr('method', 'POST');
		}
	};
	
	var find = function(id){
		var form = null;
		for(var i=0 in forms){
			if(forms[i].form.attr('id')==id){
				form = forms[i];
				break;
			}
		}
		
		return form;
	};
	
	var extractError = function(errors){
		var errorsList = [];
		
		for( var key in errors){
			for(var i in errors[key]){
				errorsList.push(errors[key][i]);
			}
		}
		
		return errorsList.join("\n");
	};
	
	var callSuccess = function($form, winMessage){
		$form.trigger('reset');
		if(successCallback){
			successCallback.apply(this, [$form, winMessage]);
		}else{
			alert(winMessage);
		}
	};
	
	var callFail = function($form, failMessage){
		if(failCallback){
			failCallback.apply(this, [$form, failMessage]);
		}else{
			alert(failMessage);
		}
	}
	
	
	var handleFormSubmit = function(e){
		e.preventDefault();
		var id = $(this).attr('id');
		var formInfo = find(id);
		var $form;
		
		if(formInfo){
			$form = formInfo.form;
			$.ajax(formInfo.url, {
				method: 'POST',
				dataType: 'json',
				data: $form.serialize(),
				complete: function(jqXHR, textStatus){
					var response;
					try {
						response = JSON.parse(jqXHR.responseText);
					}catch(ex){
						alert('Ocorreu um erro, tente novamente mais tarde.');
						return false;
					}
					/*console.log(jqXHR.status);
					console.log(jqXHR.responseText);
					console.log('textStatus', textStatus);*/
					
					if(jqXHR.status==200){
						callSuccess($form, response.success.mail);
					}else if(jqXHR.status==400 || jqXHR.status==406){
						callFail($form, extractError(response.errors));						
					}else{
						console.log('Unknow Error :(');
					}
				},
			});
		}
		
		return false;
	}
	
	return {
		init: init,
		addFormElement: addFormElement,
		onFail: onFail,
		onSuccess: onSuccess,
	};
}
