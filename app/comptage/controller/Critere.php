<?php

class Comptage_Controller_Critere extends ActionController
{
	/**
	 * Campagne en cours
	 * @var Comptage_Model_Campagne
	 */
	protected $_campagne;

	/**
	 * Opération en cours
	 * @var Comptage_Model_Operation
	 */
	protected $_operation;

	protected function _setRights()
	{
		$this->_restrictedLevel = 'comptage';
	}

	protected function _init()
	{
		$campagne = Session::getCampagne();
		if (!$campagne) {
			throw new \RuntimeException('Pas de campagne en cours !');
		}
		$this->_campagne = $campagne;
		$this->_operation = $campagne->getCurrentOperation();
	}

	/**
	 * Supprime un critère sur la cible en cours
	 * Appelé en ajax
	 * @return string|boolean
	 */
	public function remove()
	{
		if (isset($this->_operation)) {
			$cible = $this->_operation->getCurrentCible();
			$idCritere = $this->getParam('critere');
			if ($idCritere && isset($cible[$idCritere])) {
				unset($cible[$idCritere]);
			}
		}

		// Annule le comptage précédent
		$this->_operation->setComptage(NULL);

		if ($this->_request->isAjax()) {
			return toJson(array('ok' => true));
		}

		return false;
	}

	/**
	 * Supprime la valeur d'un critère
	 * Appelé en ajax
	 * @return string|boolean
	 */
	public function removevaleur()
	{
		if (isset($this->_operation)) {
			$cible = $this->_operation->getCurrentCible();
			$idCritere = $this->getParam('critere');
			$idVal = $this->getParam('valeur');
			if ($idCritere && isset($cible[$idCritere])) {
				$cibleCritere = $cible[$idCritere];
				if ($idVal && isset($cibleCritere->tabValeurs[$idVal])) {
					unset($cibleCritere->tabValeurs[$idVal]);
				}
			}
			// Si plus de valeur => supprime le critère
			if (count($cible[$idCritere]->tabValeurs) == 0) {
				unset($cible[$idCritere]);
			}
		}

		// Annule le comptage précédent
		$this->_operation->setComptage(NULL);

		if ($this->_request->isAjax()) {
			return toJson(array('ok' => true));
		}

		return false;
	}

	/**
	 * Liste des valeurs d'un critère
	 */
	public function editvaleurs()
	{
		if (isset($this->_operation)) {
			$cible = $this->_operation->getCurrentCible();
			$idCritere = $this->getParam('critere');
			if ($idCritere && isset($cible[$idCritere])) {
				$this->myValeurs = $cible[$idCritere]->tabValeurs;
				$this->myCritere = Comptage_Model_Critere_Dao::getInstance()->get($idCritere);
			}
		}

		$this->myCampagne = $this->_campagne;
	}

}