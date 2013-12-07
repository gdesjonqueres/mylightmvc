<?php
$this->setTitle('Accès refusé');
?>
			<div class="clear"></div>
			<div id="content_block_nav">
				<fieldset>
					<legend>Accès refusé</legend>
					<div id="illustration">
						<img src="<?=$appVars->URL_IMG?>error/dd_error_403.png"  alt="image erreur" />
					</div>
					<p>
						La page que vous cherchez à atteindre est en accès restreint.
						Pour continuer, cliquez <a title="accueil" href="<?=Request::createUri('::')?>">ici</a>
					</p>
					<?php if (isset($exception)) {
						include 'exception/exception.php';
					}?>
				</fieldset>
			</div>