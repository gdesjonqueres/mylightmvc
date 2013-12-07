<?php
/**
 * Classe pour gérer l'entité CIBLE en BDD
 * Motifs de conception utilisés: DAO, SINGLETON
 *
 * @package Requeteur\Model\Cible
 */
class Comptage_Model_Cible_Dao
{
	/**
	 * Singleton
	 * @var Comptage_Model_Cible_Dao
	 */
	private static $_instance;

	/**
	 * Db
	 * @var Db
	 */
	private $_db;

	private function __construct()
	{
		$this->_db = Db::getInstance('Requeteur');
	}

	/**
	 * @return Comptage_Model_Cible_Dao
	 */
	public static function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Retourne la liste des type de cible (part, pro)
	 * @return array
	 */
	public function getListType()
	{
		$list = array();

		$sql = 'SELECT id_cibletype, libelle FROM cibletype';
		$res = $this->_db->query($sql);
		while ($row = $this->_db->fetchRow($res)) {
			$list[$row['id_cibletype']] = $row['libelle'];
		}

		return $list;
	}

	/**
	 * Retourne la liste des types de cible liés à un critère
	 * @param Comptage_Model_Critere $crit
	 * @return array
	 */
	public function getListTypeByCritere(Comptage_Model_Critere $crit)
	{
		$list = array();

		$sql =
'SELECT id_cibletype, libelle FROM cibletype
WHERE id_cibletype IN (
	SELECT DISTINCT cc.id_cibletype FROM critere_cibletype cc
	WHERE cc.id_critere = ' . (int) $crit->getId() . '
)
ORDER BY libelle';
		$res = $this->_db->query($sql);
		while ($row = $this->_db->fetchRow($res)) {
			$list[$row['id_cibletype']] = $row['libelle'];
		}

		return $list;
	}

	/**
	 * Retourne la cible avec l'id demandé
	 * @param int $id id cible
	 * @return Comptage_Model_Cible
	 */
	public function get($id)
	{
		return $this->_getBy('id', $id);
	}

	public function save(Comptage_Model_Operation $op, Comptage_Model_Cible $cible)
	{
		if ($cible->getId() && strpos($cible->getId(), 'tmp') === false) {
			$this->_update($op, $cible);
		}
		else {
			$this->_create($op, $cible);
		}

		// Gestion des CibleCritere
		$tabNotDelete = array();
		foreach ($cible as $cibleCrit) {
			$this->_saveCibleCritere($cible, $cibleCrit);
			$tabNotDelete[] = (int) $cibleCrit->id;
		}
		$this->_deleteCibleCriteres($cible, $tabNotDelete);

		return true;
	}

	private function _create(Comptage_Model_Operation $op, Comptage_Model_Cible $cible)
	{
		$sql =
'INSERT INTO cpg_cible (id_operation, id_cibletype, libelle, numero)
VALUES (' .
		(int) $op->getId() . ', ' .
		$this->_db->escape_string_add_quotes($cible->getCibleType()) . ', ' .
		$this->_db->escape_string_add_quotes($cible->getLibelle()) . ', ' .
		(int) $cible->getRank($op) . '
) RETURNING id_cible;';
		$res = $this->_db->query($sql);
		if (!($row = $this->_db->fetchRow($res))) {
			throw new Db_UnexpectedResultException('Impossible de récupérer la cible créée', $this->_db, $sql);
		}
		$cible->setId($row['id_cible']);

		return true;
	}

	private function _update(Comptage_Model_Operation $op, Comptage_Model_Cible $cible)
	{
		$sql =
'UPDATE cpg_cible SET
	id_cibletype	= ' . $this->_db->escape_string_add_quotes($cible->getCibleType()) . ',
	libelle			= ' . $this->_db->escape_string_add_quotes($cible->getLibelle()) . ',
	numero			= ' . (int) $cible->getRank($op) . '
WHERE id_cible = ' . (int) $cible->getId();
		$res = $this->_db->query($sql);

		return true;
	}

	private function _saveCibleCritere(Comptage_Model_Cible $cible, Comptage_Model_Cible_Critere $cibleCrit)
	{
		if ($cibleCrit->id) {
			$this->_updateCibleCritere($cible, $cibleCrit);
		}
		else {
			$this->_createCibleCritere($cible, $cibleCrit);
		}
		return true;
	}

