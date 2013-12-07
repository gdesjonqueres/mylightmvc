<?php
/* @var $myCritere Comptage_Model_Critere */

$this->setLayout(null);
?>
                <div class="myLightbox">
					<form id="formMode" method="post" action="<?=Request::createUri('comptage:modesaisie:tree',
																					array('critere' => $idCritere))?>">
						<fieldset>

							<?=(isset($desc) ? '<p>' . $this->c($desc) . '</p>' : '')?>

							<div class="content" style="min-height: 200px; max-height: 310px; min-width:400px; overflow: auto;">
								<?=$this->displayTree('myTree', $liste, $valeurs)?>
							</div>

						</fieldset>

						<div class="hidden"></div>

						<input type="submit" class="btn" value="valider" />

					</form>
				</div>