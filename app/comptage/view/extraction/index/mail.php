<?php
/* @var $extraction Comptage_Model_Extraction */
/* @var $campagne Comptage_Model_Campagne */
/* @var $operation Comptage_Model_Operation */
/* @var $user Admin_Model_User */

$client = Comptage_Model_Contact::get($operation->getContactId());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
	body {
		font-size: 14px;
	}
</style>
</head>
<body>
	<p>
		Bonjour, <br /><br />
		L'utilisateur <em><?=$this->c($user->getPrenom())?> <?=$this->c($user->getNom())?></em> a pass&eacute; commande de
		<strong><?=$this->formatNumber($extraction->getQuantite())?></strong> adresses pour le
		client <em><?=$this->c($client['prenom'])?> <?=$this->c($client['nom'])?> (<?=$this->c($client['societe'])?>)</em>
	</p>

	<p>
		D&eacute;tail de la commande: <br />
		Id: <em><?=$campagne->getId()?></em> <br />
		Nom: <em><?=$campagne->getLibelle()?></em>
	</p>

	<p>
		Merci de traiter la demande dans le Requeteur, <br />
		<a href="<?=$this->c(Request::createUrl('admin:campagne:listcampagnes',
												array('flag' => 'enattente', 'redirect' => 'true')))?>" title="campagnes en attente">
			Campagnes en attente dans le Requeteur</a>
	</p>
</body>
</html>