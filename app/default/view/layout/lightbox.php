<!DOCTYPE html>
<!--[if lt IE 7]>      <html lang="fr" class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html lang="fr" class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html lang="fr"class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="fr" class="no-js"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title><?=($this->getTitle() ? $this->getTitle() :
									'Expert marketing direct et ciblage marketing - Accueil Dataneo')?></title>
	<meta name="description" content="Solutions marketing direct de prospection. Achat en ligne de fichiers d'adresses / Envoi en ligne de mailing postal" />
	<link rel="icon" href="<?=$appVars->URL_ROOT?>favicon.gif" type="image/gif"/>
	<!--[if IE]><link href="<?=$appVars->URL_ROOT?>favicon.ico" rel="shortcut icon" type="image/x-icon" /><![endif]-->
	<link href="<?=$appVars->URL_CSS?>fonts.css" rel="stylesheet" type="text/css" />
	<link href="<?=$appVars->URL_CSS?>style.css" rel="stylesheet" type="text/css" />
	<link href="<?=$appVars->URL_CSS?>modesaisie.css" rel="stylesheet" type="text/css" />
	<link href="http://fonts.googleapis.com/css?family=Voces" rel="stylesheet" type="text/css" />
	<?=$this->displayCss();?>
	<!--[if lt IE 9]>
		<?php if ($appVars->APP_ENV == 'prod'):?>
		<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<script>window.html5 || document.write('<script src="<?=$appVars->URL_JS?>vendor/html5shiv.js"><\/script>')</script>
		<?php else:?>
		<script src="<?=$appVars->URL_JS?>vendor/html5shiv.js"></script>
		<?php endif;?>
	<![endif]-->
	<script>
	//<![CDATA[
		document.documentElement.className = document.documentElement.className.replace("no-js","js");
	//]]>
	</script>
</head>
<body>

	<div>
		<div class="myLightbox">
		<?php if (isset($content)){
			echo $content;
		}?>
		</div>
	</div>

	<?php if (($tabFooter = $this->getFooter()) !== false) {
		$tabFooter = (array) $tabFooter;
		foreach ($tabFooter as $footer) {
			echo "\n" . $footer;
		}
	}?>

	<!-- Inclusion des JS -->
	<?php if ($appVars->APP_ENV == 'prod'):?>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="<?=$appVars->URL_JS?>vendor/jquery-1.9.1.min.js"><\/script>')</script>
	<?php else:?>
	<script src="<?=$appVars->URL_JS?>vendor/jquery-1.9.1.min.js"></script>
	<?php endif;?>
	<script>
	//<![CDATA[
		var URL_BASE = '<?=$appVars->URL_ROOT?>';
	//]]>
	</script>
	<script src="<?=$appVars->URL_JS?>base.js" charset="UTF-8"></script>
	<?=$this->displayJs();?>
	<?php if (($tabReadyEvent = $this->getReadyEvent()) !== false):?>
	<script>
	//<![CDATA[
		$(document).ready(function() {
			<?php $tabReadyEvent = (array) $tabReadyEvent;
			foreach ($tabReadyEvent as $script) {
				echo "\n" . $script;
			}?>
		});
	//]]>
	</script>
	<?php endif;?>

</body>
</html>
