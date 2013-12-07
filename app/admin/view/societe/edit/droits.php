<?php
/* @var $societe Admin_Model_Societe */
?>
				<fieldset>
					<legend>Droits d'accès</legend>
					<form action="<?=Request::createUri('admin:societe:edit')?>" method="post">
						<table style="text-align:left;">
							<tr>
								<th>Droit</th>
								<th>Autorisé</th>
								<th>Non autorisé</th>
							</tr>
							<?php foreach ($listDroits as $key => $libelle):
								$yes = '';
								$no  = '';
								if ($societe->isAllowedAcces($key)) {
									$yes = 'checked';
								}
								else {
									$no = 'checked';
								}
							?>
							<tr>
								<td><?=$this->c($libelle)?></td>
								<td style="text-align:center;"><input name="acces[<?=$key?>]" type="radio" <?=$yes?> value="1" /></td>
								<td style="text-align:center;"><input name="acces[<?=$key?>]" type="radio" <?=$no?> value="0" /></td>
							</tr>
							<?php endforeach;?>
						</table>
						<input type="hidden" name="mode" value="acces" />
						<input type="hidden" name="id" value="<?=$societe->getId()?>" />
						<input type="submit" class="btn" value="Enregistrer">
					</form>
				</fieldset>

				<fieldset>
					<legend>Droits sur les critères</legend>
					<form action="<?=Request::createUri('admin:societe:edit')?>" method="post">
						<table style="text-align:left;">
							<tr>
								<th>Critère</th>
								<th>Autorisé</th>
								<th>Non autorisé</th>
							</tr>
							<?php foreach ($listCrit as $key => $libelle):
								$myCritere = Comptage_Model_Critere_Dao::getInstance()->get($key);
								$yes = '';
								$no  = '';
								if ($societe->isAllowedCritere($key)) {
									$yes = 'checked';
								}
								else {
									$no = 'checked';
								}
							?>
							<tr>
								<?php if ($myCritere->isLocation()):?>
								<td><span class="location" title="Critère en location">
									<?=$this->c($libelle)?></span><span class="asterisque">*</span></td>
								<?php else:?>
								<td><?=$this->c($libelle)?></td>
								<?php endif;?>

								<td style="text-align:center;"><input name="critere[<?=$key?>]" type="radio" <?=$yes?> value="1" /></td>
								<td style="text-align:center;"><input name="critere[<?=$key?>]" type="radio" <?=$no?> value="0" /></td>

								<?php if ($myCritere->isLocation()):
									$checked = $societe->getDroits()->isAchatSurLocation($key) ? true : false ?>
								<td>
									<span title="Critère en location: autoriser l'achat (oui/non)">Achat autorisé:</span>&nbsp;
									<label for="achat-<?=$key?>-1" style="cursor: pointer;">Oui</label>
									<input id="achat-<?=$key?>-1" name="achat[<?=$key?>]" type="radio" value="1"<?=($checked ? ' checked="cheked"' : '')?> />
									<label for="achat-<?=$key?>-0" style="cursor: pointer;">Non</label>
									<input id="achat-<?=$key?>-0" name="achat[<?=$key?>]" type="radio" value="0"<?=(!$checked ? ' checked="cheked"' : '')?> />
								</td>
								<?php endif;?>
							</tr>
							<?php endforeach;?>
						</table>
						<input type="hidden" name="mode" value="critere" />
						<input type="hidden" name="id" value="<?=$societe->getId()?>" />
						<input type="submit" class="btn" value="Enregistrer">
					</form>
				</fieldset>

				<fieldset>
					<legend>Dessin d'enregistrement</legend>
					<form action="<?=Request::createUri('admin:societe:edit')?>" method="post">
						<table style="text-align:left;">
							<tr>
								<th>Champ</th>
								<th>Autorisé</th>
								<th>Non autorisé</th>
							</tr>
							<?php foreach ($listDE as $key => $libelle):
								$yes = '';
								$no  = '';
								if ($societe->isAllowedDE($key)) {
									$yes = 'checked';
								}
								else {
									$no = 'checked';
								}
							?>
							<tr>
								<td><?=$this->c($libelle)?></td>
								<td style="text-align:center;"><input name="de[<?=$key?>]" type="radio" <?=$yes?> value="1" /></td>
								<td style="text-align:center;"><input name="de[<?=$key?>]" type="radio" <?=$no?> value="0" /></td>
							</tr>
							<?php endforeach;?>
						</table>
						<input type="hidden" name="mode" value="de" />
						<input type="hidden" name="id" value="<?=$societe->getId()?>" />
						<input type="submit" class="btn" value="Enregistrer">
					</form>
				</fieldset>