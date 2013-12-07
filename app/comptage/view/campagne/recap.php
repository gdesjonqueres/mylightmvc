<?php
/* @var $cibleCritere Comptage_Model_Cible_Critere */
/* @var $critere Comptage_Model_Critere */
/* @var $myCampagne Comptage_Model_Campagne */
/* @var $myOperation Comptage_Model_Operation */
/* @var $myCible Comptage_Model_Cible */
/* @var $myExtraction Comptage_Model_Extraction */

$daoCritere = Comptage_Model_Critere_Dao::getInstance()->setRegistryEnabled(true);

if ($lightbox == 'oui') {
	$this->setLayout($appVars->DEFAULT_MODULE . '/view/layout/lightbox.php');
}

$this->addPlugin('jquery-ui', array('jquery-ui-1.10.1.custom.min.css', 'jquery-ui-1.10.1.custom.min.js'));
$this->addCss('comptage.css');

$this->addReadyEvent(
'$(".tooltip").tooltip({
	items: "[data-values]",
	content: function() {
		return Dtno.utils.formatTooltipValues($(this).data("values"));
	}
});');
?>
				<?php if ($lightbox != 'oui'):?>
				<div class="block_left">
					<?php include 'comptage/view/index/menu/menu.php';?>
				</div>
				<?php endif;?>

				<div<?=($lightbox != 'oui' ? ' class="content_bg"' : '')?> id="recap-campagne">

					<h1>R&eacute;sum&eacute; de la campagne</h1>

					<?php if (isset($message)):?>
					<div class="userMsg"><?=$this->c($message)?></div>
					<?php endif;?>

					<?php if ($user->canEditOperation($myOperation)):
						$url = $this->c(Request::createUri('comptage:campagne:load', array('id' => $myCampagne->getId())));?>
					<a href="<?=$lightbox == 'oui' ? "javascript:window.parent.location='$url'" : $url?>" title="charger la campagne">
						Modifier la campagne</a>
					<?php endif;?>

					<!-- Infos campagne -->
					<fieldset>
						<legend title="<?=$this->c($myCampagne->getLibelle())?>">
							<?=$this->c($this->truncate($myCampagne->getLibelle(), 40))?>
						</legend>
						<div class="label">Cr&eacute;&eacute;e le </div>
						<div class="value"><?=$this->formatDate($myCampagne->getDateCreation())?></div>
						<?php if ($myUser->getId() != $user->getId()):?>
						<div class="label">Par</div>
						<div class="value"><?=$this->c($myUser->getPrenom())?> <?=$this->c($myUser->getNom())?></div>
						<?php endif;?>
						<div class="label">Statut</div>
						<div class="value"><strong><?=$this->c($myOperation->getStatutLibelle())?></strong></div>

						<div class="separation"></div>

						<div class="label">Cible</div>
						<div class="value"><strong><?=$this->c($myOperation->getCibleTypeLibelle())?></strong></div>
						<div class="label">Potentiel</div>
						<div class="value"><?=($myOperation->getComptage() ? $this->formatNumber($myOperation->getComptage()) . ' adresses' : '-')?></div>

						<div class="separation"></div>

						<?php if(isset($myContactAssocie)):?>
						<div class="label">Comptage pour</div>
						<div class="value">
							<?=$this->c($myContactAssocie['societe'])?><br />
							<?=$this->c($myContactAssocie['prenom'])?> <?=$this->c($myContactAssocie['nom'])?>
							<?php if (!empty($myContactAssocie['email'])):?>
							(<?=$this->c($myContactAssocie['email'])?>)
							<?php endif;?>
							<?php if (!empty($myContactAssocie['tel'])):?>
							<br />Tel: <?=$this->c($myContactAssocie['tel'])?>
							<?php endif;?>
						</div>
						<?php endif;?>
					</fieldset>

					<!-- Infos cible -->
					<fieldset>
						<legend>Cible</legend>
						<?php foreach ($myCible as $idCritere => $cibleCritere):
							$valeurs = $cibleCritere->tabValeurs;
							$critere = $daoCritere->get($idCritere);
							if ($user->isAllowedCritere($idCritere)):?>
						<div class="label"><?=$critere->getLibelle()?></div>

							<?php if ($critere->getTypeValeur() == 'intervalle'):?>
						<div class="value">
								<?php if (isset($valeurs['min']) && isset($valeurs['max'])):?>
							De <em><?=current($valeurs['min'])?></em> à <em><?=current($valeurs['max'])?></em>
								<?php elseif(isset($valeurs['min'])):?>
							&gt;= à <em><?=current($valeurs['min'])?></em>
								<?php elseif(isset($valeurs['max'])):?>
							&lt;= à <em><?=current($valeurs['max'])?></em>
								<?php endif;?>
						</div>
							<?php elseif ($critere->getTypeValeur() == 'liste'):?>
						<div class="value">
							<a href="#" class="tooltip" data-values='<?=toJson($this->formatTooltipValues($valeurs))?>'>
								<span class="count"><?=$this->plural(count($valeurs), '%d valeur(s)')?></span></a>
						</div>
							<?php elseif ($critere->getTypeValeur() == 'switch'):?>
						<div class="value">Oui</div>
							<?php else:?>
						<div class="value">
								<?=current($valeurs)?>
						</div>
							<?php endif;?>
						<?php endif;
						endforeach;?>
					</fieldset>

					<!-- Infos extraction -->
					<?php if (isset($myExtraction)):
						$myFichier = $myExtraction->getFichier();?>
					<fieldset>
						<legend>Commande</legend>
						<div class="label">Quantit&eacute; command&eacute;e</div>
						<div class="value"><strong><?=$this->formatNumber($myExtraction->getQuantite())?></strong> adresses</div>

						<?php if (!empty($myFichier->nom)):?>
						<div class="label">Fichier</div>
						<div class="value">
							<?php if ($user->isAllowedAcces('telechargement')):?>
							<a title="t&eacute;l&eacute;charger" href="<?=$appVars->URL_SCRIPTS?>getFile.php?id=<?=$myOperation->getId()?>">
								<?=$this->c($myFichier->nom)?></a>
							<?php else:?>
							<?=$this->c($myFichier->nom)?>
							<?php endif;?>
						</div>
						<?php endif;?>

						<div class="label">Format du fichier</div>
						<div class="value">
							<?=$this->c($fichierListeType[$myFichier->type])?>
							<?php if ($myFichier->type == 'excel') {
								echo ', ' . $fichierListeOptions['excel']['format']['values'][$myFichier->options['format']];
							}
							elseif ($myFichier->type == 'txt') {
								echo ', ' . $fichierListeOptions['txt']['separator']['values'][$myFichier->options['separator']];
							}?>
						</div>

						<div class="label">Destinataire</div>
						<div class="value">
							<strong><?=$this->c($myContactDestinataire['prenom'])?> <?=$this->c($myContactDestinataire['nom'])?></strong>
							<?php if (!empty($myContactDestinataire['email'])):?>
							(<?=$this->c($myContactDestinataire['email'])?>)
							<?php endif;?>
							<?php if (!empty($myContactDestinataire['tel'])):?>
							<br />Tel: <?=$this->c($myContactDestinataire['tel'])?>
							<?php endif;?>
						</div>
					</fieldset>
					<?php endif;?>

				</div>