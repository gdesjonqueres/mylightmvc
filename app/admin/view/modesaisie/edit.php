<?php
/* @var $mode Comptage_Model_ModeSaisie */
?>
			<div class="clear"></div>
			<div id="content_block_nav">
				<h1>Fiche mode de saisie</h1>
				<h2><?php echo $mode->getId() . ' - ' . $this->c($mode->getLibelle()); ?></h2>
				<?php if (isset($message)) {
					echo '<div class="userMsg">' . $this->c($message) . '</div>';
				}?>
				<fieldset>
					<legend>Informations mode de saisie</legend>
					<form method="post" action="<?=Request::createUri('admin:modesaisie:edit')?>">
						<table style="margin-left: 29px;margin-top: -5px;width:580px;">
							<tr>
								<td  align="left">Code</td>
								<td><input name="code" type="text" id="code" value="<?=$mode->getCode()?>" size="17" maxlength="38"  /></td>
								<td align="left">Libellé</td>
								<td><input type="text" name="libelle" value="<?=$mode->getLibelle()?>" size="17" maxlength="38" /></td>
							</tr>
							<tr>
								<td align="left">Description</td>
								<td><textarea id="description" name="description" rows="3" cols="25"><?=$mode->getDescription()?></textarea></td>
								<td align="left">Arguments</td>
								<td><textarea id="args" name="args" rows="3" cols="25" placeholder="Valeurs séparées par des virgules"><?=implode(', ', $mode->getListArgs())?></textarea></td>
							</tr>
						</table>
						<input type="hidden" name="id" value="<?=$mode->getId()?>">
						<input type="submit" class="btn" value="Valider">
					</form>
				</fieldset>

				<div id="etape_valide">
					<span class="retour" ><a href="<?=Request::createUri('admin:modesaisie:')?>"> &lt; retour</a></span>
				</div>
			</div>