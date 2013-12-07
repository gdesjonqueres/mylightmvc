<?php
$this->addPlugin('prettyCheckboxes', array('prettyCheckboxes.css', 'prettyCheckboxes.js'));
$this->addReadyEvent('$(".content_bg input[type=radio]").prettyCheckboxes({checkboxWidth: 17, checkboxHeight: 17});');
?>
				<div class="block_left">
					<?php include 'comptage/view/index/menu/menu.php';?>
				</div>

				<?php /*include 'menu/recap.php';*/?>

				<div class="content_bg">
					<h1>Choisissez votre cible</h1>
					<form method="post" action="<?=Request::createUri('comptage:cible:type')?>">
						<div style="width: 500px; min-height: 150px; margin: auto;">
						<?php $i = 0;
						foreach ($listeCibleType as $code => $label):?>
							<div class="bigCheckbox" style="float:<?=($i % 2 ? 'right' : 'left')?>;">
								<input type="radio" name="cibletype" value="<?=$code?>" id="cible-<?=$code?>"<?=($cibletype == $code ? ' checked="checked"' : '')?>>
								<label for="cible-<?=$code?>"><?=$label?></label>
							</div>
						<?php $i++;
						endforeach;?>
						</div>

						<div class="clear"></div>

						<div class="separation"></div>
						<div id="etape_valide">
							<!-- <span class="retour"><a href="<?=Request::createUri('comptage::')?>"> &lt; retour</a></span> -->
							<span class="suivant"><input type="submit" class="btn" value="suivant" /></span>
						</div>
					</form>
				</div>