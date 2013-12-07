<?php
/* @var $myCritere Comptage_Model_Critere */
$this->setLayout(null);

?>
            <div class="myLightbox">
				<form id="formMode" method="post" action="<?=Request::createUri('comptage:modesaisie:autocomplete',
																					array('critere' => $idCritere))?>">

					<fieldset>

						<?=(isset($desc) ? '<p class="description">' . $this->c($desc) . '</p>' : '')?>

						<div class="content" style="width: 400px; min-height: 60px;">
							<?=$this->displayAutoComplete('autocom', 'comptage:modesaisie:autocompleteJson', array('critere' => $idCritere))?>
						</div>

					</fieldset>

					<input type="submit" class="btn" value="valider" />

				</form>
            </div>