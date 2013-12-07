<?php
$this->setTitle('Page introuvable');
?>
			<div class="clear"></div>
			<div id="content_block_nav">
				<fieldset>
					<legend>Page introuvable</legend>
					<div id="illustration">
						<img src="<?=$appVars->URL_IMG?>error/dd_error_404.png"  alt="image erreur" />
					</div>
					<p>
						La page que vous cherchez à atteindre n'existe pas. <br/>
						Pour continuer, cliquez <a title="accueil" href="<?=Request::createUri('::')?>"> ici </a>
					</p>
					<?php if (isset($exception)) {
						include 'exception/exception.php';
					}?>
				</fieldset>
			</div>