<?php
/**
 * Classe pour gérer l'entité CRITERE en BDD
 * Motifs de conception utilisés: DAO, SINGLETON, REGISTRY
 *
 * @package Requeteur\Model\Critere
 */
class Comptage_Model_Critere_Dao
{
	/**
	 * Liste des types de valeur (devrait être stocké en base)
	 * @var array
	 */
	private static $_tabTypeValeur = array('unitaire' => 'Unitaire',
											'intervalle' => 'Intervalle',
											'liste' => 'Liste',
											'switch' => 'Switch');

	/**
	 * Liste des types de données (devrait être stocké en base)
	 * @var array
	 */
	private static $_tabTypeDonnee = array('string' => 'Chaîne',
											'numeric' => 'Numérique',);

	/**
	 * Singleton
	 * @var Comptage_Model_Critere_Dao
	 */
	private static $_instance;

	/**
	 * Db
	 * @var Db
	 */
	private $_db;

	/**
	 * Registre des instances de CRITERE
	 * @var array tableau de Comptage_Model_Critere
	 */
	private $_tabInstances;

	/**
	 * TRUE si le registre est activé, FALSE sinon
	 * @var bool
	 */
	private $_isRegistryEnabled;


	private function __construct()
	{
		$this->_db = Db::getInstance('Requeteur');
		$this->_tabInstances = new ArrayObject();
		$this->_isRegistryEnabled = false;
	}

	/**
	 * @return Comptage_Model_Critere_Dao
	 */
	public static function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function setRegistryEnabled($enabled)
	{
		$this->_isRegistryEnabled = (bool) $enabled;
		return $this;
	}

	public function isRegistryEnabled()
	{
		return $this->_isRegistryEnabled;
	}

	/**
	 * Retourne le critère avec l'id demandé
	 * @param int $id id du critère
	 * @return Comptage_Model_Critere
	 */
	public function get($id)
	{
		return $this->_getBy('id', $id);
	}

	/**
	 * Retourne la liste de tous les critères
	 * @return array liste de Comptage_Model_Critere
	 */
	public function getList()
	{
		return $this->_getBy('all');
	}

	/**
	 * Retourne la liste des type de critères (géo, part, pro, ...)
	 */
	public function getListType()
	{
		$list = array();

		$sql = 'SELECT id_criteretype, code, libelle from criteretype ORDER BY libelle';
		$res = $this->_db->query($sql);
		while ($row = $this->_db->fetchRow($res)) {
			$list[$row['id_criteretype']] = $row['libelle'];
		}

		return $list;
	}

	/**
	 * Retourne la liste des type de valeurs (unitaire, liste, ...)
	 */
	public function getListTypeValeur()
	{
		return self::$_tabTypeValeur;
	}

	/**
	 * Retourne la liste des type de données (chaîne, numérique)
	 */
	public function getListTypeDonnee()
	{
		return self::$_tabTypeDonnee;
	}

	/**
	 * Effectue la sauvegarde en base (création ou mise à jour)
	 * @param Comptage_Model_Critere $critere
	 */
	public function save(Comptage_Model_Critere $critere)
	{
		if ($critere->valid()) {
			if ($critere->getId()) {
				return $this->_update($critere);
			}
			else {
				return $this->_create($critere);
			}
		}
	}

