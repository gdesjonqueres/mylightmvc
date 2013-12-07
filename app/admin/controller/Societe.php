<?php

class Admin_Controller_Societe extends ActionController
{
	protected function _setRights()
	{
		$this->_restrictedLevel = 'gestion_societes';
	}

	public function index()
	{
		// Si en création de société
		if ($this->_request->getMethod() == 'POST') {
			if ($this->getParam('rs') && $this->getParam('id_contact')) {
				try {
					$societe = new Admin_Model_Societe();
					$societe->register($this->getParam('rs'),
										$this->getParam('adresse1'),
										$this->getParam('adresse2'),
										$this->getParam('cp'),
										$this->getParam('ville'),
										$this->getParam('tel'),
										$this->getParam('id_contact'),
										$this->getParam('id_groupe'));
					$this->message = 'La société ' . $societe->getRaisonSociale() . ' a bien été créée';
				}
				catch (Model_Exception $e) {
					$this->message = $e->getMessage();
				}
			}
		}

		// Charge les différentes composantes
		try {
			// Récupère la liste des sociétés
			$this->listSociete = $this->_user->getListSociete();

			// Récupère la liste des groupes
			$this->listGroupe = Admin_Model_Groupe::getList();
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
			return;
		}
	}

	public function edit()
	{
		$id = (int) $this->getParam('id');
		if (!$id) {
			throw new \InvalidArgumentException('Id société invalide');
		}

		// Charge la société
		try {
			$societe = new Admin_Model_Societe($id);
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
					throw new \BadMethodCallException("Méthode inconnue " . __CLASS__ . "::$meth");
				}
				// Appelle la bonne méthode pour la sauvegarde des données
				$this->$meth($societe);
			}
			catch (Model_Exception $e) {
				$this->message = $e->getMessage();
				return;
			}
		}

		// Charge les différentes composantes
		try {
			$societe->loadAcces();
			$societe->loadCritere();
			$societe->loadDE();

			$this->listDE 	    = Admin_Model_Acces::getListDE();
			$this->listDroits   = Admin_Model_Acces::getListAcces();
			$this->listCrit     = Admin_Model_Acces::getListCritere();

			$this->droitSociete = $societe->getDroitsAcces();
			$this->utilisateurs = $societe->getUsers();

			$this->listUserTypesGlobal  = Admin_Model_UserType::getList();
			$this->listUserTypesSociete = $societe->getUserTypes();

			$this->listGroupe = Admin_Model_Groupe::getList();
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
			return;
		}

		// Affecte la Société à la vue
		$this->societe = $societe;
	}

	private function _edit(Admin_Model_Societe $societe)
	{
		$societe->setRS($this->getParam('rs'))
				->setContact($this->getParam('id_contact'))
				->setAdresse1($this->getParam('adresse1'))
				->setAdresse2($this->getParam('adresse2'))
				->setCp($this->getParam('cp'))
				->setVille($this->getParam('ville'))
				->setTel($this->getParam('tel'))
				->setIdGroupe($this->getParam('id_groupe'))
				->save();
		$this->message = 'La société a bien été modifiée';
	}

	private function _editAcces(Admin_Model_Societe $societe)
	{
		Admin_Model_Acces::updateAcces($societe, $this->getArray('acces'));
		$this->message = 'Les droits d\'accès ont bien été modifiés';
	}

	private function _editCritere(Admin_Model_Societe $societe)
	{
		Admin_Model_Acces::updateCritere($societe, $this->getArray('critere'));

		$tabAchat = $this->getArray('achat');
		foreach ($tabAchat as $idCritere => $value) {
			$societe->getDroits()->saveAchatSurLocation($idCritere, ($value == 1));
		}

		$this->message = 'Les droits d\'accès aux critères ont bien été modifiés';
	}

	private function _editDe(Admin_Model_Societe $societe)
	{
		Admin_Model_Acces::updateDe($societe, $this->getArray('de'));
		$this->message = 'Les droits d\'accès au dessin d\'enregistrement ont bien été modifiés';
	}
}