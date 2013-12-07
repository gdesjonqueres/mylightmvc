<!DOCTYPE html>
<html lang="fr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
	body {
		font-size: 14px;
	}
	.exception {
		font-size: 14px;
		text-align: justify;
		margin: 10px 5px;
	}

	.exception .header {
		font-size: 16px;
		font-weight: bold;
		color: red;
	}

	.exception span.type {
		font-weight: normal;
		font-style: italic;
	}

	.exception p {
		margin-top: 5px;
	}

	.exception h2 {
		font-size: 16px;
		font-weight: normal;
		text-decoration: underline;
		color: blue;
	}

	.exception p em {
		color: green;
		font-weight: bold;
	}

	.dump, .sql {
		font-size: 12px;
		max-height: 300px;
		max-width: 700px;
		overflow: auto;
		border: 1px solid grey;
		background: linear-gradient(45deg, #E8E8E8 0%, #D6D6D6 40%, #C6C6C6 100%) repeat scroll 0 0 transparent;
		box-shadow: 0px 0px 5px 0px;
		margin: 5px;
		padding: 15px;
	}
	.dump .container {
		color: red;
		font-weight: bold;
		font-style: normal;
	}
	.dump .pname {
		color: blue;
		font-weight: bold;
	}
	.dump .pvalue {
		color: green;
		font-style: italic;
	}

	.sql .keyword {
		color: blue;
		font-weight: bold;
	}
	.sql .operator {
		color: red;
	}
	.sql .string {
		color: green;
		font-style: italic;
	}
	.sql .punctuation {
		font-style: bold;
		color: blue;
	}
</style>
</head>
<body>
	<p>
		Bonjour le syst&egrave;me a g&eacute;n&eacute;r&eacute; l'erreur suivante:
		<?php if (isset($user)):?>
		<br /> lors de la session de l'utilisateur <em><?=$this->c($user->getPrenom())?> <?=$this->c($user->getNom())?></em> (<?=$user->getId()?>)
		<?php endif;?>
	</p>
	<?php if (isset($exception)) {
		$noCss = true;
		include 'exception/exception.php';
	}?>
</body>
</html>