	public function saveModeSaisieArgs(Comptage_Model_Critere $critere, Comptage_Model_ModeSaisie $mode, array $args)
	{
		if (!$critere->getId() || !$mode->getId()) {
			throw new \RuntimeException('L\'id mode saisie ou l\'id critère n\'est pas renseigné');
		}

		$old = array_keys($critere->getModeSaisieArgs($mode->getId()));
		$new = array_keys($args);

		$tabInsert = array_diff($new, $old);
		$tabDelete = array_diff($old, $new);
		$tabUpdate = array_intersect($old, $new);

		if (!empty($tabDelete)) {
			// TODO: A décommenter lors du passage en PHP5.4 (ne marche pas en PHP5.3, $this ne peut pas être utilisé avec une closure)
			//$callback = function($val) use ($this) {
			//	return $this->_db->escape_string_add_quotes($val);
			//};
			//$in = implode(', ', array_map($callback, $tabDelete));
			foreach ($tabDelete as $i => $e) {
				$tabDelete[$i] = $this->_db->escape_string_add_quotes($e);
			}
			$in = implode(', ', $tabDelete);
			$sql =
'DELETE FROM critere_modesaisie
WHERE id_critere = ' . (int) $critere->getId() . '
	AND id_modesaisie = ' . (int) $mode->getId() . '
	AND nom IN (' . $in . ')';
			$res = $this->_db->query($sql);
		}
		if (!empty($tabInsert)) {
			foreach ($tabInsert as $idArg) {
				$sql =
'INSERT INTO critere_modesaisie (id_critere, id_modesaisie, nom, valeur, type)
VALUES (' .
	(int) $critere->getId() . ', ' .
	(int) $mode->getId() . ', ' .
	$this->_db->escape_string_add_quotes($idArg) . ', ' .
	$this->_db->escape_string_add_quotes($args[$idArg]['valeur']) . ', ' .
	$this->_db->escape_string_add_quotes($args[$idArg]['type']) . '
)';
				$res = $this->_db->query($sql);
			}
		}
		if (!empty($tabUpdate)) {
			foreach ($tabUpdate as $idArg) {
				$sql =
'UPDATE critere_modesaisie
SET
	valeur = ' . $this->_db->escape_string_add_quotes($args[$idArg]['valeur']) . ',
	type = ' . $this->_db->escape_string_add_quotes($args[$idArg]['type']) . '
WHERE id_critere = ' . (int) $critere->getId() . '
	AND id_modesaisie = ' . (int) $mode->getId() . '
	AND nom = ' . $this->_db->escape_string_add_quotes($idArg);
				$res = $this->_db->query($sql);
			}
		}

		return true;
	}

	private function _create(Comptage_Model_Critere $critere)
	{
		$type = $critere->getType();
		$type = is_array($type) ? $type['id'] : $type;
		$scoring = $critere->isScoring() ? 't' : 'f';
		$location = $critere->isLocation() ? 't' : 'f';
		$desactive = $critere->isDesactive() ? 't' : 'f';
		$codeSql = $critere->getCodeSql();
		if (is_array($codeSql)) {
			$codeSql = json_encode($codeSql);
		}

		$sql =
'INSERT INTO critere (code, libelle, libellecourt, id_criteretype, typevaleur, scoring,
	location, champsql, codesql, desactive, typedonnee, ordre)
VALUES (' .
		$this->_db->escape_string_add_quotes($critere->getCode()) . ', ' .
		$this->_db->escape_string_add_quotes($critere->getLibelle()) . ', ' .
		$this->_db->escape_string_add_quotes($critere->getLibelleCourt()) . ', ' .
		(int) $type . ', ' .
		$this->_db->escape_string_add_quotes($critere->getTypeValeur()) . ', ' .
		$this->_db->escape_string_add_quotes($scoring) . ', ' .
		$this->_db->escape_string_add_quotes($location) . ', ' .
		$this->_db->escape_string_add_quotes($critere->getChampSql()) . ', ' .
		$this->_db->escape_string_add_quotes($codeSql) . ', ' .
		$this->_db->escape_string_add_quotes($desactive) . ', ' .
		$this->_db->escape_string_add_quotes($critere->getTypeDonnee()) . ', ' .
		(int) $critere->getOrdre() . '
) RETURNING id_critere;';
		$res = $this->_db->query($sql);
		if (!($row = $this->_db->fetchRow($res))) {
			throw new Db_UnexpectedResultException('Impossible de récupérer le critère créé', $this->_db, $sql);
		}
		$critere->setId($row['id_critere']);

		$this->_updateCibleType($critere);

