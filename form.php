<?php include_once 'contactForm.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Test Form Validator</title>
</head>
<body>
	<form id="form-contact" method="POST" action="bla_blabla.php">
		<div>
			<label>Nome</label>
			<input name="nome" value="<?php echo $form->old('nome')?>" />
		</div>
		
		<div>
			<label>E-Mail</label>
			<input name="email" value="<?php echo $form->old('email')?>" />
		</div>
		
		<div>
			<label>Mensagem</label>
			<textarea name="mensagem"><?php echo $form->old('mensagem')?></textarea>
		</div>
		<button type="submit">Enviar</button>
			
	</form>
</body>

<script type="text/javascript" src="validator/vendor/components/jquery/jquery.min.js"></script>
<script type="text/javascript" src="validator/validator.min.js"></script>
<script type="text/javascript">
	$('document').ready(function(){
		var validator = new Validator();
		validator.addFormElement('#form-contact', 'contactForm.php');
		validator.init();
	});
</script>
</html> 