	private function _createCibleCritere(Comptage_Model_Cible $cible, Comptage_Model_Cible_Critere $cibleCrit)
	{
		$sql =
'INSERT INTO cpg_cible_critere (id_cible, id_critere, typevaleur, valeurs)
VALUES (' .
		(int) $cible->getId() . ', ' .
		(int) $cibleCrit->idCritere . ', ' .
		$this->_db->escape_string_add_quotes($cibleCrit->typeValeur) . ', ' .
		$this->_db->escape_string_add_quotes(toJson($cibleCrit->tabValeurs)) . '
) RETURNING id_cic;';
		$res = $this->_db->query($sql);
		if (!($row = $this->_db->fetchRow($res))) {
			throw new Db_UnexpectedResultException('Impossible de récupérer la cible_critere créée', $this->_db, $sql);
		}
		//$op->setId($row['id_cic']);
		$cibleCrit->id = $row['id_cic'];

		return true;
	}

	private function _updateCibleCritere(Comptage_Model_Cible $cible, Comptage_Model_Cible_Critere $cibleCrit)
	{
		$sql =
'UPDATE cpg_cible_critere SET
	typevaleur 	= ' . $this->_db->escape_string_add_quotes($cibleCrit->typeValeur) . ',
	valeurs 	= ' . $this->_db->escape_string_add_quotes(toJson($cibleCrit->tabValeurs)) . '
WHERE id_cic = ' . (int) $cibleCrit->id;
		$res = $this->_db->query($sql);

		return true;
	}

	/**
	 * Supprime les CibleCritere
	 * @param Comptage_Model_Cible $cible
	 * @param array $list d'ids à conserver
	 */
	private function _deleteCibleCriteres(Comptage_Model_Cible $cible, array $list)
	{
		$sql =
'DELETE FROM cpg_cible_critere
WHERE id_cible = ' . (int) $cible->getId();
		if (!empty($list)) {
			$sql .=
'	AND id_cic NOT IN (' . implode(', ', $list) . ')';
		}
		$res = $this->_db->query($sql);

		return true;
	}

	/**
	 * Instancie une cible à partir d'un enregistrement
	 * @param array $row un enregistrement
	 * @return Comptage_Model_Cible
	 */
	private function _getFromResult($row)
	{
		$obj = new Comptage_Model_Cible();
		$obj->setId($row['id_cible']);
		$obj->setCibleType($row['id_cibletype']);
		$obj->setLibelle($row['libelle']);

		// Récupère la liste des CibleCritere
		$sql = 'SELECT * FROM cpg_cible_critere WHERE id_cible = ' . (int) $obj->getId() . ' ORDER BY id_critere';
		$res = $this->_db->query($sql);
		while ($row = $this->_db->fetchRow($res)) {
			$cc = new Comptage_Model_Cible_Critere();
			$cc->id = $row['id_cic'];
			$cc->idCritere = $row['id_critere'];
			$cc->typeValeur = $row['typevaleur'];
			$cc->tabValeurs = json_decode($row['valeurs'], true);
			$obj[$cc->idCritere] = $cc;
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
	 * Retourne une ou plusieurs instance(s) de Comptage_Model_Operation
	 * Appelée sous la forme dao->getBy{Param}({value})
	 * @param string $param nom du paramètre (cibletype|id)
	 * @param mixed $value valeur du paramètre
	 * @param array $others liste de paramètres nommés
	 * @throws \BadMethodCallException si param non géré
	 * @return array|Comptage_Model_Operation
	 */
	private function _getBy($param, $value = null, $others = null)
	{
		$sql = 'SELECT * FROM cpg_cible';

		$join  = array();
		$where = array();
		$order = array();
		switch ($param) {
			case 'id':
				$where[] = 'cpg_cible.id_cible = ' . (int) $value;
				break;

			case 'operation':
				$where[] = 'cpg_cible.id_operation = ' . (int) $value;
				break;

			default:
				throw new \BadMethodCallException("Paramètre non géré $param");
			break;
		}

		//if (isset($others) && isset($others['order'])) {
		//	switch ($others['order']) {
		//	}
		//}

		$order[] = 'cpg_cible.numero';
		if (!empty($join)) {
			$sql .=  "\n" . implode("\n", $join);
		}
		if (!empty($where)) {
			$sql .=  "\n" . 'WHERE ' . implode('AND ', $where);
		}
		if (!empty($order)) {
			$sql .= "\n" . 'ORDER BY ' . implode(', ', $order);
		}

		// Logger::log($sql, 'sql');

		if ($param == 'id') {
			$res = $this->_db->query($sql);
			$row = $this->_db->fetchRow($res);
			if (empty($row)) {
				throw new Db_UnexpectedResultException('La cible avec l\'id ' . $value . ' n\'existe pas', $this->_db, $sql);
			}
			return $this->_getFromResult($row);
		}
		else {
			$list = new ArrayObject();//array();
			$res = $this->_db->query($sql);
			while ($row = $this->_db->fetchRow($res)) {
				$list[$row['id_cible']] = $this->_getFromResult($row);
			}
			return $list;
		}
	}

}