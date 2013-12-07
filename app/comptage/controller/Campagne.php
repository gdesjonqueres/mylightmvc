<?php

class Comptage_Controller_Campagne extends ActionController
{

	protected function _setRights()
	{
		$this->_restrictedLevel = 'comptage';
	}

	/**
	 * Créé une nouvelle campagne et redirige vers le choix de la cible
	 */
	public function add()
	{
		$campagne = new Comptage_Model_Campagne();
		$campagne->setUserId(Session::getUser()->getId());
		$campagne->setCurrentOperation(new Comptage_Model_Operation());
		Session::setCampagne($campagne);
		$this->redirect('comptage:cible:');
	}

	/**
	 * Sauvegarde la campagne en cours
	 * Appelé en ajax
	 * @throws \RuntimeException Si pas de campagne en cours
	 * @return string|boolean tableau json avec l'index "reload" si rechargement de la page nécessaire
	 */
	public function save()
	{
		$cpg = Session::getCampagne();
		if (!$cpg) {
			throw new \RuntimeException('Pas de campagne en cours !!');
		}

		$libelle = $this->getParam('libelle');
		if ($libelle) {
			$cpg->setLibelle($libelle);
		}

		$reload = false;
		if (!$cpg->getId() || strpos($cpg->getId(), 'tmp') !== false) {
			$reload = true;
		}

		if (Comptage_Model_Campagne_Dao::getInstance()->saveFullCampagne($cpg)) {
			if ($this->_request->isAjax()) {
				return toJson(array('ok' => true, 'reload' => $reload));
			}
			else {
				return true;
			}
		}

		return false;
	}

	/**
	 * Charge une campange et rédirige vers l'édition de la cible ou le recap selon l'état de l'opération
	 * @throws InvalidArgumentException Si pas d'id campagne
	 */
	public function load()
	{
		$id = $this->getParam('id');
		if (!$id) {
			throw new InvalidArgumentException('Pas d\'id fourni');
		}

		$cpg = Comptage_Model_Campagne_Dao::getInstance()->getFullCampagne($id);
		Session::setCampagne($cpg);
		if ($cpg->getCurrentOperation()->getStatut() == 'STA02' || $cpg->getCurrentOperation()->getStatut() == 'STA03') {
			$this->redirect('comptage:extraction:');
		}
		else if ($cpg->getCurrentOperation()->getStatut() == 'STA01') {
			$this->redirect('comptage:cible:edit');
		}
		else {
			$this->redirect('comptage:cible:recap');
		}
	}

	/**
	 * Enregistre le nom de la campagne
	 * Appelé en ajax
	 * @throws \RuntimeException Si pas de campagne en cours
	 * @return string|boolean tableau json avec l'index "reload" si rechargement de la page nécessaire
	 */
	public function saveLibelle()
	{
		$cpg = Session::getCampagne();
		if (!$cpg) {
			throw new \RuntimeException('Pas de campagne en cours !!');
		}

		$libelle = $this->getParam('libelle');
		if ($libelle) {
			$cpg->setLibelle($libelle);
		}
		if ($cpg->getId() && strpos($cpg->getId(), 'tmp') === false) {
			Comptage_Model_Campagne_Dao::getInstance()->save($cpg);
			if ($this->_request->isAjax()) {
				return toJson(array('ok' => true));
			}
		}
		else {
			Comptage_Model_Campagne_Dao::getInstance()->saveFullCampagne($cpg);
			if ($this->_request->isAjax()) {
				return toJson(array('ok' => true, 'reload' => true));
			}
		}

		return true;
	}

	/**
	 * Affiche la liste des campagnes de l'utilisateur
	 */
	public function listcampagnes()
	{
		$this->listCpg = Comptage_Model_Campagne_Dao::getInstance()->getList(array('user' => Session::getUser()->getId()));
	}

	/**
	 * Affiche le récap de la campagne
	 * @throws \InvalidArgumentException Si pas d'id campagne fourni
	 */
	public function recap()
	{
		$id = $this->getParam('id');
		if (empty($id)) {
			throw new \InvalidArgumentException('Id campagne non fourni');
		}
		$this->lightbox = $this->getParam('lightbox');

		$cpg = Comptage_Model_Campagne_Dao::getInstance()->getFullCampagne($id);
		$op = $cpg->getCurrentOperation();
		$ext = $op->getExtraction();

		$this->myCampagne = $cpg;
		$this->myOperation = $op;
		$this->myCible = $op->getCurrentCible();
		$this->myUser = new Admin_Model_User($cpg->getUserId());

		if ($op->getContactId()) {
			$this->myContactAssocie = Comptage_Model_Contact::get($op->getContactId());
		}
		if ($ext) {
			$this->myExtraction = $ext;
			if ($ext->getContactId()) {
				$this->myContactDestinataire = Comptage_Model_Contact::get($ext->getContactId());
			}
		}

		if ($msg = $this->getFlashMessage()) {
			$this->message = $msg;
		}

		$this->fichierListeType = Comptage_Model_Extraction_Fichier::getListType();
		$this->fichierListeOptions = Comptage_Model_Extraction_Fichier::getListOptions();
	}
}