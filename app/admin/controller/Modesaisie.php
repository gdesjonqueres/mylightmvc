<?php

class Admin_Controller_Modesaisie extends ActionController
{
	/**
	 * Dao
	 * @var Comptage_Model_ModeSaisie_Dao
	 */
	private $_dao;

	protected function _setRights()
	{
		$this->_restrictedLevel = 'gestion_criteres';
	}

	protected function _init()
	{
		$this->_dao = Comptage_Model_ModeSaisie_Dao::getInstance();
	}

	public function index()
	{
		// Si en création
		if ($this->_request->getMethod() == 'POST') {
			try {
				$args = $this->getParam('args', array());
				if (!empty($args)) {
					$args = array_map('trim', explode(',', $args));
				}

				$mode = new Comptage_Model_ModeSaisie();
				$mode->setCode($this->getParam('code'))
					->setLibelle($this->getParam('libelle'))
					->setDescription($this->getParam('description'))
					->setListArgs($args);
				$this->_dao->save($mode);
				$this->message = 'Le mode de saisie ' . $mode->getCode() . ' a bien été créé';
			}
			catch (Model_Exception $e) {
				$this->message = $e->getMessage();
			}
		}

		// Charge les différentes composantes
		try {
			// Récupère la liste des modes de saisie
			$this->listMode = $this->_dao->getList();
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
		}
	}

	public function edit()
	{
		$id = (int) $this->getParam('id');
		if (!$id) {
			throw new \InvalidArgumentException('Id mode de saisie invalide');
		}

		// Charge le mode de saisie
		try {
			$mode = $this->_dao->get($id);
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
		}

		// Si en modification
		if ($this->_request->getMethod() == 'POST') {
			try {
				$args = $this->getParam('args', array());
				if (!empty($args)) {
					$args = array_map('trim', explode(',', $args));
				}

				$mode->setCode($this->getParam('code'))
					->setLibelle($this->getParam('libelle'))
					->setDescription($this->getParam('description'))
					->setListArgs($args);
				$this->_dao->save($mode);
				$this->message = 'Le mode de saisie ' . $mode->getCode() . ' a bien été modifié';
			}
			catch (Model_Exception $e) {
				$this->message = $e->getMessage();
			}
		}

		// Affecte le mode à la vue
		$this->mode = $mode;
	}

}