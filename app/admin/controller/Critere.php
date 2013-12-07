<?php

class Admin_Controller_Critere extends ActionController
{
	/**
	 * Dao
	 * @var Comptage_Model_Critere_Dao
	 */
	private $_dao;

	protected function _setRights()
	{
		$this->_restrictedLevel = 'gestion_criteres';
	}

	protected function _init()
	{
		$this->_dao = Comptage_Model_Critere_Dao::getInstance();
	}

	public function index()
	{
		// Si en création
		if ($this->_request->getMethod() == 'POST') {
			try {
				$critere = new Comptage_Model_Critere();
				$critere->setCode($this->getParam('code'))
						->setLibelle($this->getParam('libelle'))
						->setLibelleCourt($this->getParam('libellecourt'))
						->setType($this->getParam('type'))
						->setTypeValeur($this->getParam('typevaleur'))
						->setScoring($this->getParam('scoring', 0) == 1 ? true : false)
						->setLocation($this->getParam('location', 0) == 1 ? true : false)
						->setDesactive($this->getParam('desactive', 0) == 1 ? true : false)
						->setListCibleType($this->getArray('cibletype', array()));
				$this->_dao->save($critere);
				$this->message = 'Le critère ' . $critere->getCode() . ' a bien été créé';
			}
			catch (Model_Exception $e) {
				$this->message = $e->getMessage();
			}
		}

		// Charge les différentes composantes
		try {
			$this->listCritere = $this->_dao->getList();
			$this->listType = $this->_dao->getListType();
			$this->listTypeValeur = $this->_dao->getListTypeValeur();
			$this->listCibleType = Comptage_Model_Cible_Dao::getInstance()->getListType();
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
		}
	}

	public function edit()
	{
		$id = (int) $this->getParam('id');
		if (!$id) {
			throw new \InvalidArgumentException('Id critère invalide');
		}

		// Charge le critère
		try {
			$critere = $this->_dao->get($id);
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
		}

		// Si en modification
		if ($this->_request->getMethod() == 'POST') {
			try {
				$mode = $this->getParam('mode');
				if ($mode == 'modesaisie') {
					$this->_editModeSaisie($critere);
				}
				else {
					$this->_edit($critere);
				}
			}
			catch (Model_Exception $e) {
				$this->message = $e->getMessage();
			}
		}

		// Charge les différentes composantes
		try {
			$this->listType = $this->_dao->getListType();
			$this->listTypeValeur = $this->_dao->getListTypeValeur();
			$this->listTypeDonnee = $this->_dao->getListTypeDonnee();
			$this->listCibleType = Comptage_Model_Cible_Dao::getInstance()->getListType();
			$this->listArgType = Comptage_Model_ModeSaisie_Dao::getInstance()->getListArgType();
			$this->listMode = Comptage_Model_ModeSaisie_Dao::getInstance()->getList();
		}
		catch (Model_Exception $e) {
			$this->message = $e->getMessage();
		}

		// Affecte le critère à la vue
		$this->critere = $critere;
		$codeSql = $critere->getCodeSql();
		if (is_array($codeSql)) {
			$codeSql = json_encode($codeSql);
		}
		$this->codeSql = $codeSql;
	}

	private function _edit(Comptage_Model_Critere $critere)
	{
		$codeSql = $this->_request->getParam('codesql', '', false);
		if (isJson($codeSql)) {
			$codeSql = json_decode($codeSql, true);
		}

		$critere->setCode($this->getParam('code'))
				->setLibelle($this->getParam('libelle'))
				->setLibelleCourt($this->getParam('libellecourt'))
				->setType($this->getParam('type'))
				->setTypeValeur($this->getParam('typevaleur'))
				->setTypeDonnee($this->getParam('typedonnee'))
				->setOrdre($this->getParam('ordre'))
				->setScoring($this->getParam('scoring', 0) == 1 ? true : false)
				->setLocation($this->getParam('location', 0) == 1 ? true : false)
				->setDesactive($this->getParam('desactive', 0) == 1 ? true : false)
				->setListCibleType($this->getArray('cibletype', array()))
				->setChampSql($this->getParam('champsql'))
				->setCodeSql($codeSql);
		$this->_dao->save($critere);
		$this->message = 'Le critère ' . $critere->getCode() . ' a bien été modifié';
	}

	private function _editModeSaisie(Comptage_Model_Critere $critere)
	{
		$args = $this->getArray('args');
		$idMode = $this->getParam('modesaisie');
		if (!empty($idMode)) {
			$modeSaisie = Comptage_Model_ModeSaisie_Dao::getInstance()->get($idMode);

			$myArgs = array();
			if (!empty($args)) {
				foreach ($args as $id => $arg) {
					if (isset($arg['valeur']) && !empty($arg['valeur']) &&
						isset($arg['type']) && !empty($arg['type'])
					) {
						$myArgs[$id] = array('valeur' => $arg['valeur'],
											'type' => $arg['type']);
					}
				}
			}
			$this->_dao->saveModeSaisieArgs($critere, $modeSaisie, $myArgs);
			$critere->setModeSaisieArgs($modeSaisie->getId(), $myArgs);
			$this->message = 'Les arguments du mode de saisie ont bien été modifiés';
		}
	}

}