		return true;
	}

	private function _update(Comptage_Model_Critere $critere)
	{
		$type = $critere->getType();
		$type = is_array($type) ? $type['id'] : $type;
		$scoring = $critere->isScoring() ? 't' : 'f';
		$location = $critere->isLocation() ? 't' : 'f';
		$desactive = $critere->isDesactive() ? 't' : 'f';
		$codeSql = $critere->getCodeSql();
		if (is_array($codeSql)) {
			$codeSql = json_encode($codeSql);
		}

		$sql =
'UPDATE critere SET
	libelle				= ' . $this->_db->escape_string_add_quotes($critere->getLibelle()) . ',
	libellecourt		= ' . $this->_db->escape_string_add_quotes($critere->getLibelleCourt()) . ',
	code				= ' . $this->_db->escape_string_add_quotes($critere->getCode()) . ',
	scoring				= ' . $this->_db->escape_string_add_quotes($scoring) . ',
	location			= ' . $this->_db->escape_string_add_quotes($location) . ',
	id_criteretype		= ' . (int) $type . ',
	typevaleur			= ' . $this->_db->escape_string_add_quotes($critere->getTypeValeur()) . ',
	champsql			= ' . $this->_db->escape_string_add_quotes($critere->getChampSql()) . ',
	codesql				= ' . $this->_db->escape_string_add_quotes($codeSql) . ',
	desactive			= ' . $this->_db->escape_string_add_quotes($desactive) . ',
	typedonnee			= ' . $this->_db->escape_string_add_quotes($critere->getTypeDonnee()) . ',
	ordre				= ' . (int) $critere->getOrdre() . '
WHERE id_critere = ' . (int) $critere->getId();
		$res = $this->_db->query($sql);

		$this->_updateCibleType($critere, $this->get($critere->getId()));

		// Supprime du registre => force la mise à jour
		if (isset($this->_tabInstances[$critere->getId()])) {
			unset($this->_tabInstances[$critere->getId()]);
		}

		return true;
	}

	private function _updateCibleType(Comptage_Model_Critere $critere, Comptage_Model_Critere $oldCritere = null)
	{
		if ($oldCritere !== null) {
			$old = array_keys($oldCritere->getListCibleType());
			$new = array_keys($critere->getListCibleType());
			$tabInsert = array_diff($new, $old);
			$tabDelete = array_diff($old, $new);
		}
		else {
			$tabInsert = array_keys($critere->getListCibleType());
			$tabDelete = array();
		}
		if (!empty($tabDelete)) {
			// TODO: A décommenter lors du passage en PHP5.4 (ne marche pas en PHP5.3, $this ne peut pas être utilisé avec une closure)
			//$callback = function($val) use ($this) {
			//	return $this->_db->escape_string_add_quotes($val);
			//};
			//$in = implode(', ', array_map($callback, $tabDelete));
			foreach ($tabDelete as $i => $e) {
				$tabDelete[$i] = $this->_db->escape_string_add_quotes($e);
			}
			$in = implode(', ', $tabDelete);
			$sql =
'DELETE FROM critere_cibletype
WHERE id_critere = ' . (int) $critere->getId() . '
	AND id_cibletype IN (' . $in . ')';
			$res = $this->_db->query($sql);
		}
		if (!empty($tabInsert)) {
			foreach ($tabInsert as $idCibleType) {
				$sql =
'INSERT INTO critere_cibletype (id_critere, id_cibletype)
VALUES (' .
	(int) $critere->getId() . ', ' .
	$this->_db->escape_string_add_quotes($idCibleType) . '
)';
				$res = $this->_db->query($sql, 'sql');
			}
		}

		return true;
	}

	/**
	 * Instancie un critère à partir d'un enregistrement
	 * @param array $row un enregistrement
	 * @return Comptage_Model_Critere
	 */
	private function _getFromResult($row)
	{
		$obj = new Comptage_Model_Critere();
		$obj->setId($row['id_critere']);
		$obj->setCode($row['code']);
		$obj->setLibelle($row['libelle']);
		$obj->setLibelleCourt($row['libellecourt']);
		$obj->setScoring($row['scoring'] == 't' ? true : false);
		$obj->setLocation($row['location'] == 't' ? true : false);
		$obj->setDesactive($row['desactive'] == 't' ? true : false);
		$obj->setType(array('id' => $row['type_id'],
							'code' => $row['type_code'],
							'libelle' => $row['type_libelle']));
		$obj->setTypeValeur($row['typevaleur']);
		$obj->setTypeDonnee($row['typedonnee']);
		$obj->setOrdre($row['ordre']);
		$obj->setChampSql($row['champsql']);
		if (isJson($row['codesql'])) {
			$obj->setCodeSql(json_decode($row['codesql'], true));
		}
		else {
			$obj->setCodeSql($row['codesql']);
		}

		// Ajoute au registre
		if ($this->_isRegistryEnabled) {
			$this->_tabInstances[$obj->getId()] = $obj;
		}

		return $obj;
	}

	public function __call($funct, $args)
	{
		if (preg_match('#^(?P<prefix>getBy)(?P<param>\w+)$#', $funct, $matches)) {
			$value = array_shift($args);
			if (!empty($args)) {
				$args = $args[0];
			}
			return $this->_getBy(strtolower($matches['param']), $value, $args);
		}
		throw new \BadMethodCallException("Méthode inconnue " . __CLASS__ . "::$funct");
	}

	/**
	 * Retourne une ou plusieurs instance(s) de Comptage_Model_Critere
	 * Appelée sous la forme dao->getBy{Param}({value})
	 * @param string $param nom du paramètre (cibletype|id)
	 * @param mixed $value valeur du paramètre
	 * @param array $others liste de paramètres nommés
	 * @throws \BadMethodCallException si param non géré
	 * @return array|Comptage_Model_Critere
	 */
	private function _getBy($param, $value = null, $others = null)
	{
		// Récupère le critère dans le registre plutôt que de recréer l'objet
		if ($param == 'id' && $this->_isRegistryEnabled) {
			if (isset($this->_tabInstances[$value])) {
				//Logger::log('en cache: ' . $value);
				return $this->_tabInstances[$value];
			}
		}

		$sql =
'SELECT critere.*,
	criteretype.id_criteretype as type_id,
	criteretype.code as type_code,
	criteretype.libelle as type_libelle
FROM critere
INNER JOIN criteretype ON criteretype.id_criteretype = critere.id_criteretype';

		$join  = array();
		$where = array();
		$order = array();
		switch ($param) {
			case 'cibletype':
				$join[] = 'INNER JOIN critere_cibletype cct ON cct.id_critere = critere.id_critere';
				$where[] = 'cct.id_cibletype = ' . $this->_db->escape_string_add_quotes($value);
			break;

			case 'id':
				if (is_numeric($value)) {
					$where[] = 'critere.id_critere = ' . (int) $value;
				}
				else {
					$where[] = 'critere.code = ' . $this->_db->escape_string_add_quotes($value);
				}
				break;

			case 'all':
				break;

			default:
				throw new \BadMethodCallException("Paramètre non géré $param");
			break;
		}

		if (isset($others) && isset($others['order'])) {
			switch ($others['order']) {
				case 'type':
					$order[] = 'criteretype.ordre, criteretype.libelle';
			}
		}

		$order[] = 'critere.ordre, critere.libelle';
		if (!empty($join)) {
			$sql .=  "\n" . implode("\n", $join);
		}
		if (!empty($where)) {
			$sql .=  "\n" . 'WHERE ' . implode('AND ', $where);
		}
		if (!empty($order)) {
			$sql .= "\n" . 'ORDER BY ' . implode(', ', $order);
		}

		//Logger::log($sql, 'sql');

		if ($param == 'id') {
			$res = $this->_db->query($sql);
			$row = $this->_db->fetchRow($res);
			if (empty($row)) {
				throw new Db_UnexpectedResultException('Le critère avec l\'id ' . $value . ' n\'existe pas', $this->_db, $sql);
			}
			return $this->_getFromResult($row);
		}
		else {
			$list = array();
			$res = $this->_db->query($sql);
			while ($row = $this->_db->fetchRow($res)) {
				$list[] = $this->_getFromResult($row);
			}
			return $list;
		}
	}

	/**
	 * Retourne la liste des arguments liés à un critère et un mode de saisie
	 * @param Comptage_Model_Critere $crit
	 * @param int|string $modeSaisie
	 * @return array indexé sur nom, {nom, valeur, type}
	 */
	public function getModeSaisieArgs(Comptage_Model_Critere $crit, $modeSaisie)
	{
		$list = array();

		$sql =
'SELECT crm.nom, crm.valeur, crm.type
FROM critere_modesaisie crm
	INNER JOIN modesaisie ms ON ms.id_modesaisie = crm.id_modesaisie
WHERE crm.id_critere = ' . (int) $crit->getId();
		if (is_numeric($modeSaisie)) {
			$sql .= '
	AND crm.id_modesaisie = ' . (int) $modeSaisie;
		}
		else {
			$sql .= '
	AND ms.code = ' . $this->_db->escape_string_add_quotes($modeSaisie);
		}
		$res = $this->_db->query($sql);
		while ($row = $this->_db->fetchRow($res)) {
			$list[$row['nom']] = array('nom'	=> $row['nom'],
										'valeur'=> $row['valeur'],
										'type'	=> $row['type']);
		}

		return $list;
	}

	/**
	 * Retourne un pseudo critère (tests)
	 * @param string $code code du critère
	 * @throws RuntimeException si code critère non géré
	 * @return Comptage_Model_Critere
	 */
	public static function getMock($code)
	{
		$crit = new Comptage_Model_Critere();

		switch ($code) {
			case 'ville':
			case 1:
				$crit->setId(1);
				$crit->setCode('ville');
				$crit->setLibelle('Ville');
				$crit->setTypeCritere(array('type_sql' => 'in',
											'code_sql' => '([1])'));
				$crit->setChampSql('VILLE');
				$crit->setListeValeurs(array());//Comptage_Model_Common::getTreeRubrique();
				$crit->setInterfArgs(array(
										'autocomplete' =>
											array(
												'desc' => 'Recherchez au moins les 3 premières lettres d\'une rubrique',
												'search' => 'Comptage_Model_Common::lookupRubrique'),));
				$crit->setValueRemovable(true);
				break;

			case 'sexe':
			case 3:
				$crit->setId(3);
				$crit->setCode('sexe');
				$crit->setLibelle('Sexe');
				$crit->setTypeCritere(array('type_sql' => 'in',
											'code_sql' => '([1])'));
				$crit->setChampSql('SEXE');
				$crit->setListeValeurs(array('M-D' => 'Homme',
											'F-D' => 'Femme',
											'' => 'Indifférent'));
				$crit->setInterfArgs(array('radio' =>
										array('desc' => array('nom' => 'desc', 'valeur' => 'Veuillez sélectionner le sexe'),
												'liste' => array('nom' => 'liste', 'valeur' => serialize(array('M-D' => 'Homme',
																												'F-D' => 'Femme',
																												'' => 'Indifférent')),
																'type' => 'serialized'))));
				break;

			case 'habitat':
			case 4:
				$crit->setId(4);
				$crit->setCode('habitat');
				$crit->setLibelle('Type d\'habitat');
				$crit->setTypeCritere(array('type_sql' => 'in',
											'code_sql' => '([1])'));
				$crit->setChampSql('TYPE_HABITAT');
				$crit->setListeValeurs(array('C' => 'Collectif',
											'I' => 'Individuel',
											'' => 'Indifférent'));
				$crit->setInterfArgs(array('radio' =>
										array('desc' => 'Veuillez sélectionner le type d\'habitat')));
				break;

			case 'foyer':
			case 6:
				$crit->setId(6);
				$crit->setCode('foyer');
				$crit->setLibelle('Nombre de foyers');
				$crit->setTypeCritere(array('type_sql' => 'in',
											'code_sql' => '([1])'));
				$crit->setChampSql('NB_FOYERS');
				$crit->setListeValeurs(array('2' => '2',
											'10' => '10',
											'20' => '20',
											'30' => '30',
											'40' => 'plus...'));
				$crit->setInterfArgs(array('intervalle' =>
										array('desc' => 'Veuillez sélectionner un nombre de foyers minimum et maximum')));
				break;

			case 'revenu':
			case 5:
				$crit->setId(5);
				$crit->setCode('revenu');
				$crit->setLibelle('Revenus');
				$crit->setTypeCritere(array('type_sql' => 'in',
											'code_sql' => '([1])'));
				$crit->setChampSql('REVENUS');
				$crit->setListeValeurs(array(1 => 'Faible',
											2 => 'Médian',
											3 => 'Elevé',
											4 => 'Très élevé'));
				$crit->setInterfArgs(array('intervalle' =>
										array('desc' => 'Veuillez sélectionner un revenu minimum et maximum')));
				break;

			case 'age':
			case 2:
				$crit->setId(2);
				$crit->setCode('age');
				$crit->setLibelle('Age');
				$crit->setTypeCritere(array('type_sql' => 'in',
											'code_sql' => '([1])'));
				$crit->setChampSql('AGE');
				$crit->setListeValeurs(array('0' => '0',
											'20' => '20 ans',
											'30' => '30 ans',
											'40' => '40 ans',
											'50' => '50 ans',
											'60' => '60 ans',
											'70' => '70 ans',
											'80' => 'plus...'));
				$crit->setInterfArgs(array(
										'intervalle' =>
											array(
												'desc' => 'Veuillez sélectionner un intervalle d\'âges'),
										'checkbox' => array(
												'desc' => 'Veuillez cocher les tranches d\'âge à sélectionner')));
				break;

			case 'activite':
			case 7:
				$crit->setId(7);
				$crit->setCode('activite');
				$crit->setLibelle('Activité');
				$crit->setTypeCritere(array('type_sql' => 'in',
											'code_sql' => '([1])'));
				$crit->setChampSql('ACTIVITE');
				$crit->setListeValeurs(array());//Comptage_Model_Common::getTreeRubrique();
				$crit->setInterfArgs(array(
										'tree' =>
											array(
												'desc' => 'Veuillez sélectionner une ou plusieurs activités dans l\'arborescence'),
										'autocomplete' =>
											array(
												'desc' => 'Recherchez au moins les 3 premières lettres d\'une rubrique',
												'search' => 'Comptage_Model_Common::lookupRubrique'),
										'import' => array(
												'desc' => 'Import de codes rubriques',
												'filter' => 'Comptage_Model_Common::filterRubrique'),));
				$crit->setValueRemovable(true);
				break;

			default:
				throw new RuntimeException('Critère ' . $code . ' non géré');
		}

		return $crit;
	}

}