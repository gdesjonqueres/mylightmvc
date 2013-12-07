				<fieldset>
					<legend>Création d'un groupe</legend>
					<?php if (isset($message)) {
						echo '<div class="userMsg">' . $this->c($message) . '</div>';
					}?>
					<form method="post" action="<?=Request::createUri('admin:groupe:')?>">
						<table style="margin-left: 29px;margin-top: -5px;width:580px;">
							<tr>
								<td  align="left">Libellé</td>
								<td><input name="libelle" type="text" id="libelle" size="17" maxlength="38"  />
								</td>
							</tr>
						</table>
						<input type="submit" class="btn" value="Valider">
					</form>
				</fieldset>