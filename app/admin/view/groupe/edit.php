			<div class="clear"></div>

			<div id="content_block_nav">
				<h1>Fiche groupe</h1>
				<h2><?=($groupe->getId() . ' - ' . $this->c($groupe->getLibelle()))?></h2>
				<?php if (isset($message)) {
					echo '<div class="userMsg">' . $this->c($message) . '</div>';
				}?>
				<fieldset>
					<legend>Informations groupe</legend>
					<form method="post" action="<?=Request::createUri('admin:groupe:edit')?>">
						<table style="margin-left: 29px;margin-top: -5px;width:580px;">
							<tr>
								<td  align="left">Libellé</td>
								<td><input name="libelle" type="text" id="libelle" value="<?=$groupe->getLibelle()?>" size="17" maxlength="38"  /></td>
							</tr>
						</table>
						<input type="hidden" name="id" value="<?=$groupe->getId()?>">
						<input type="submit" class="btn" value="Valider">
					</form>
				</fieldset>

				<div class="separation"></div>
				<div id="etape_valide">
					<span class="retour" ><a href="<?=Request::createUri('admin:groupe:')?>"> &lt; retour</a></span>
				</div>
			</div>