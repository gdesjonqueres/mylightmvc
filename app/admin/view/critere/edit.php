<?php
/* @var $critere Comptage_Model_Critere */

$this->addCss('admin.css');

$js = <<<'EOT'
	$("#critere-args fieldset legend").on("click", function(event) {
		$(this).parent().children("form").slideToggle();
		$(this).toggleClass("open closed");
	});
EOT;
$this->addReadyEvent($js);
?>
			<div class="clear"></div>
			<div id="content_block_nav">
				<h1>Fiche critère</h1>
				<h2><?php echo $critere->getId() . ' - ' . $this->c($critere->getLibelle()); ?></h2>
				<?php if (isset($message)) {
					echo '<div class="userMsg">' . $this->c($message) . '</div>';
				}?>
				<fieldset>
					<legend>Informations critère</legend>
					<form method="post" action="<?=Request::createUri('admin:critere:edit')?>">
						<table style="margin-left: 29px;margin-top: -5px;width:580px;">
							<tr>
								<td align="left">Code</td>
								<td><input name="code" type="text" id="code" value="<?=$critere->getCode()?>" /></td>
								<td align="left">Libellé</td>
								<td><input name="libelle" type="text" id="libelle" value="<?=$critere->getLibelle()?>" /></td>
							</tr>

							<tr>
								<td align="left">Libellé court</td>
								<td><input name="libellecourt" type="text" id="libellecourt" value="<?=$critere->getLibelleCourt()?>" /></td>
							</tr>

							<tr>
								<td align="left">Type de critère</td>
								<td>
									<?php $type = $critere->getType();?>
									<select style="width: 165px;" name="type">
										<option value="">-- Sélectionnez --</option>
									<?php foreach ($listType as $id => $label):?>
										<option value="<?=$id?>"<?=($id == $type['id'] ? ' selected' : '')?>>
											<?=$this->c($label)?></option>
									<?php endforeach;?>
									</select>
								</td>
								<td align="left">Type de valeur</td>
								<td>
									<select style="width: 165px;" name="typevaleur">
										<option value="">-- Sélectionnez --</option>
									<?php foreach ($listTypeValeur as $id => $label):?>
										<option value="<?=$id?>"<?=($id == $critere->getTypeValeur() ? ' selected' : '')?>>
											<?=$this->c($label)?></option>
									<?php endforeach;?>
									</select>
								</td>
							</tr>

							<tr>
								<td align="left">Type de donnée</td>
								<td>
									<select style="width: 165px;" name="typedonnee">
										<option value="">-- Sélectionnez --</option>
									<?php foreach ($listTypeDonnee as $id => $label):?>
										<option value="<?=$id?>"<?=($id == $critere->getTypeDonnee() ? ' selected' : '')?>>
											<?=$this->c($label)?></option>
									<?php endforeach;?>
									</select>
								</td>
								<td align="left">Ordre</td>
								<td><input name="ordre" type="text" id="ordre" value="<?=$critere->getOrdre()?>" /></td>
							</tr>

							<tr>
								<td align="left">Type de cible</td>
								<td colspan="3" align="left"><?=$this->displayCheckbox('cibletype', $listCibleType, array_keys($critere->getListCibleType()))?></td>
							</tr>

							<tr>
								<td align="left">Désactive</td>
								<td align="left"><input type="checkbox" name="desactive" id=""desactive"" value="1"<?=($critere->isDesactive() ? ' checked="checked"' : '')?> /></td>
							</tr>

							<tr>
								<td align="left">Scoring</td>
								<td align="left"><input type="checkbox" name="scoring" id="scoring" value="1"<?=($critere->isScoring() ? ' checked="checked"' : '')?> /></td>
								<td align="left">Location</td>
								<td align="left"><input type="checkbox" name="location" id="location" value="1"<?=($critere->isLocation() ? ' checked="checked"' : '')?> /></td>
							</tr>

							<tr>
								<td align="left">Champ SQL</td>
								<td align="left"><input name="champsql" type="text" id="champsql" value="<?=$critere->getChampSql()?>" /></td>
							</tr>

							<tr>
								<td align="left">Code SQL</td>
								<td align="left" colspan="3"><textarea rows="5" cols="50" id="codesql" name="codesql"><?=$codeSql?></textarea></td>
							</tr>
						</table>
						<input type="hidden" name="id" value="<?=$critere->getId()?>">
						<input type="submit" class="btn" value="Valider">
					</form>
				</fieldset>

				<?php include 'edit/modesaisie.php'?>

				<div id="etape_valide">
					<span class="retour" ><a href="<?=Request::createUri('admin:critere:')?>"> &lt; retour</a></span>
				</div>
			</div>