<div id="menuTopDroite">
	<div class="infoUser">
		<div class="info-societe">
			<label>
				<?php if ($user->getIdGroupe() && $user->isAllowedAcces('change_societe')):
					$list = Admin_Model_Societe::getListByGroupe($user->getIdGroupe());?>
					<form method="post" action="<?=Request::createUri('::changeSociete')?>">
						<select style="width: 165px;" name="id_societe" onChange="document.forms[0].submit();">
						<?php foreach ($list as $societe):?>
							<option value="<?=$societe->getId()?>"<?=($societe->getId() == $user->getSociete()->getId() ? ' selected="selected"' : '')?>>
								<?=$this->c($societe->getRaisonSociale())?></option>
						<?php endforeach;?>
						</select>
					</form>
				<?php else:?>
					<?=$this->c($user->getSociete()->getRaisonSociale())?>
				<?php endif;?>
			</label>
		</div>
		<div class="info-deco" ><a href="<?=Request::createUri('::logout')?>">Déconnexion</a></div>
		<div class="clear"> </div>
		<div class="info-nom" ><?=$this->c($user->getPrenom() . ' ' . $user->getNom())?></div>
		<div class="info-num" ><img src="<?=$appVars->URL_IMG?>num_cristal.png" width="190" alt="09 69 39 03 06"></div>
		<div class="clear"></div>
	</div>
	<?php if ($user->isAllowedAcces('administration')):?>
		<div class="switch demo1">
			<input type="checkbox" title="Mode debug"<?=($user->isDebugMode() ? ' checked="checked"' : '')?>
				onclick="window.location.href='<?=Request::createUri('admin::toggleDebug')?>'">
			<label></label>
		</div>
	<?php endif;?>
</div>
