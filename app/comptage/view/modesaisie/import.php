<?php
/* @var $myCritere Comptage_Model_Critere */

$this->setTitle($this->c($myCritere->getLibelle()));
$this->setLayout($appVars->DEFAULT_MODULE . '/view/layout/lightbox.php');
?>
				<form method="post" action="<?=Request::createUri('comptage:modesaisie2:import',
																	array('critere' => $idCritere))?>">

					<fieldset>

						<?=(isset($titre) ? '<p>' . $this->c($titre) . '</p>' : '')?>

						<p>Saisissez les codes dans le champ ci-dessous ou copiez votre sélection à partir d'un fichier.<br />
								Puis validez pour lancer l'import. (séparateurs disponibles : "," ";" ou "&crarr;")</p>

						<?=(isset($desc) ? '<p class="description">' . $this->c($desc) . '</p>' : '')?>

						<div class="content" style="width: 660px;">
							<textarea name="valeurs" id="valeurs" rows="8" cols="75"><?=$valeurs?></textarea>
						</div>

					</fieldset>

					<input type="submit" class="btn" value="valider" />

					<?php if (isset($message)):?>
					<div><?=$message?></div>
					<?=$this->displayScript('parent.Dtno.controllers.cible.isChanged = true;')?>
					<?php endif;?>

				</form>