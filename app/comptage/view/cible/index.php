<?php
/* @var $critere Comptage_Model_Critere */
/* @var $cible Comptage_Model_Cible */
/* @var $campagne Comptage_Model_Campagne */
/* @var $myOperation Comptage_Model_Operation */
?>
				<div class="block_left">
					<?php include 'comptage/view/index/menu/menu.php';?>
				</div>

				<div class="content_bg">
					<h1>Comptage</h1> <br/>

					<fieldset>
						<legend>Cibles</legend>

						<h2 style="text-align: left;"><?=$this->plural(count($myOperation), '%d cible(s) en cours', 'Aucune cible')?></h2>
						<?php if (count($myOperation) >= 1):?>
						<p>Sélectionnez une cible: </p>
						<ul>
							<?php foreach ($myOperation as $cible):?>
							<li><a href="<?=Request::createUri('comptage:cible:edit',
																array('cible' => $cible->getId()))?>"><?=$cible->getId()?></a></li>
							<?php endforeach;?>
						</ul>
						<?php endif;?>

						<p style="margin-top: 15px;"><a title="ajouter une cible" class="add" href="<?=Request::createUri('comptage:cible:add')?>">Ajouter une cible</a></p>

					</fieldset>

					<div id="etape_valide">
						<span class="retour" ><a href="<?=Request::createUri('comptage::')?>"> &lt; retour</a></span>
						<?php if (count($myOperation) >= 1):?>
						<span class="suivant"><input title="Effectuer le comptage" type="submit" class="btn" value="Comptage" /></span>
						<?php endif;?>
					</div>

				</div>