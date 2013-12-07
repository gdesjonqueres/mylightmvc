<?php

class Admin_Controller_Groupe extends ActionController
{
	protected function _setRights()
	{
		$this->_restrictedLevel = 'gestion_societes';
	}

	public function index()
	{
		// Si en création de groupe
		if ($this->_request->getMethod() == 'POST') {
			if (($libelle = $this->getParam('libelle'))) {
				try {
					$groupe = new Admin_Model_Groupe();
					$groupe->register($libelle);
					$this->message = 'Le groupe ' . $groupe->getLibelle() . ' a bien été créé';
				}
				catch (Model_Exception $e) {
					$this->message = $e->getMessage();
				}
			}
			else {
				$this->message = 'Le libellé est obligatoire';
			}
		}

		// Récupère la liste des groupes
		$this->listGroupe = Admin_Model_Groupe::getList();
	}

	public function edit()
	{
		$id = (int) $this->getParam('id');
		if (!$id) {
			throw new \InvalidArgumentException('Id groupe invalide');
		}

		// Charge le groupe
		try {
			$groupe = new Admin_Model_Groupe($id);
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
			return;
		}

		// Si en modification
		if ($this->_request->getMethod() == 'POST') {
			if (($libelle = $this->getParam('libelle'))) {
				$groupe->setLibelle($libelle);
				try {
					$groupe->save();
					$this->message = 'Le groupe a bien été modifié';
				}
				catch (Model_Exception $e) {
					$this->message = $e->getMessage();
				}
			}
			else {
				$this->message = 'Le libellé est obligatoire';
			}
		}

		// Affecte le groupe à la vue
		$this->groupe = $groupe;
	}
}