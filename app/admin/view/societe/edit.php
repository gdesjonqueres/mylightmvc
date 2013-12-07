<?php

$this->addCss('admin.css');
$this->addPlugin('dataTables', array('jquery.dataTables.css', 'jquery.dataTables.min.js'));

$this->addReadyEvent(
			'$("#socTab").dataTable({
				"sPaginationType": "full_numbers",
				"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
			});
			$("#userTab").dataTable({
				"sPaginationType": "full_numbers",
				"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
			});
			$("#typeTab").dataTable({
				"sPaginationType": "full_numbers",
				"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
			});');
?>
			<div class="clear"></div>
			<div id="content_block_nav">
				<h1>Fiche société</h1>
				<h2><?php echo $societe->getId() . ' - ' . $this->c($societe->getRaisonSociale()); ?></h2>
				<?php if (isset($message)) {
					echo '<div class="userMsg">' . $this->c($message) . '</div>';
				}?>
				<fieldset>
					<legend>Informations société</legend>
					<form method="post" action="<?=Request::createUri('admin:societe:edit')?>">
						<table style="margin-left: 29px;margin-top: -5px;width:580px;">
							<tr>
								<td  align="left">Raison sociale </td>
								<td><input name="rs" type="text" id="rs" value="<?=$societe->getRaisonSociale()?>" size="17" maxlength="38"  /></td>
								<td align="left">Contact</td>
								<td><input type="text" name="id_contact" value="<?=$societe->getContact()?>" size="7" maxlength="38" /></td>
							</tr>
							<tr>
								<td align="left">Adresse</td>
								<td><input name="adresse1" type="text" value="<?=$societe->getAdresse1()?>" id="adresse_1"  size="17" maxlength="38" /></td>
								<td align="left">Complément d'adresse</td>
								<td><input type="text" name="adresse2" value="<?=$societe->getAdresse2()?>" size="17" maxlength="38" /></td>
							</tr>
							<tr>
								<td align="left">Code Postal </td>
								<td><input name="cp" type="text" id="code_postal" value="<?=$societe->getCp()?>" size="17" maxlength="38" /></td>
								<td align="left">Ville </td>
								<td><input name="ville" type="text" id="ville" value="<?=$societe->getVille()?>" size="17" maxlength="32" /></td>
							</tr>
							<tr>
								<td align="left">N° Tel </td>
								<td><input type="text" value="<?=$societe->getTel()?>" name="tel" size="17" /></td>
								<td align="left">Groupe</td>
								<td>
									<select style="width: 165px;" name="id_groupe">
										<option value=""<?=(!$societe->getIdGroupe() ? ' selected="selected"' : '')?>>-- Sélectionnez --</option>
									<?php foreach ($listGroupe as $groupe):?>
										<option value="<?=$groupe->getId()?>"<?=($groupe->getId() == $societe->getIdGroupe() ? ' selected="selected"' : '')?>>
											<?=$groupe->getId()?> - <?=$this->c($groupe->getLibelle())?></option>
									<?php endforeach;?>
									</select>
								</td>
							</tr>
						</table>
						<input type="hidden" name="id" value="<?=$societe->getId()?>">
						<input type="submit" class="btn" value="Valider">
					</form>
				</fieldset>

				<?php include 'edit/droits.php';?>

				<?php include 'edit/users.php';?>

				<div id="etape_valide">
					<span class="retour" ><a href="<?=Request::createUri('admin:societe:')?>"> &lt; retour</a></span>
				</div>
			</div>