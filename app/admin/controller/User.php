<?php

class Admin_Controller_User extends ActionController
{
	protected function _setRights()
	{
		$this->_restrictedLevel = 'gestion_utilisateurs';
	}

	public function index()
	{
		// Récupère la liste des utilisateurs
		$this->listUtilisateur = $this->_user->getListUtilisateurs();
	}

	public function add()
	{
		$this->idSociete = $this->getParam('id_societe');
		$this->civilite  = $this->getParam('civilite');
		$this->prenom    = $this->getParam('prenom');
		$this->nom 		 = $this->getParam('nom');
		$this->adresse1  = $this->getParam('adresse1');
		$this->adresse2  = $this->getParam('adresse2');
		$this->cp 		 = $this->getParam('cp');
		$this->ville 	 = $this->getParam('ville');
		$this->tel 		 = $this->getParam('tel');
		$this->email 	 = $this->getParam('email');
		$this->mdp 		 = $this->getParam('mdp');
		$this->type 	 = $this->getParam('type');

		if ($this->_request->getMethod() == 'POST') {
			if ($this->prenom && $this->nom) {
				try {
					$societe = new Admin_Model_Societe($this->idSociete);

					$user = new Admin_Model_User();
					$user->register($this->idSociete,
									$this->civilite,
									$this->prenom,
									$this->nom,
									$this->adresse1,
									$this->adresse2,
									$this->cp,
									$this->ville,
									$this->tel,
									$this->email,
									$this->mdp,
									$this->type,
									$societe->getIdGroupe());
					$this->message = 'L\'utilisateur ' . $user->getPrenom() . ' ' . $user->getNom() . ' a bien été créé';
				}
				catch (Model_Exception $e) {
					$this->message = $e->getMessage();
				}
			}
		}

		$this->listUserTypes = Admin_Model_UserType::getList();
		$this->listSociete   = $this->_user->getListSociete();
	}

	public function edit()
	{
		$id = (int) $this->getParam('id');
		if (!$id) {
			throw new \InvalidArgumentException('Id utilisateur invalide');
		}

		// Charge l'utilisateur
		try {
			$user = new Admin_Model_User($id);
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
				$this->$meth($user);
			}
			catch (Model_Exception $e) {
				$this->message = $e->getMessage();
				return;
			}
		}

		// Charge les différentes composantes
		try {
			$this->listDroits = Admin_Model_Acces::getListAcces();
			$this->listCrit   = Admin_Model_Acces::getListCritere();
			$this->listDE	  = Admin_Model_Acces::getListDE();

			$user->loadCritere();
			$user->loadDE();
			$user->getSociete()->loadCritere();
			$user->getSociete()->loadDE();

			$this->listSociete		= $this->_user->getListSociete();
			$this->listUserTypes	= Admin_Model_UserType::getList();
			$this->userType 		= $user->getType();

			$this->userType->loadCritere();
			$this->userType->loadDE();
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
			return;
		}

		// Affecte le User à la vue
		$this->userEd = $user;
	}

	private function _edit(Admin_Model_User $user)
	{
		$societe = new Admin_Model_Societe($this->getParam('id_societe'));

		$user->setSociete($this->getParam('id_societe'))
			->setType($this->getParam('type'))
			->setAdresse1($this->getParam('adresse1'))
			->setAdresse2($this->getParam('adresse2'))
			->setCp($this->getParam('cp'))
			->setTel($this->getParam('tel'))
			->setEmail($this->getParam('email'))
			->setCivilite($this->getParam('civilite'))
			->setNom($this->getParam('nom'))
			->setPrenom($this->getParam('prenom'))
			->setVille($this->getParam('ville'))
			->setIdGroupe($societe->getIdGroupe())
			->save();
		$this->message = 'L\'utilisateur a bien été modifié';
	}

	private function _editAcces(Admin_Model_User $user)
	{
		Admin_Model_Acces::updateAcces($user, $this->getArray('acces'));
		$this->message = 'Les droits d\'accès ont bien été modifiés';
	}

	private function _editCritere(Admin_Model_User $user)
	{
		Admin_Model_Acces::updateCritere($user, $this->getArray('critere'));

		$tabAchat = $this->getArray('achat');
		foreach ($tabAchat as $idCritere => $value) {
			$user->getDroits()->saveAchatSurLocation($idCritere, ($value == 1));
		}

		$this->message = 'Les droits d\'accès aux critères ont bien été modifiés';
	}

	private function _editDe(Admin_Model_User $user)
	{
		Admin_Model_Acces::updateDe($user, $this->getArray('de'));
		$this->message = 'Les droits d\'accès au dessin d\'enregistrement ont bien été modifiés';
	}

}