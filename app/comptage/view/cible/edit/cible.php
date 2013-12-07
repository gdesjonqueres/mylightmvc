<?php
/* @var $cibleCritere Comptage_Model_Cible_Critere */
/* @var $cible Comptage_Model_Cible */
/* @var $critere Comptage_Model_Critere */
/* @var $myCible Comptage_Model_Cible */
/* @var $myCampagne Comptage_Model_Campagne */
/* @var $myOperation Comptage_Model_Operation */

$daoCritere = Comptage_Model_Critere_Dao::getInstance()->setRegistryEnabled(true);
$cntCrit = 0;
?>
									<?php foreach ($cible as $idCritere => $cibleCritere):
										$valeurs = $cibleCritere->tabValeurs;
										$critere = $daoCritere->get($idCritere);
										if ($user->isAllowedCritere($idCritere)):
											$cntCrit++;?>
									<div class="critere">
										<div class="title"><?=$critere->getLibelle()?></div>
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
											<a href="<?=$this->c(Request::createUri('comptage:critere:editvaleurs',
																					array('critere' => $critere->getId())))?>" class="iframe tooltip edit" data-values='<?=toJson($this->formatTooltipValues($valeurs));?>'>
												<span class="count"><?=$this->plural(count($valeurs), '%d valeur(s)')?></span></a>
											<?php else:?>
												<a href="#" class="tooltip" data-values='<?=toJson($this->formatTooltipValues($valeurs));?>'>
													<span class="count"><?=$this->plural(count($valeurs), '%d valeur(s)')?></span></a>
											<?php endif;?>
										<?php elseif ($critere->getTypeValeur() == 'switch'):?>
											Oui
										<?php else:?>
											<?=current($valeurs)?>
										<?php endif;?>
										</div>
										<?php if ($cible === $myCible):?>
										<button type="button" class="delete btn-reset" onClick="Dtno.models.critere.remove('<?=$critere->getId()?>')" title="supprimer">
											<i class="icon-delete-small icon-small"></i></button>
										<?php endif;?>
									</div>
									<?php endif;
									endforeach;?>

									<?php if ($cntCrit == 0):?>
										<?php if ($cible === $myCible):?>
										<p>Définissez votre cible.<br />Choisissez les critères dans le menu gauche</p>
										<?php else:?>
										<p>Aucun critère sélectionné sur cette cible</p>
										<?php endif;?>
									<?php endif;?>