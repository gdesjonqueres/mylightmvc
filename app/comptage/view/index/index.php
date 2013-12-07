<?php
/* @var $myCampagne Comptage_Model_Campagne */
?>
				<div class="clear"></div>

				<div class="block_left">
					<?php include 'menu/menu.php';?>
				</div>

				<div class="content_bg">
					<h1>Comptage</h1>

					<fieldset>
						<legend>Compte associé</legend>
						<table style="text-align:left;">
							<tr>
								<td>Société :</td><td>Dataneo</td>
							</tr>
							<tr>
								<td>Responsable :</td><td>J. F. Kennedy </td>
							</tr>
						</table>
					</fieldset>

					<?php if (isset($myCampagne)):?>
					<fieldset>
						<legend>Campagne en cours:</legend>
						<table style="text-align:left;">
							<tr>
								<td>Id:</td>
								<td><?=$myCampagne->getLibelle()?></td>
								<td><?=$this->plural(count($myCampagne->getCurrentOperation()), '%d cible(s)', 'Aucune cible')?></td>
							</tr>
						</table>
					</fieldset>

					<div id="etape_valide">
						<a href="<?=Request::createUri('comptage:cible:')?>">
							<span class="suivant"><input type="submit" class="btn" value="Gérer les cibles" /></span>
						</a>
					</div>
					<?php else:?>
					<div id="etape_valide">
						<a href="<?=Request::createUri('comptage:campagne:add')?>">
							<span class="suivant"><input type="submit" class="btn" value="Nouv. campagne" /></span>
						</a>
					</div>
					<?php endif;?>

				</div>