				<fieldset>
					<legend>Création d'une société</legend>
					<?php if (isset($message)) {
						echo '<div class="userMsg">' . $this->c($message) . '</div>';
					}?>
					<form method="post" action="<?=Request::createUri('admin:societe:')?>">
						<table style="margin-left: 29px;margin-top: -5px;width:580px;">
							<tr>
								<td  align="left">Raison sociale </td>
								<td><input name="rs" type="text" id="rs" size="17" maxlength="38"  />
								</td>
								<td align="left">Contact</td>
								<td><input type="text" name="id_contact" size="7" maxlength="38" /></td>
							</tr>
							<tr>
								<td align="left">Adresse</td>
								<td><input name="adresse1" type="text" id="adresse_1"  size="17" maxlength="38" /></td>
								<td align="left">Complément d'adresse</td>
								<td><input type="text" name="adresse2" size="17" maxlength="38" /></td>
							</tr>
							<tr>
								<td align="left">Code Postal </td>
								<td><input name="cp" type="text" id="code_postal" size="17" maxlength="38" /></td>
								<td align="left">Ville </td>
								<td><input name="ville" type="text" id="ville" size="17" maxlength="32" /></td>
							</tr>
							<tr>
								<td align="left">N° Tel </td>
								<td><input type="text" name="tel" size="17" /></td>
								<td align="left">Groupe</td>
								<td>
									<select style="width: 165px;" name="id_groupe">
										<option value="">-- Sélectionnez --</option>
									<?php foreach ($listGroupe as $groupe):?>
										<option value="<?=$groupe->getId()?>">
											<?=$groupe->getId()?> - <?=$this->c($groupe->getLibelle())?></option>
									<?php endforeach;?>
									</select>
								</td>
							</tr>
						</table>
						<input type="submit" class="btn" value="Valider">
					</form>
				</fieldset>