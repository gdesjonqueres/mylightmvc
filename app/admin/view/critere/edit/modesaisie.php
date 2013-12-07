<?php
/* @var $mode Comptage_Model_ModeSaisie */
/* @var $critere Comptage_Model_Critere */
?>
			<div id="critere-args">
				<?php foreach ($listMode as $mode):?>
				<fieldset>
					<?php $critereArgs = $critere->getModeSaisieArgs($mode->getId());?>
					<legend class="<?=(!empty($critereArgs) ? 'open' : 'closed')?>">
						<?=$this->c($mode->getLibelle())?> (<?=$mode->getCode()?>)<?=(!empty($critereArgs) ? ' *' : '')?>
					</legend>
					<form action="<?=Request::createUri('admin:critere:edit')?>" method="post"<?=(empty($critereArgs) ? ' style="display:none;"' : '')?>>
						<table style="text-align:left;">
							<?php $args = $mode->getListArgs();
							foreach ($args as $arg):?>
							<tr>
								<td><?=$arg?></td>
								<td><textarea rows="3" cols="30" name="args[<?=$arg?>][valeur]"><?=(isset($critereArgs[$arg]) ? $critereArgs[$arg]['valeur'] : '')?></textarea></td>
								<td>
									<select style="width: 165px;" name="args[<?=$arg?>][type]">
										<option value="">-- Sélectionnez --</option>
									<?php foreach ($listArgType as $id => $label):?>
										<option value="<?=$id?>"<?=(isset($critereArgs[$arg]) && $id == $critereArgs[$arg]['type'] ? ' selected' : '')?>>
											<?=$this->c($label)?></option>
									<?php endforeach;?>
									</select>
								</td>
							</tr>
							<?php endforeach;?>
						</table>
						<input type="hidden" name="mode" value="modesaisie" />
						<input type="hidden" name="id" value="<?=$critere->getId()?>" />
						<input type="hidden" name="modesaisie" value="<?=$mode->getId()?>" />
						<input type="submit" class="btn" value="Enregistrer">
					</form>
				</fieldset>
				<?php endforeach;?>
			</div>