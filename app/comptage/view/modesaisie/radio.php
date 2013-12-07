<?php
/* @var $myCritere Comptage_Model_Critere */

$this->setLayout(null);
?>
                <div class="myLightbox">
					<form id="formMode" method="post" action="<?=Request::createUri('comptage:modesaisie:radio',
																		array('critere' => $idCritere))?>">
						<fieldset>

							<?=(isset($desc) ? '<p class="description">' . $this->c($desc) . '</p>' : '')?>

							<div class="content">
								<?=$this->displayPrettyRadio('valeurs', $liste, $valeurs)?>
							</div>

							<?php if ($myCritere->isScoring()):?>
							<div class="infos">Données enrichies par scoring</div>
							<?php endif;?>

							<div style="clear: both;"></div>

						</fieldset>

						<input type="submit" class="btn" value="valider" />

					</form>
				</div>