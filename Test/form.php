<?php
date_default_timezone_set('America/Sao_Paulo');
include_once 'ContactForm.php';
$form = new ContactForm();

if($_SERVER['REQUEST_METHOD']=='POST'){
	$form->validate($_POST);
	if( $form->fail() ){
		header('Location: form.php');
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Test Form Validator</title>
</head>
<body>
	<?php if($form->hasError()): ?>
		<?php foreach($form->getErrors(true) as $err): ?>
			<p><font color="red"><?php echo $err ?></font></p>
		<?php endforeach; ?>
	<?php endif; ?>
	<form action="" method="POST">
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
</html> 
