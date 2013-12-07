<?php

class Comptage_Controller_Cible extends ActionController
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

	/**
	 * Liste de critères ajoutés automatiquement à chaque nouvelle campagne
	 * @var array
	 */
	static protected $_tabCriteresdefaults = array(
		'part' => array(
			'premium' => array(
				'defaultValue' => 10,
				'defaultLabel' => 'Bonne à très bonne'
			),
			'pacitel' => array(
				'defaultValue' => 0,
				'defaultLabel' => 'Exclure Pacitel'
			),
			'orange' => array(
				'defaultValue' => 0,
				'defaultLabel' => 'Exclure liste orange'
			),
		),
	);

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

	public function index()
	{
		if ($this->_operation->getStatut() == 'STA01') {
			$this->redirect('comptage:cible:type');
		}
		else {
			$this->redirect('comptage:cible:recap');
		}
	}

	/**
	 * Formulaire choix du type de cible
	 */
	public function type()
	{
		// Choix du type de cible
		$cibletype = $this->_operation->getCibleType();

		if ($this->_request->getMethod() == 'POST') {
			$newCibleType = $this->getParam('cibletype');

			if (!empty($newCibleType)) {
				// Efface toutes les cibles si un type de cible a déjà été défini
				// et qu'il est différent du type sélectionné
				if ($cibletype && $newCibleType != $cibletype) {
					$this->_operation->unsetCibles();
				}

				$this->_operation->setCibleType($newCibleType);

				if (count($this->_operation) == 0) {
					$this->redirect('comptage:cible:add');
				}
				else {
					$this->redirect('comptage:cible:edit');
				}
			}
		}

		// Passage des données à la vue
		$this->listeCibleType = Comptage_Model_Cible_Dao::getInstance()->getListType();
		$this->cibletype = $cibletype;
		$this->myCampagne = $this->_campagne;
		$this->myOperation = $this->_operation;
		if (count($this->_operation) >= 1) {
			$this->myCible = $this->_operation->getCurrentCible();
		}
	}

	/**
	 * Créé une nouvelle cible et redirige vers l'édition de cible
	 */
	public function add()
	{
		if (!$this->_operation->getCibleType()) {
			$this->redirect('comptage:cible:type');
		}
		else {
			$cible = new Comptage_Model_Cible();
			$cible->setCibleType($this->_operation->getCibleType())
					->setLibelle('Cible n°' . (count($this->_operation) + 1));
			$this->_operation->setCurrentCible($cible);

			// Ajout des critères par défaut
			if (!empty(self::$_tabCriteresdefaults[$this->_operation->getCibleType()])) {
				foreach (self::$_tabCriteresdefaults[$this->_operation->getCibleType()] as $critId => $opts) {
					$crit = Comptage_Model_Critere_Dao::getInstance()->get($critId);
					$cibleCritere = new Comptage_Model_Cible_Critere();
					$cibleCritere->idCritere = $crit->getId();
					$cibleCritere->typeValeur = $crit->getTypeValeur();
					$cibleCritere->tabValeurs = array($opts['defaultValue'] => $opts['defaultLabel']);
					$cible[$crit->getId()] = $cibleCritere;
				}
			}

			$this->redirect('comptage:cible:edit');
		}
	}

	/**
	 * Interface d'édition d'une cible
	 * @throws RuntimeException Si la cible donné n'existe pas
	 */
	public function edit()
	{
		// Change la cible en cours
		$idCible = $this->getParam('cible');
		if (!empty($idCible) && $idCible !== $this->_operation->getCurrentCible()->getId()) {
			if (!isset($this->_operation[$idCible])) {
				throw new RuntimeException('La cible ' . $idCible . 'n\'existe pas');
			}
			$this->_operation->setCurrentCible($this->_operation[$idCible]);
		}

		// Passage des données à la vue
		$this->myCampagne = $this->_campagne;
		$this->myOperation = $this->_operation;
		$this->myCible = $this->_operation->getCurrentCible();

		$this->listeCriteres = Comptage_Model_Critere_Dao::getInstance()
								->setRegistryEnabled(true)
								->getByCibleType($this->_operation->getCurrentCible()->getCibleType(),
												array('order' => 'type'));
		//Comptage_Model_Critere_Dao::getInstance()->isRegistryEnabled(false);
	}

	public function recap()
	{
		// Passage des données à la vue
		$this->myCampagne = $this->_campagne;
		$this->myOperation = $this->_operation;
		$this->myCible = $this->_operation->getCurrentCible();
	}

	/**
	 * Supprime une cible
	 * @throws RuntimeException Si la cible donnée n'existe pas
	 */
	public function remove()
	{
		$idCible = $this->getParam('cible');
		if (!isset($this->_operation[$idCible])) {
			throw new RuntimeException('La cible ' . $idCible . 'n\'existe pas');
		}
		unset($this->_operation[$idCible]);
		if (count($this->_operation)) {
			$this->redirect('comptage:cible:edit');
		}
		$this->redirect('comptage:cible:');
	}

	/**
	 * Affiche les détails de la cible en cours (liste des critères avec valeurs)
	 * Appelé en ajax
	 * @return string chaîne html
	 */
	public function refreshcurrentcible()
	{
		$view = new View();
		return $view->render($this->getViewPath() . 'edit/cible.php',
							array('user'  => Session::getUser(),
									'cible' => $this->_operation->getCurrentCible(),
									'myCible' => $this->_operation->getCurrentCible()));
	}

}