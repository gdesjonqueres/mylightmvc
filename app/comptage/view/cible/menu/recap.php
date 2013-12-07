<?php
/* @var $this View */
/* @var $myCampagne Comptage_Model_Campagne */
/* @var $myOperation Comptage_Model_Operation */
/* @var $cible Comptage_Model_Cible */
/* @var $myCible Comptage_Model_Cible */
/* @var $cibleCritere Comptage_Model_Cible_Critere */
/* @var $critere Comptage_Model_Critere */

$js = <<<'EOT'
	$(".delete").on("mouseover", function(event) {
		$(this).siblings(".title").addClass("todelete");
	});
	$(".delete").on("mouseout", function(event) {
		$(this).siblings(".title").removeClass("todelete");
	});
EOT;
$this->addReadyEvent($js);

$daoCritere = Comptage_Model_Critere_Dao::getInstance()->setRegistryEnabled(true);
?>
			<div id="menu-recap">

				<div class="top"></div>

				<div class="middle">
					<h2><strong>Récapitulatif</strong> <br />
						de la campagne</h2>

					<article class="macampagne">
						<header>
							<?=$myOperation->getTypeLibelle()?>
							<a class="save" href="#" onClick="myCpg.doSave()" title="enregistrer la campagne"></a>
						</header>
						<p class="infos">Créée le <em><?=$this->formatDate($myCampagne->getDateCreation())?></em>, <br />
							statut <em><?=$myCampagne->getStatutLibelle()?></em></p>
						<?php if ($myOperation->getCibleType()):?>
						<p><span class="cible" title="cible: <?=$myOperation->getCibleTypeLibelle()?>">
							<?=$myOperation->getCibleTypeLibelle()?></span></p>
						<?php endif;?>
					</article>

				<?php if (isset($myCible) && $this->_request->getAction() == 'edit'):?>
					<section class="mescriteres">
						<header>Cible n°<?=($myCible->getRank($myOperation) + 1)?></header>
						<?php if (count($myCible) >= 1):?>
						<nav>
							<ul class="top">
							<?php foreach ($myCible as $idCritere => $cibleCritere):
								$valeurs = $cibleCritere->tabValeurs;
								$critere = $daoCritere->get($idCritere);?>
								<li>
									<span>
										<span class="title"><?=$critere->getLibelleCourt()?></span>
										<a class="delete" href="#" onClick="myCpg.doCritereRemove('<?=$critere->getId()?>')" title="supprimer le critère">
											<span class="btndelete"></span></a>
									</span>

									<ul class="nested">
										<li>
										<?php if ($critere->getTypeValeur() == 'intervalle'):?>
											<?php if (isset($valeurs['min']) && isset($valeurs['max'])):?>
											De <em><?=current($valeurs['min'])?></em> à <em><?=current($valeurs['max'])?></em>
											<?php elseif(isset($valeurs['min'])):?>
											&gt; à <em><?=current($valeurs['min'])?></em>
											<?php elseif(isset($valeurs['max'])):?>
											&lt; à <em><?=current($valeurs['max'])?></em>
											<?php endif;?>
										<?php elseif ($critere->getTypeValeur() == 'liste'):?>
											<a href="<?=$this->c(Request::createUri('comptage:critere:editvaleurs',
																					array('critere' => $critere->getId())))?>" class="iframeReload" title="voir le détail">
												<span class="count"><?=$this->plural(count($valeurs), '%d valeur(s) sélectionnée(s)')?></span></a>
										<?php elseif ($critere->getTypeValeur() != 'switch'):?>
											<?=current($valeurs)?>
										<?php endif;?>
										</li>
									</ul>
								</li>
							<?php endforeach;?>
							</ul>
						</nav>
						<?php else:?>
						Aucun critère sélectionné
						<?php endif;?>
					</section>

					<?php if (count($myOperation) > 1):?>
					<section class="mescibles">
						<header>Mes autres cibles (<?=(count($myOperation) - 1)?>)</header>
						<nav>
							<ul class="top">
							<?php foreach ($myOperation as $cible):?>
								<?php if ($cible !== $myCible):?>
								<li>
									<span>
										<span class="title">
											<a href="<?=$this->c(Request::createUri('comptage:cible:edit',
																					array('cible' => $cible->getId())))?>" title="modifier la cible">
											Cible n°<?=($cible->getRank($myOperation) + 1)?></a></span>
										<a class="delete" href="<?=Request::createUri('comptage:cible:remove',
																						array('cible' => $cible->getId()))?>" title="supprimer la cible">
											<span class="btndelete"></span></a>
									</span>
									<ul class="nested">
										<li class="count"><?=$this->plural(count($cible), '%d critère(s)', 'Aucun critère')?></li>
									</ul>
								</li>
								<?php endif;?>
							<?php endforeach;?>
							</ul>
						</nav>
					</section>
					<?php endif;?>

					<p class="addcible">
						<a href="<?=$this->c(Request::createUri('comptage:cible:add'))?>" class="add" title="ajouter une nouvelle cible">
							Ajouter une cible</a>
					</p>
				<?php endif;?>

				</div>

				<div class="bottom"></div>
				<div class="comptage"></div>
			</div>