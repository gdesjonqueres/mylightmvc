			<div id="content_block_nav">
				<h1>Gestion des utilisateurs</h1>
				<fieldset>
					<legend>Création d'un utilisateur</legend>
					<?php if (isset($message)) {
						echo '<div class="userMsg">' . $this->c($message) . '</div>';
					}?>

					<form method="post" action="<?=Request::createUri('admin:user:add')?>">
						<table style="margin-left: 29px;margin-top: -5px;width:580px;">
							<tr>
								<td width="250">Société</td>
								<td>
								<?php if ($user->isAllowedAcces('gestion_societes')):?>
									<select style="width: 165px;"  name="id_societe">
									<?php foreach ($listSociete as $societe):?>
										<option value="<?=$societe->getId()?>"<?=($societe->getId() == $idSociete ? ' selected="selected"' : '')?>>
											<?=$societe->getId()?> - <?=$this->c($societe->getRaisonSociale())?></option>
									<?php endforeach;?>
									</select>
								<?php else:?>
									<input type="hidden" name="id_societe" value="<?=$user->getSociete()->getId()?>" />
								<?php endif;?>
								</td>
								<td width="250">Type </td>
								<td>
									<select style="width: 165px;" name="type">
									<?php foreach ($listUserTypes as $idType => $libelle):?>
										<option value="<?=$idType?>"<?=($idType == $type ? ' selected="selected"' : '')?>>
											<?=$this->c($libelle)?></option>
									<?php endforeach;?>
									</select>
								</td>
							</tr>

							<tr>
								<td >
									<select name="civilite">
										<option value="Mr"<?=($civilite == 'Mr' ? ' selected="selected"' : '')?>>Mr</option>
										<option value="Mme"<?=($civilite == 'Mme' ? ' selected="selected"' : '')?>>Mme</option>
										<option value="Mlle"<?=($civilite == 'Mlle' ? ' selected="selected"' : '')?>>Mlle</option>
									</select>
									Prénom
								 </td>
								<td width="145"><input name="prenom" type="text" id="prenom" size="17"  maxlength="38" value="<?=$prenom?>"/></td>
								<td width="154" >Nom </td>
								<td width="161">
									<input name="nom" type="text" id="nom" size="17" maxlength="38" value="<?=$nom?>"/></td>
							</tr>

							<tr>
								<td >Adresse</td>
								<td><input name="adresse1" type="text" id="adresse_1"  size="17" maxlength="38" value="<?=$adresse1?>"/></td>
								<td >Complément d'adresse</td>
								<td><input type="text" name="adresse2" size="17" maxlength="38" value="<?=$adresse2?>"/></td>
							</tr>

							<tr>
								<td >Code Postal </td>
								<td><input name="cp" type="text" id="code_postal" size="17" maxlength="38" value="<?=$cp?>"/></td>
								<td >Ville </td>
								<td><input name="ville" type="text" id="ville" size="17" maxlength="32" value="<?=$ville?>"/></td>
							</tr>

							<tr>
								<td >N° Tel </td>
								<td><input type="text" name="tel" size="17" value="<?=$tel?>"/></td>
								<td>Mot de passe</td>
								<td><input type="password" name="mdp" size="20" value=""/></td>
							</tr>

								<tr><td >Email</td>
								<td><input type="text" name="email" size="17" maxlength="38" value="<?=$email?>"/></td>
							</tr>
						</table>
						<input type="submit" class="btn" value="Valider">
					</form>
				</fieldset>

				<div id="etape_valide">
					<span class="retour" ><a href="<?=Request::createUri('admin::')?>"> &lt; retour</a></span>
				</div>
			</div>