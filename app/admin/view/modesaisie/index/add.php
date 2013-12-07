				<fieldset>
					<legend>Création d'un mode de saisie</legend>
					<?php if (isset($message)) {
						echo '<div class="userMsg">' . $this->c($message) . '</div>';
					}?>
					<form method="post" action="<?=Request::createUri('admin:critere:')?>">
						<table style="margin-left: 29px;margin-top: -5px;width:580px;">
							<tr>
								<td  align="left">Code </td>
								<td><input name="code" type="text" id="code" size="17" maxlength="38"  />
								</td>
								<td align="left">Libellé</td>
								<td><input name="libelle" type="text" id="libelle" size="17" maxlength="38"  />
							</tr>
							<tr>
								<td align="left">Description</td>
								<td><textarea id="description" name="description" rows="3" cols="25"></textarea></td>
								<td align="left">Arguments</td>
								<td><textarea id="args" name="args" rows="3" cols="25" placeholder="Valeurs séparées par des virgules"></textarea></td>
							</tr>
						</table>
						<input type="submit" class="btn" value="Valider">
					</form>
				</fieldset>
