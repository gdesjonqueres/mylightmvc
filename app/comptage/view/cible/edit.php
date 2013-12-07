<?php
/* @var $cibleCritere Comptage_Model_Cible_Critere */
/* @var $cible Comptage_Model_Cible */
/* @var $critere Comptage_Model_Critere */
/* @var $myCible Comptage_Model_Cible */
/* @var $myCampagne Comptage_Model_Campagne */
/* @var $myOperation Comptage_Model_Operation */

$isComptageDone = ($myOperation->getComptage() > 0);
$isContactSet = ($myOperation->getContactId());

$this->addPlugin('jquery-ui', array('jquery-ui-1.10.1.custom.min.css', 'jquery-ui-1.10.1.custom.min.js'));
$this->addPlugin('colorbox', array('colorbox.css', 'jquery.colorbox-min.js'));
$this->addPlugin('jEditable', 'jquery.jeditable-min.js');

$this->addCss('comptage.css');
$this->addJs('comptage/cible/edit.js');
$this->addJs('comptage/campagne.js');

$this->addJs('comptage/modesaisie/modesaisie.js');
$this->addCss('modesaisie.css');

$this->addCss('helper/autocomplete.css');
$this->addJs('helper/dtno.autocomplete.js');

$this->addPlugin('prettyCheckboxes', array('prettyCheckboxes.css', 'prettyCheckboxes.js'));
$this->addCss('helper/prettycb.css');

$this->addCss('helper/intervalle.css');
$this->addJs('helper/dtno.intervalle.js');

$this->addPlugin('tree', array('tree.css', 'jquery.tree.js'));
$this->addJs('helper/dtno.tree.js');
?>
				<div class="block_left">
					<?php include 'menu/criteres.php';?>
					<?php include 'comptage/view/index/menu/menu.php';?>
				</div>

				<?php /*include 'menu/recap.php';*/?>

				<div class="content_bg">

					<div id="timeline">
						<ul>
							<li><a href="<?=Request::createUri('comptage:cible:')?>">Comptage</a> &gt;</li>
							<li class="selected"><a href="<?=Request::createUri('comptage:cible:edit')?>">Cible</a> &gt;</li>
							<?php if ($isComptageDone):?>
							<li><a href="<?=Request::createUri('comptage:extraction:')?>">Extraction</a></li>
							<?php else:?>
							<li>Extraction</li>
							<?php endif;?>
						</ul>
					</div>

					<div id="destinataire">
						<div class="header">
							<a class="iframe edit" href="<?=Request::createUri('comptage:operation:listcontacts')?>" title="associer un client &agrave; l'extraction">Compte associé</a>
						</div>
						<div class="content">
						<?php if ($myOperation->getContactId()):
							$contact = Comptage_Model_Contact::get($myOperation->getContactId());?>
							<div class="societe"><?=$this->c($contact['societe'])?></div>
							<div class="nom"><?=$this->c($contact['nom'])?> <?=$this->c($contact['prenom'])?></div>
						<?php else:?>
							Veuillez associer un compte
						<?php endif;?>
						</div>
					</div>

					<div id="messageZone"></div>

					<section id="ma-campagne">
						<header>
							<h1 id="ma-campagne-libelle" class="editable" title="Cliquez ici pour changer le nom de la campagne..."><?=$this->c($myCampagne->getLibelle())?></h1>
						</header>

						<div class="content">
							<div id="ma-campagne-caracteristiques">
								<div style="border-right: 1px dotted #545454; padding-right: 10px;" title="cible" class="type-cible"><?=$myOperation->getCibleTypeLibelle()?></div>
								<div title="opération" class="type-op"><?=$myOperation->getTypeLibelle()?></div>
								<div title="données" class="type-donnees"><?=$myOperation->getDonneesTypeLibelle()?></div>
							</div>

							<div id="ma-campagne-resultats-comptage" class="right">
							<?php if ($isComptageDone):?>
								<span class="badge"><?=$this->formatNumber($myOperation->getComptage())?></span> <br />
								adresses sélectionnées
							<?php else:?>
								Aucun comptage
							<?php endif;?>
							</div>

							<p class="infos">
								Créée le: <em><?=$this->formatDate($myCampagne->getDateCreation())?></em>, statut: <em><?=$myOperation->getStatutLibelle()?></em>
								<button type="button" class="save btn-reset" onClick="Dtno.models.campagne.save()" title="enregistrer la campagne"><i class="icon-disk"></i></button>
							</p>
						</div>
					</section>

					<section id="ma-cible">
						<header>
							<h1 class="gradient">Ma cible</h1>
							<!-- <a href="<?=$this->c(Request::createUri('comptage:cible:add'))?>" class="add" title="ajouter une nouvelle cible">Ajouter</a> -->
						</header>

						<div>

							<?php foreach ($myOperation as $cible):?>
							<article<?=($cible === $myCible ? ' class="selected"' : '')?>>
								<div class="rank title">
									<h2>
									<?php if ($cible === $myCible):?>
										<?=($cible->getRank($myOperation) + 1)?>
									<?php else:?>
										<a class="edit" href="<?=$this->c(Request::createUri('comptage:cible:edit',
																							array('cible' => $cible->getId())))?>" title="editer cette cible">
											<?=($cible->getRank($myOperation) + 1)?><span class="editBtnSmall"></span></a>
									<?php endif;?>
									</h2>
								</div>

								<?php if (count($myOperation) > 1):?>
								<a href="<?=Request::createUri('comptage:cible:remove',
																array('cible' => $cible->getId()))?>" class="delete" title="supprimer cette cible" >
									<span class="deleteBtn"></span></a>
								<?php endif;?>

								<div id="cible-en-cours">
									<?php include 'edit/cible.php';?>
								</div>

							</article>
							<?php endforeach;?>

							<?php if (count($myOperation) == 0):?>
								<p>Aucune cible définie</p>
							<?php endif;?>

						</div>

					</section>

					<div id="etape_valide">
						<span class="retour" ><a href="<?=Request::createUri('comptage:cible:')?>"> &lt; retour</a></span>
						<span class="suivant">
							<button id="btnComptage" type="button" class="btn" title="effectuer le comptage"<?=($isComptageDone ? ' style="display: none;"' : '')?> data-iscontactset="<?=($isContactSet ? 'true' : 'false')?>">Comptage</button>
							<a id="btnSuivant" href="<?=Request::createUri('comptage:extraction:')?>"<?=(!$isComptageDone ? ' style="display: none;"' : '')?>>suivant &gt;</a>
						</span>
					</div>

				</div>