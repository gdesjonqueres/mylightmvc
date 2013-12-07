<?php
$this->addCss('admin.css');
?>
			<div class="clear"></div>
			<div id="content_block_nav">
				<h1>Fiche utilisateur</h1>
				<h2><?=$userEd->getId()?> - <?=$this->c($userEd->getPrenom() . ' ' . $userEd->getNom())?></h2>
				<?php if (isset($message)) {
					echo '<div class="userMsg">' . $this->c($message) . '</div>';
				}?>
				<fieldset>
					<legend>Informations de l'utilisateur</legend>
					<form method="post" action="<?=Request::createUri('admin:user:edit')?>">
						<table style="margin-left: 29px;margin-top: -5px;width:580px;">
							<tr>
								<td width="250">Société </td>
								<td>
									<select style="width: 165px;" name="id_societe">
									<?php foreach ($listSociete as $societe):?>
										<option value="<?=$societe->getId()?>"<?=($societe->getId() == $userEd->getSociete()->getId() ? ' selected="selected"' : '')?>>
											<?=$societe->getId()?> - <?=$this->c($societe->getRaisonSociale())?></option>
									<?php endforeach;?>
									</select>
								</td>
								<td width="250">Type</td>
								<td>
									<select style="width: 165px;" name="type">
									<?php foreach ($listUserTypes as $idType => $libelle):?>
										<option value="<?=$idType?>"<?=($idType == $userEd->getType()->getId() ? ' selected="selected"' : '')?>>
											<?=$this->c($libelle)?></option>
									<?php endforeach;?>
									</select>
								</td>
							</tr>
							<tr>
								<td><select name="civilite" >
									<option value="Mr"<?=($userEd->getCivilite() == 'Mr' ? ' selected="selected"' : '')?>>Mr</option>
									<option value="Mme"<?=($userEd->getCivilite() == 'Mme' ? ' selected="selected"' : '')?>>Mme</option>
									<option value="Mlle"<?=($userEd->getCivilite() == 'Mlle' ? ' selected="selected"' : '')?>>Mlle</option>
								</select>
								Prénom  </td>
								<td width="145"><input name="prenom" value="<?=$userEd->getPrenom()?>" type="text" id="prenom" size="17"  maxlength="38"/></td>
								<td width="154" >Nom </td>
								<td width="161">
									<input name="nom" type="text" id="nom" value="<?=$userEd->getNom()?>" size="17" maxlength="38" /></td>
								</tr>
								<tr>
									<td >Adresse</td>
									<td><input name="adresse1" type="text" id="adresse_1" value="<?=$userEd->getAdresse1()?>" size="17" maxlength="38" /></td>
									<td >Complément d'adresse</td>
									<td><input type="text" name="adresse2" value="<?=$userEd->getAdresse2()?>" size="17" maxlength="38" /></td>
								</tr>
								<tr>
									<td >Code Postal </td>
									<td><input name="cp" type="text" id="code_postal" value="<?=$userEd->getCp()?>" size="17" maxlength="38" /></td>
									<td >Ville </td>
									<td><input name="ville" type="text" id="ville" value="<?=$userEd->getVille()?>" size="17" maxlength="32" /></td>
								</tr>
								<tr>
									<td >N° Tel </td>
									<td><input type="text" value="<?=$userEd->getTel()?>" name="tel" size="17" /></td>
									<td >Email</td>
									<td><input type="text" value="<?=$userEd->getEmail()?>" name="email"  size="17" maxlength="38" /></td>
								</tr>
								<tr>
									<td>Groupe</td>
									<td><?=($userEd->getIdGroupe() ? $userEd->getGroupe()->getLibelle() : 'Aucun groupe')?></td>
									<td></td>
									<td></td>
								</tr>
							</table>
							<input type="hidden" name="id" value="<?=$userEd->getId()?>" />
							<input type="submit" class="btn" value="Valider" />
					</form>
				</fieldset>

				<?php include 'edit/droits.php';?>

				<div id="etape_valide">
					<span class="retour" ><a href="<?=Request::createUri('admin:user:')?>"> &lt; retour</a></span>
				</div>
			</div>