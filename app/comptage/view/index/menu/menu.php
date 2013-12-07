<?php
/* @var $cpg Comptage_Model_Campagne */
/* @var $this View */

$listCpgMenu = Comptage_Model_Campagne_Dao::getInstance()->getList(	array('user' => Session::getUser()->getId()),
																	array('id' => 'desc'), 3);
?>
<nav id="menu-site" class="menu gradient">
	<h1>Menu</h1>

	<h2>Mes campagnes</h2>
	<ul id="mes-campagnes">
		<?php if (count($listCpgMenu) > 0):
			foreach ($listCpgMenu as $cpg):?>
		<li>
			<span class="libelle">
				<?php if (Comptage_Model_Operation::isEditableState($cpg['statut_id'])):?>
				<a href="<?=Request::createUri('comptage:campagne:load',
												array('id' => $cpg['id_campagne']))?>" title="charger la campagne <?=$this->c('"' . $cpg['libelle'] . '"')?>">
					<?=$this->c($this->truncate($cpg['libelle'], 17))?></a>
				<?php else:?>
				<a href="<?=Request::createUri('comptage:campagne:recap',
												array('id' => $cpg['id_campagne']))?>" title="voir la campagne <?=$this->c('"' . $cpg['libelle'] . '"')?>">
					<?=$this->c($this->truncate($cpg['libelle'], 17))?></a>
				<?php endif;?>
			</span>
			<span class="date"><?=$this->c($cpg['date'], 'd/m/Y')?></span>
			<div class="clear"></div>
		</li>
		<?php endforeach;
		else:?>
		<li>Aucune campagne en cours</li>
		<?php endif;?>
		<li><a href="<?=Request::createUri('comptage:campagne:listcampagnes')?>" class="list" title="Voir toutes mes campagnes">Voir toutes mes campagnes</a></li>
		<li><a href="<?=Request::createUri('comptage:campagne:add')?>" class="add" title="Créer une nouvelle campagne">
			Créer une <em>nouvelle</em> campagne</a></li>
	</ul>

	<!-- <h2>Mon compte</h2>
	<ul id="mon-compte">
		<li><a href="#">Mes factures</a></li>
		<li><a href="#">Mes coordonnées</a></li>
		<li><a href="#">Aide</a></li>
	</ul> -->
</nav>