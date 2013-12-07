<?php
/* @var $user User */
/* @var $this View */

$this->addCss('style.css', true);
$this->addCss('fonts.css', true);
$this->addJs('base.js', true);

if (isset($user) && $user->isDebugMode()) {
	$this->addCss('debug.css');
}
?>
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
	<link rel="icon" href="<?=$appVars->URL_ROOT?>favicon.gif" type="image/gif" />
	<!--[if IE]> <link href="<?=$appVars->URL_ROOT?>favicon.ico" rel="shortcut icon" type="image/x-icon" /> <![endif]-->
	<link href="//fonts.googleapis.com/css?family=Voces" rel="stylesheet" type="text/css" />
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
	<!--[if lt IE 7]>
		<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
	<![endif]-->

	<header id="header">
		<div id="header_content">
			<div style="float:left; padding-top: 13px;">
				<a href="<?=Request::createUri('::')?>">
					<img src="<?=$appVars->URL_IMG?>logo_Dataneo.png" alt="Logo dataneo" height="54" /></a>
			</div>
			<div id="horiz-menu">
				<ul style="overflow: visible;" class="nav"></ul>
			</div>

			<div class="clear"></div>

			<?php if (isset($user)) {
				include 'default/user_info.php';
			}?>
		</div><!-- header_content -->
	</header>

	<div id="corps">
		<div id="corps_content">
			<?php if (isset($content)) {
				echo $content;
			}?>

			<div class="clear"></div>

		</div><!-- corps_content -->
	</div><!-- corps -->

	<footer id="footer" >
		<div id="footer_1">
			<!-- <ul style="padding-top: 11px;">
				<li><a href="<?=$appVars->URL_ROOT?>divers/mentions-legales.php">Mentions légales</a></li>
				<li>|</li>
				<li><a href="<?=$appVars->URL_ROOT?>divers/plan-du-site.php">Plan du site</a></li>
			</ul> -->
		<?php if (($tabFooter = $this->getFooter()) !== false) {
			$tabFooter = (array) $tabFooter;
			foreach ($tabFooter as $footer) {
				echo "\n" . $footer;
			}
		}?>
		</div><!-- footer_1 -->
		<div class="clear"></div>

		<?php if (isset($user) && $user->isDebugMode()) {
			include 'debug/debug.php';
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

	</footer>

</body>
</html>
