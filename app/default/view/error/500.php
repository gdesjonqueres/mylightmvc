<?php
$this->setTitle('Service indisponible');
?>
			<div class="clear"></div>
			<div id="content_block_nav">
				<fieldset>
					<legend>Service indisponible</legend>
					<div id="illustration">
						<img src="<?=$appVars->URL_IMG?>error/dd_error_500.png"  alt="image erreur" />
					</div>
					<p>
						Le service est actuellement indisponible et/ou votre demande a généré une erreur.<br/>
						Une notification vient d'être envoyée à notre support technique.<br/>
						Si le problème persiste veuillez contacter notre equipe support.<br/>
						Veuillez nous excuser pour la gêne occasionnée.
						Pour continuer, cliquez <a title="accueil" href="<?=Request::createUri('::')?>"> ici </a>
					</p>
					<?php if (isset($exception)) {
						include 'exception/exception.php';
					}?>
				</fieldset>
			</div>