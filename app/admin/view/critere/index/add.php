				<fieldset>
					<legend>Création d'un critère</legend>
					<?php if (isset($message)) {
						echo '<div class="userMsg">' . $this->c($message) . '</div>';
					}?>
					<form method="post" action="<?=Request::createUri('admin:critere:')?>">
						<table style="margin-left: 29px;margin-top: -5px;width:580px;">
							<tr>
								<td align="left">Code</td>
								<td><input name="code" type="text" id="code" size="17" maxlength="38" /></td>
								<td align="left">Libellé</td>
								<td><input name="libelle" type="text" id="libelle" size="17" maxlength="38" /></td>
							</tr>

							<tr>
								<td align="left">Libellé court</td>
								<td><input name="libellecourt" type="text" id="libellecourt" size="17" maxlength="38" /></td>
							</tr>

							<tr>
								<td align="left">Type de critère</td>
								<td>
									<select style="width: 165px;" name="type">
										<option value="">-- Sélectionnez --</option>
									<?php foreach ($listType as $id => $label):?>
										<option value="<?=$id?>">
											<?=$this->c($label)?></option>
									<?php endforeach;?>
									</select>
								</td>
								<td align="left">Type de valeur</td>
								<td>
									<select style="width: 165px;" name="typevaleur">
										<option value="">-- Sélectionnez --</option>
									<?php foreach ($listTypeValeur as $id => $label):?>
										<option value="<?=$id?>">
											<?=$this->c($label)?></option>
									<?php endforeach;?>
									</select>
								</td>
							</tr>

							<tr>
								<td>Type de cible</td>
								<td colspan="3" align="left"><?=$this->displayCheckbox('cibletype', $listCibleType)?></td>
							</tr>

							<tr>
								<td align="left">Scoring</td>
								<td align="left"><input type="checkbox" name="scoring" id="scoring" value="1" /></td>
								<td align="left">Location</td>
								<td align="left"><input type="checkbox" name="location" id="location" value="1" /></td>
							</tr>
						</table>
						<input type="submit" class="btn" value="Valider">
					</form>
				</fieldset>
