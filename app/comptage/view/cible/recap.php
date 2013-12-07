<?php
/* @var $cibleCritere Comptage_Model_Cible_Critere */
/* @var $cible Comptage_Model_Cible */
/* @var $critere Comptage_Model_Critere */
/* @var $myCible Comptage_Model_Cible */
/* @var $myCampagne Comptage_Model_Campagne */
/* @var $myOperation Comptage_Model_Operation */

$daoCritere = Comptage_Model_Critere_Dao::getInstance()->setRegistryEnabled(true);
$isComptageDone = ($myOperation->getComptage() > 0);
$contactOperation = Comptage_Model_Contact::get($myOperation->getContactId());

$this->addPlugin('jquery-ui', array('jquery-ui-1.10.1.custom.min.css', 'jquery-ui-1.10.1.custom.min.js'));
$this->addCss('comptage.css');

$this->addReadyEvent(
'$(".tooltip").tooltip({
	items: "[data-values]",
	content: function() {
		return Dtno.utils.formatTooltipValues($(this).data("values"));
	}
});');

$this->addReadyEvent(
'$("#set-editable").click(function(event) {
	//event.preventDefault();
	if (confirm("Attention, cette action va annuler le comptage en cours. Continuer ?")) {
		return true;
	}
	else {
		return false;
	}
});');
?>
				<div class="block_left">
					<?php include 'comptage/view/index/menu/menu.php';?>
				</div>

				<div class="content_bg">

					<div id="timeline">
						<ul>
							<li>Comptage &gt;</li>
							<li class="selected"><a href="<?=Request::createUri('comptage:cible:recap')?>">Cible</a> &gt;</li>
							<li><a href="<?=Request::createUri('comptage:extraction:')?>">Extraction</a></li>
						</ul>
					</div>

					<div id="destinataire">
						<div class="header">Compte associé</div>
						<div class="content">
							<div class="societe"><?=$this->c($contactOperation['societe'])?></div>
							<div class="nom"><?=$this->c($contactOperation['nom'])?> <?=$this->c($contactOperation['prenom'])?></div>
						</div>
					</div>

					<section id="ma-campagne">
						<header>
							<h1 id="ma-campagne-libelle"><?=$this->c($myCampagne->getLibelle())?></h1>
						</header>

						<div class="content">
							<div id="ma-campagne-caracteristiques">
								<div style="border-right: 1px dotted #545454; padding-right: 10px;" title="cible" class="type-cible"><?=$myOperation->getCibleTypeLibelle()?></div>
								<div title="opération" class="type-op"><?=$myOperation->getTypeLibelle()?></div>
								<div title="données" class="type-donnees"><?=$myOperation->getDonneesTypeLibelle()?></div>
							</div>

							<div id="ma-campagne-resultats-comptage" class="right">
								<span class="badge"><?=$this->formatNumber($myOperation->getComptage())?></span> <br />
								adresses sélectionnées
							</div>

							<p class="infos">
								Créée le: <em><?=$this->formatDate($myCampagne->getDateCreation())?></em>, statut: <em><?=$myOperation->getStatutLibelle()?></em>
							</p>
						</div>
					</section>

					<section id="ma-cible">
						<header>
							<h1 class="gradient">Ma cible</h1>
						</header>

						<?php if ($user->canEditOperation($myOperation)):?>
							<p style="text-align: left;"><a href="<?=Request::createUri('comptage:operation:seteditable')?>" id="set-editable" title="editer la cible">Passer en modification</a></p>
						<?php endif;?>

						<div>

							<?php foreach ($myOperation as $cible):?>
							<article<?=($cible === $myCible ? ' class="selected"' : '')?>>
								<div class="rank title">
									<h2><?=($cible->getRank($myOperation) + 1)?></h2>
								</div>

								<div>

									<?php foreach ($cible as $idCritere => $cibleCritere):
										$valeurs = $cibleCritere->tabValeurs;
										$critere = $daoCritere->get($idCritere);
										if ($user->isAllowedCritere($idCritere)):?>
									<div class="critere">
										<div class="title"><?=$this->c($critere->getLibelle())?></div>
										<div class="value">
										<?php if ($critere->getTypeValeur() == 'intervalle'):?>
											<?php if (isset($valeurs['min']) && isset($valeurs['max'])):?>
											De <em><?=current($valeurs['min'])?></em> à <em><?=current($valeurs['max'])?></em>
											<?php elseif(isset($valeurs['min'])):?>
											&gt;= à <em><?=current($valeurs['min'])?></em>
											<?php elseif(isset($valeurs['max'])):?>
											&lt;= à <em><?=current($valeurs['max'])?></em>
											<?php endif;?>
										<?php elseif ($critere->getTypeValeur() == 'liste'):?>
											<?php if ($cible === $myCible):?>
											<a href="#" class="tooltip" data-values='<?=toJson($this->formatTooltipValues($valeurs))?>'>
												<span class="count"><?=$this->plural(count($valeurs), '%d valeur(s)')?></span></a>
											<?php else:?>
												<a href="#" class="tooltip" data-values='<?=toJson($this->formatTooltipValues($valeurs))?>'>
													<span class="count"><?=$this->plural(count($valeurs), '%d valeur(s)')?></span></a>
											<?php endif;?>
										<?php elseif ($critere->getTypeValeur() == 'switch'):?>
											Oui
										<?php else:?>
											<?=current($valeurs)?>
										<?php endif;?>
										</div>
									</div>
									<?php endif;
									endforeach;?>

									<?php if (count($cible) == 0):?>
										<p>Aucun critère sur cette cible</p>
									<?php endif;?>

								</div>
							</article>
							<?php endforeach;?>

							<?php if (count($myOperation) == 0):?>
								<p>Aucune cible définie</p>
							<?php endif;?>

						</div>

					</section>

					<div id="etape_valide">
						<span class="suivant">
							<a id="btnSuivant" href="<?=Request::createUri('comptage:extraction:')?>">suivant &gt;</a>
						</span>
					</div>

				</div>