<?php

class Comptage_Controller_Modesaisie extends ActionController
{
	/**
	 * Regex pour séparer les codes à importer
	 * @var string
	 */
	const REGEX_SPLIT_IMPORT_VALUES = "/[,;\n]/";

	/**
	 * Cible en cours
	 * @var Comptage_Model_Cible
	 */
	protected $_cible;

	/**
	 * Critere
	 * @var Comptage_Model_Critere
	 */
	protected $_critere;

	/**
	 * CibleCritere
	 * @var Comptage_Model_Cible_Critere
	 */
	protected $_cibleCritere;

	/**
	 * ModeSaisie
	 * @var Comptage_Model_ModeSaisie
	 */
	protected $_modeSaisie;

	/**
	 * Arguments du critère à passer au mode de saisie
	 * @var array
	 */
	protected $_modeSaisieArgs;

	protected function _setRights()
	{
		$this->_restrictedLevel = 'comptage';
	}

	protected function _init()
	{
		// Récupère la cible en cours
		$campagne = Session::getCampagne();
		if (!$campagne) {
			throw new \RuntimeException('Campagne non définie');
		}
		$this->_cible = $campagne->getCurrentOperation()->getCurrentCible();

		// Récupère le critère
		$idCritere = $this->getParam('critere');
		if (!$idCritere) {
			throw new \InvalidArgumentException('Id critère invalide: ' . $idCritere);
		}
		$this->_critere = Comptage_Model_Critere_Dao::getInstance()->get($idCritere);

		// Récupère le CibleCritere
		if (isset($this->_cible[$idCritere])) {
			$this->_cibleCritere = $this->_cible[$idCritere];
		}
		else {
			$this->_cibleCritere = new Comptage_Model_Cible_Critere();
			$this->_cibleCritere->idCritere = $this->_critere->getId();
			$this->_cibleCritere->typeValeur = $this->_critere->getTypeValeur();
		}

		// Récupère le mode de saisie
		$modeSaisie = $this->_request->getAction();
		if (($pos = strpos($modeSaisie, 'Json')) !== false) {
			$modeSaisie = substr($modeSaisie, 0, $pos);
		}
		if (empty($modeSaisie)) {
			throw new \RuntimeException('Aucun mode de saisie défini');
		}
		$this->_modeSaisie = Comptage_Model_ModeSaisie_Dao::getInstance()->getById($modeSaisie);

		// Récupère les arguments liés au couple {critère, mode de saisie}
		$args = (array) $this->_critere->getModeSaisieArgs($modeSaisie);
		if (empty($args)) {
			throw new \RuntimeException('Le mode de saisie ' . $modeSaisie .
										' n\'est pas associé au critère ' . $idCritere);
		}
		$this->_modeSaisieArgs = $args;
	}

	/**
	 * Exécute une fonction de callback définie en argument sur le critère
	 * @param string $callback nom de la fonction
	 * @param array $args tableau d'arguments supplémentaires à passer à la callback (ex: search)
	 * @throws \RuntimeException si la callback n'est pas exécutable
	 * @return mixed valeur de retour du callback
	 */
	private function _runCallback($callback, array $args = array())
	{
		// si callback sérialisée en json avec les paramètres
		if (isJson($callback)) {
			list($callback, $myArgs) = json_decode($callback, true);
			$args = array_merge($myArgs, $args);
		}

		// vérifie qu'on peut exécuter la fonction de callback
		if (is_callable($callback)) {
			// passe automatiquement le code critere à la callback en 1er argument
			array_unshift($args, $this->_critere->getCode());
			return call_user_func_array($callback, $args);
		}
		throw new \RuntimeException('La fonction de callback n\'est pas exécutable: ' . $callback . ' pour ' .
									$this->_critere->getCode() . ' sur ' . $this->_modeSaisie->getCode());
	}

	/**
	 * Passe les arguments du mode de saisie à la vue
	 */
	private function _initView()
	{
		// passe chaque argument du critère pour la vue
		foreach ($this->_modeSaisieArgs as $k => $arg) {

			// ne traite pas les filtres (se fait au moment du traitement des données)
			if ($arg['nom'] == 'filter') {
				continue;
			}

			// interprète la valeur si nécessaire
			$val = $arg['valeur'];
			if (isset($arg['type']) && !empty($arg['type'])) {
				// si liste de valeurs sérialisée
				if ($arg['type'] == 'serialized') {
					$val = unserialize($val);
				}
				// si liste de valeurs sérialisée en json
				elseif ($arg['type'] == 'json') {
					$val = json_decode($val, true);
				}
				// si fonction de callback
				elseif ($arg['type'] == 'callable') {
					$val = $this->_runCallback($arg['valeur']);
				}
				elseif ($arg['type'] == 'sql') {
					throw new RuntimeException('Type SQL non géré pour le moment');
				}
			}
			// ajoute l'argument à la réponse
			$this->_response->addVar($arg['nom'], $val);
		}

		$this->idCritere = $this->_critere->getId();
		$this->myCritere = $this->_critere;
		$this->myMode = $this->_modeSaisie;
	}

