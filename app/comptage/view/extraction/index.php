<?php
/* @var $cibleCritere Comptage_Model_Cible_Critere */
/* @var $cible Comptage_Model_Cible */
/* @var $critere Comptage_Model_Critere */
/* @var $myCible Comptage_Model_Cible */
/* @var $myCampagne Comptage_Model_Campagne */
/* @var $myOperation Comptage_Model_Operation */
/* @var $myExtraction Comptage_Model_Extraction */

$contactOperation = Comptage_Model_Contact::get($myOperation->getContactId());

$this->addPlugin('jquery-ui', array('jquery-ui-1.10.1.custom.min.css', 'jquery-ui-1.10.1.custom.min.js'));
$this->addPlugin('colorbox', array('colorbox.css', 'jquery.colorbox-min.js'));
$this->addPlugin('jquery-number', 'jquery.number.min.js');

$this->addCss('comptage.css');
$this->addJs('comptage/extraction/index.js');

/*$this->addReadyEvent(
'$(".commander").click(function(event) {
	event.preventDefault();
	$.post("' . Request::createUri('comptage:extraction:commander') . '", {}, function(data) {
		if (data.ok == true) {
			$("commander").hide();
			alert("Votre demande d\'extraction a bien été enregistrée et un opérateur va la traiter.");
			window.location.href = "' . Request::createUri('comptage:campagne:recap', array('id' => $myCampagne->getId())) . '";
		}
		else {
			alert("Une erreur s\'est produite lors de la demande d\'extraction");
		}
	}, "json");
});');*/
?>
				<div class="block_left">
					<?php include 'comptage/view/index/menu/menu.php';?>
				</div>

				<div class="content_bg">

					<div id="timeline">
						<ul>
							<!-- <li><a href="<?=Request::createUri('comptage:cible:')?>">Comptage</a> &gt;</li> -->
							<li>Comptage &gt;</li>
							<li><a href="<?=Request::createUri('comptage:cible:recap')?>">Cible</a> &gt;</li>
							<li class="selected"><a href="<?=Request::createUri('comptage:extraction:')?>">Extraction</a></li>
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

					<?php if (isset($message)):?>
					<div class="userMsg"><?=$this->c($message)?></div>
					<?php endif;?>

					<div id="messageZone"></div>

					<form id="formExtraction" method="post" action="<?=Request::createUri('comptage:extraction:index')?>">
						<fieldset>
							<legend>Extraction</legend>
							<div class="content" style="width: 500px;">
								<div class="label">Destinataire *</div>
								<div class="value">
									<span id="contact-infos">
									<?php if (isset($contact)):?>
									<?=$contact['nom']?> <?=$contact['prenom']?> (<?=$contact['email']?>)
									<?php endif;?>
									</span>&nbsp;
									<a class="iframe edit" href="<?=Request::createUri('comptage:extraction:listcontacts')?>">Associer</a>
									<input type="hidden" class="required" id="contact" name="contact"<?=(isset($contact) ? ' value="' . $contact['id'] . '"' : '')?> />
								</div>

								<div class="label">Nombre d'adresses à extraire *</div>
								<div class="value"><input type="text" id="quantite" class="required" name="quantite" value="<?=$quantite?>" /></div>

								<div class="label">Commentaire</div>
								<div class="value"><textarea id="commentaire" name="commentaire" cols="45" rows="5"><?=$commentaire?></textarea></div>

								<div class="label">Méthode d'envoi *</div>
								<div class="value">
									<?php foreach ($listEnvoi as $key => $val):?>
									<label for="envoi-<?=$key?>"><?=$val?></label><input type="radio" name="envoi" id="envoi-<?=$key?>" class="required" value="<?=$key?>"<?=($key == $envoi ? ' checked="checked"' : '')?> />
									<?php endforeach;?>
								</div>

								<div class="label">Format de fichier *</div>
								<div class="value">
									<select id="fichiertype" name="fichiertype" class="required" style="margin-right: 15px;">
										<?php foreach ($listFichierType as $key => $val):?>
										<option value="<?=$key?>"<?=($key == $myFichierType ? ' selected="selected"' : '')?>><?=$val?></option>
										<?php endforeach;?>
									</select>
									<div id="fichiertype-options">
										<?php foreach ($listFichierType as $fichierType => $val) {
											echo '<div id="opt-' . $fichierType . '" class="fichiertype-options">';
											$options = $listFichierTypeOptions[$fichierType];
											if (!empty($options)) {
												foreach ($options as $opt => $vals) {
													echo $vals['label'] . '&nbsp;<select name="options[' . $opt . ']">';
													foreach ($vals['values'] as $k => $v) {
														$selected = isset($myFichierOptions[$opt]) && $k == $myFichierOptions[$opt] ? ' selected="selected"' : '';
														echo '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
													}
													echo '</select>';
												}
											}
											echo '</div>';
										}?>
									</div>
								</div>
							</div>
						</fieldset>

						<fieldset>
							<legend>Dessin d'enregistrement</legend>
							<p style="width: 70%">Sélectionnez les colonnes à faire apparaître dans votre fichier en faisant glisser les blocs dans la liste de droite</p>
							<div style=" width: 70%;">
								<div style="display: inline-block; width: 50%;">
									<h2>Disponibles</h2>
									<ul id="champs-dispo" class="connectedSortable mySortable">
										<?php foreach ($listChampsDispo as $id => $label):?>
										<li id="<?=$id?>" class="ui-state-default"><?=$label?></li>
										<?php endforeach;?>
									</ul>
								</div>
								<div style="display: inline-block; width: 49%;">
									<h2>Sélectionnées</h2>
									<ul id="champs-ajoutes" class="connectedSortable mySortable">
										<?php foreach ($listChampsAjoutes as $id => $label):?>
										<li id="<?=$id?>" class="ui-state-default"><?=$label?></li>
										<?php endforeach;?>
									</ul>
								</div>
								<div id="champs-ajoutes-ids" style="display: none;"></div>
							</div>
							<button id="champs-tous">Ajouter tout</button>
						</fieldset>

						<div id="etape_valide">
							<span class="retour" ><a href="<?=Request::createUri('comptage:cible:recap')?>"> &lt; retour</a></span>
							<span class="suivant">
								<?php if ($myOperation->getStatut() == 'STA02' || ($myOperation->getStatut() == 'STA03' && $user->isAdmin())):?>
								<input type="submit" class="btn" value="Commander" />
								<?php endif;?>
							</span>
						</div>

					</form>

				</div>