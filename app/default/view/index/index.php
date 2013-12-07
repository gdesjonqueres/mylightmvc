				<div class="clear"></div>

				<div id="content_block_nav">
					<div class="gradient_bg">
					<h1>Menu</h1>
					<div id="menu_principal">
						<!-- <a href="espace-perso/mon-compte.php">
							<div class="menu_block">
								<div class="menu_illustration_block" >
									<img src="<?=$appVars->URL_IMG?>index/ico-compte.png" >
								</div>
								<label>Mon compte</label>
							</div>
						</a> -->
						<?php if (isset($user) && ($user->isAllowedAcces('extraction') || $user->isAllowedAcces('comptage'))):?>
						<a href="<?=Request::createUri('comptage::')?>">
							<div class="menu_block">
								<div class="menu_illustration_block" >
									<img src="<?=$appVars->URL_IMG?>index/ico-extraction.png" >
								</div>
								<label>Comptage</label><br/>
								<label>et extraction</label>
							</div>
						</a>
						<?php endif;?>
						<?php if (isset($user) && $user->isAllowedAcces('fichiers_clients')):?>
						<a href="mon-compte.php">
							<div class="menu_block">
								<div class="menu_illustration_block" >
									<img src="<?=$appVars->URL_IMG?>index/ico-fichiers-clients.png" >
								</div>
								<label>Mes fichiers </label><br/>
								<label> clients</label>
							</div>
						</a>
						<?php endif;?>
						<?php if (isset($user) && $user->isAllowedAcces('historique')):?>
						<a href="mon-compte.php">
							<div class="menu_block">
								<div class="menu_illustration_block" >
									<img src="<?=$appVars->URL_IMG?>index/ico-historique.png" >
								</div>
								<label>Historique</label>
							</div>
						</a>
						<?php endif;?>
						<?php if (isset($user) && $user->isAllowedAcces('administration')):?>
						<a href="<?=Request::createUri('admin::')?>">
							<div class="menu_block">
								<div class="menu_illustration_block" >
									<img src="<?=$appVars->URL_IMG?>index/ico-admin.png" >
								</div>
								<label>Administration</label>
							</div>
						</a>
						<?php endif;?>
						<?php if (isset($user) && $user->isAllowedAcces('redevance')):?>
						<a href="mon-compte.php">
							<div class="menu_block">
								<div class="menu_illustration_block" >
									<img src="<?=$appVars->URL_IMG?>index/ico-redevance.png" >
								</div>
								<label>Redevance</label>
							</div>
						</a>
						<?php endif;?>
					</div>
				</div>
				</div>