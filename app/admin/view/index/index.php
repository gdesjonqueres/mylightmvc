				<div class="clear"></div>
				<div id="content_block_nav">
					<nav class="admin">
						<h1>Administration</h1>
						<?php
						if(!empty($_GET["err"]) && !empty($userMsg))
							echo '<div class="userMsg">' . $userMsg . '</div>';
						?>
						<?php if($user->isAllowedAcces('gestion_utilisateurs')):?>
						<fieldset>
							<legend>Utilisateurs</legend>
							<a href="<?=Request::createUri('admin:user:')?>">Gestion des utilisateurs</a><br/>
							<a href="<?=Request::createUri('admin:user:add')?>">Création d'un nouvel utilisateur</a><br/>
							<a href="<?=Request::createUri('admin:userType:')?>">Gestion des types d'utilisateurs</a><br/>
						</fieldset>
						<?php endif;?>
						<?php if ($user->isAllowedAcces('gestion_societes')):?>
						<fieldset>
							<legend>Sociétés</legend>
							<a href="<?=Request::createUri('admin:societe:')?>">Gestion des sociétés</a><br/>
							<a href="<?=Request::createUri('admin:groupe:')?>">Gestion des groupes</a><br/>
						</fieldset>
						<?php endif;?>
						<?php if ($user->isAllowedAcces('gestion_criteres')):?>
						<fieldset>
							<legend>Critères</legend>
							<a href="<?=Request::createUri('admin:modesaisie:')?>">Gestion des modes de saisie</a><br/>
							<a href="<?=Request::createUri('admin:critere:')?>">Gestion des critères</a><br/>
						</fieldset>
						<?php endif;?>
						<?php if ($user->isAllowedAcces('gestion_campagnes')):?>
						<fieldset>
							<legend>Campagnes</legend>
							<a href="<?=Request::createUri('admin:campagne:listcampagnes', array('flag' => 'enattente'))?>">Les opérations en attente</a><br/>
							<a href="<?=Request::createUri('admin:campagne:listcampagnes', array('flag' => 'tout'))?>">Toutes les opérations</a><br/>
						</fieldset>
						<?php endif;?>
					</nav>
					<div id="etape_valide">
						<span class="retour" ><a href="<?=Request::createUri('::')?>"> &lt; retour</a></span>
					</div>
				</div>