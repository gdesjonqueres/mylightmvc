<?php
/* @var $myCritere Comptage_Model_Critere */
/* @var $myMode Comptage_Model_ModeSaisie */

$this->setLayout(null);
?>
                <div class="myLightbox">
					<form id="formMode" method="post" action="<?=Request::createUri('comptage:modesaisie:intervalle',
																						array('critere' => $idCritere))?>">
						<fieldset>

							<?=(isset($desc) ? '<p class="description">' . $this->c($desc) . '</p>' : '')?>

							<div class="content" style="width: 520px; height: 95px;">
								<?=$this->displayIntervalle('intervalle', $liste, $min, $max)?>
							</div>

							<?php if ($myCritere->isScoring()):?>
							<div class="infos">Données enrichies par scoring</div>
							<?php endif;?>

						</fieldset>

						<input type="submit" class="btn" value="valider" />

					</form>
				</div>