	/**
	 * Traite les valeurs soumises par l'utilisateur
	 * @param array $values la liste des valeurs
	 * @return array la liste des valeurs possiblement filtrée
	 */
	private function _processValues($values)
	{
		$idCritere = $this->_critere->getId();

		if (!empty($values)) {

			// Filtre les valeurs si nécessaire
			if (isset($this->_modeSaisieArgs['filter']) && $this->_modeSaisieArgs['filter']['type'] == 'callable') {
				$filter = $this->_modeSaisieArgs['filter']['valeur'];
				$values = $this->_runCallback($filter, array($values));
			}

			// Si autocomplete ou import, ajoute les valeurs à celles déjà existantes
			if (in_array($this->_modeSaisie->getCode(), array('autocomplete', 'import'))) {
				$this->_cibleCritere->tabValeurs += $values;
			}
			// Met à jour les valeurs du critère (écrase les anciennes valeurs)
			else {
				$this->_cibleCritere->tabValeurs = $values;
			}

			// Ajoute le critère au requeteur
			if (!isset($this->_cible[$idCritere])) {
				$this->_cible[$idCritere] = $this->_cibleCritere;
			}
		}
		// Supprime le critère du requeteur si pas de valeur
		else if (isset($this->_cible[$idCritere])) {
			unset($this->_cible[$idCritere]);
		}

		// Annule le comptage précédent
		Session::getCampagne()->getCurrentOperation()->setComptage(NULL);

		return $values;
	}

	public function checkbox()
	{
		$valeurs = (array) $this->_cibleCritere->tabValeurs;

		if ($this->_request->getMethod() == 'POST') {
			$valeurs = $this->getArray('valeurs');
			$this->_processValues($valeurs);
			if ($this->_request->isAjax()) {
				return toJson(array('ok' => true));
			}
		}

		// Passage des valeurs à la vue
		$this->_initView();
		$this->valeurs = array_keys($valeurs);
	}

	public function radio()
	{
		$valeurs = (array) $this->_cibleCritere->tabValeurs;

		if ($this->_request->getMethod() == 'POST') {
			$valeurs = $this->getArray('valeurs');
			$this->_processValues($valeurs);
			if ($this->_request->isAjax()) {
				return toJson(array('ok' => true));
			}
		}

		// Passage des valeurs à la vue
		$this->_initView();
		$this->valeurs = array_keys($valeurs);
	}

	public function intervalle()
	{
		$valeurs = (array) $this->_cibleCritere->tabValeurs;

		if ($this->_request->getMethod() == 'POST') {
			$valeurs = $this->getArray('valeurs');
			if ($this->_request->isAjax()) {
				$this->_processValues($valeurs);
				return toJson(array('ok' => true));
			}

		}

		// Passage des valeurs à la vue
		$this->_initView();

		// Initialise le min
		if (!empty($valeurs) && isset($valeurs['min'])) {
			$this->min = key($valeurs['min']);
		}
		else {
			$this->min = '';
		}

		// Initialise le max
		if (!empty($valeurs) && isset($valeurs['max'])) {
			$this->max = key($valeurs['max']);
		}
		else {
			$this->max = '';
		}
	}

	public function tree()
	{
		$valeurs = (array) $this->_cibleCritere->tabValeurs;

		if ($this->_request->getMethod() == 'POST') {
			$valeurs = $this->getArray('valeurs');
			$this->_processValues($valeurs);
			if ($this->_request->isAjax()) {
				return toJson(array('ok' => true));
			}
		}

		// Passage des valeurs à la vue
		$this->_initView();
		$this->valeurs = array_keys($valeurs);
	}

	public function autocomplete()
	{
		$valeurs = (array) $this->_cibleCritere->tabValeurs;

		if ($this->_request->getMethod() == 'POST') {
			$valeurs = $this->getArray('valeurs');
			$this->_processValues($valeurs);
			if ($this->_request->isAjax()) {
				return toJson(array('ok' => true));
			}
		}

		// Passage des valeurs à la vue
		$this->_initView();
	}

	/**
	 * Effectue une recherche et retourne la liste des valeurs encodées en json
	 * Appelé en ajax
	 * @return string
	 */
	public function autocompleteJson()
	{
		$values = array();
		$lookup = $this->_request->getParam('lookup');
		if (!empty($lookup)) {
			if (isset($this->_modeSaisieArgs['search'])) {
				$search = $this->_modeSaisieArgs['search']['valeur'];
				$values = $this->_runCallback($search, array($lookup));
			}
		}
		return toJson($values);
	}

	public function import()
	{
		$valeurs = (array) $this->_cibleCritere->tabValeurs;

		if ($this->_request->getMethod() == 'POST') {
			$valeurs = $this->getParam('valeurs');
			$valeurs = array_map('trim', preg_split(self::REGEX_SPLIT_IMPORT_VALUES, $valeurs));
			$filtered = $this->_processValues($valeurs);
			if (!empty($valeurs)) {
				$this->message = '<p>Nombre de codes importés : <span class="data">' . count($filtered) . '</span> </p>
								<p>Nombre de codes dans votre sélection : <span class="data">' . count($valeurs) . '</span> </p>';
			}
			$valeurs = $filtered;
		}

		// Passage des valeurs à la vue
		$this->_initView();
		$this->valeurs = implode(', ', array_keys($valeurs));
	}
}
