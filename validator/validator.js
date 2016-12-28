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
	
	
	var displayLoader = function($button){
		var size = $button.height();
		var $spinner = $('<div class="spinner"></div>');
		var $svg = $([
			'<svg viewBox="0 0 64 64">',
			'	<g stroke-width="4" stroke-linecap="round">',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(180)"><animate attributeName="stroke-opacity" dur="750ms" values="1;.85;.7;.65;.55;.45;.35;.25;.15;.1;0;1" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(210)"><animate attributeName="stroke-opacity" dur="750ms" values="0;1;.85;.7;.65;.55;.45;.35;.25;.15;.1;0" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(240)"><animate attributeName="stroke-opacity" dur="750ms" values=".1;0;1;.85;.7;.65;.55;.45;.35;.25;.15;.1" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(270)"><animate attributeName="stroke-opacity" dur="750ms" values=".15;.1;0;1;.85;.7;.65;.55;.45;.35;.25;.15" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(300)"><animate attributeName="stroke-opacity" dur="750ms" values=".25;.15;.1;0;1;.85;.7;.65;.55;.45;.35;.25" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(330)"><animate attributeName="stroke-opacity" dur="750ms" values=".35;.25;.15;.1;0;1;.85;.7;.65;.55;.45;.35" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(0)"><animate attributeName="stroke-opacity" dur="750ms" values=".45;.35;.25;.15;.1;0;1;.85;.7;.65;.55;.45" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(30)"><animate attributeName="stroke-opacity" dur="750ms" values=".55;.45;.35;.25;.15;.1;0;1;.85;.7;.65;.55" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(60)"><animate attributeName="stroke-opacity" dur="750ms" values=".65;.55;.45;.35;.25;.15;.1;0;1;.85;.7;.65" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(90)"><animate attributeName="stroke-opacity" dur="750ms" values=".7;.65;.55;.45;.35;.25;.15;.1;0;1;.85;.7" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(120)"><animate attributeName="stroke-opacity" dur="750ms" values=".85;.7;.65;.55;.45;.35;.25;.15;.1;0;1;.85" repeatCount="indefinite"></animate></line>',
			'		<line y1="17" y2="29" transform="translate(32,32) rotate(150)"><animate attributeName="stroke-opacity" dur="750ms" values="1;.85;.7;.65;.55;.45;.35;.25;.15;.1;0;1" repeatCount="indefinite"></animate></line>',
			'	</g>',
			'</svg>',
		].join(''));
		
		$spinner.css('stroke', 'black');
		$spinner.css('width', size);
		$spinner.css('height', size);
		$spinner.css('float', 'right');
		$spinner.css('margin-left', 5);
		
		$spinner.append($svg);
		$button.append($spinner);
	}
	
	var hideLoader = function($button){
		$button.find('.spinner').remove();
	}
	
	var handleFormSubmit = function(e){
		e.preventDefault();
		var id = $(this).attr('id');
		var formInfo = find(id);
		var $form, $submit;
		
		if(formInfo){
			$form = formInfo.form;
			$submit = $form.find('input[type="submit"], button, button[type="submit"]');
			$submit.attr('disabled', true);
			
			if($submit.prop('nodeName')=='BUTTON'){
				displayLoader($submit);
			}
			
			$.ajax(formInfo.url, {
				type: 'POST',
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
					
					$submit.attr('disabled', false);
					hideLoader($submit);
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
