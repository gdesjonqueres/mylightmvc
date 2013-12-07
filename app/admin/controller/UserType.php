<?php

class Admin_Controller_UserType extends ActionController
{
	protected function _setRights()
	{
		$this->_restrictedLevel = 'gestion_utilisateurs';
	}

	protected function _init()
	{
		if ($this->_request->getAction() == 'add') {
			if (!$this->_user->isAllowedAcces('gestion_societes') &&
				$this->_user->getSociete()->getId() !== $this->getParam('idSoc')
			) {
				throw new Access_Exception('Violation de droit d\'accès à la création de droit UserType');
			}
		}
	}

	public function index()
	{
		$this->societe = $this->_user->getSociete();

		$this->listUserTypesGlobal  = Admin_Model_UserType::getList();
		$this->listUserTypesSociete = $this->societe->getUserTypes();
	}

	public function add()
	{
		$idSociete = (int) $this->getParam('idSoc');
		$idType    = (int) $this->getParam('idType');

		if (!$idSociete || !$idType) {
			throw new InvalidArgumentException('Id société ou Id type invalide');
		}

		try {
			$userType = new Admin_Model_UserType();
			if ($userType->create($idSociete, $idType)) {
				$this->message = 'Le UserType a bien a été créé';
				$this->redirect('admin:userType:edit', array('id' => $userType->getId()));
			}
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
		}
	}

	public function edit()
	{
		$id = (int) $this->getParam('id');
		if (!$id) {
			throw new \InvalidArgumentException('Id asut invalide');
		}

		// Charge le UserType
		try {
			$userType = new Admin_Model_UserType($id);
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
			return;
		}

		// Si en modification
		if ($this->_request->getMethod() == 'POST') {
			try {
				$meth = '_edit' . ucfirst(strtolower($this->getParam('mode', '')));
				if (!method_exists($this, $meth)) {
					throw new \BadMethodCallException('Méthode inconnue ' . __CLASS__ . '::' . $meth);
				}
				// Appelle la bonne méthode pour la sauvegarde des données
				$this->$meth($userType);
			}
			catch (Model_Exception $e) {
				$this->message = $e->getMessage();
				return;
			}
		}

		// Charge les différentes composantes
		try {
			$userType->loadDE();
			$userType->loadCritere();
			$userType->getSociete()->loadCritere();
			$userType->getSociete()->loadDE();

			$this->listDE     = Admin_Model_Acces::getListDE();
			$this->listDroits = Admin_Model_Acces::getListAcces();
			$this->listCrit   = Admin_Model_Acces::getListCritere();

			$this->droitUser = $userType->getDroitsAcces();
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
			return;
		}

		// Affecte le UserType à la vue
		$this->userType = $userType;
	}

	private function _editAcces(Admin_Model_UserType $userType)
	{
		Admin_Model_Acces::updateAcces($userType, $this->getArray('acces'));
		$this->message = 'Les droits d\'accès ont bien été modifiés';
	}

	private function _editCritere(Admin_Model_UserType $userType)
	{
		Admin_Model_Acces::updateCritere($userType, $this->getArray('critere'));

		$tabAchat = $this->getArray('achat');
		foreach ($tabAchat as $idCritere => $value) {
			$userType->getDroits()->saveAchatSurLocation($idCritere, ($value == 1));
		}

		$this->message = 'Les droits d\'accès aux critères ont bien été modifiés';
	}

	private function _editDe(Admin_Model_UserType $userType)
	{
		Admin_Model_Acces::updateDe($userType, $this->getArray('de'));
		$this->message = 'Les droits d\'accès au dessin d\'enregistrement ont bien été modifiés';
	}

}