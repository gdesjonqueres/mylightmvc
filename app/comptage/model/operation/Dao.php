<?php
/**
 * Classe pour gérer l'entité OPERATION en BDD
 * Motifs de conception utilisés: DAO, SINGLETON
 *
 * @package Requeteur\Model\Operation
 */
class Comptage_Model_Operation_Dao
{
	/**
	 * Singleton
	 * @var Comptage_Model_Operation_Dao
	 */
	private static $_instance;

	/**
	 * Db
	 * @var Db_Postgre
	 */
	private $_db;

	private function __construct()
	{
		$this->_db = Db::getInstance('Requeteur');
	}

	/**
	 * @return Comptage_Model_Operation_Dao
	 */
	public static function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Retourne la liste des types d'opération
	 * @return array
	 */
	public function getListType()
	{
		$list = array();

		$sql = 'SELECT id_operationtype, libelle FROM operationtype';
		$res = $this->_db->query($sql);
		while ($row = $this->_db->fetchRow($res)) {
			$list[$row['id_operationtype']] = $row['libelle'];
		}

		return $list;
	}

	/**
	 * Retourne la liste des types de données
	 * @return array
	 */
	public function getListDonneesType()
	{
		$list = array();

		$sql = 'SELECT id_donneestype, libelle FROM donneestype';
		$res = $this->_db->query($sql);
		while ($row = $this->_db->fetchRow($res)) {
			$list[$row['id_donneestype']] = $row['libelle'];
		}

		return $list;
	}

	/**
	 * Retourne l'operation avec l'id demandé
	 * @param int $id id operation
	 * @return Comptage_Model_Operation
	 */
	public function get($id)
	{
		return $this->_getBy('id', $id);
	}

	/**
	 * Retourne une operation complète contenant toutes les cibles, ...
	 * @param int|Comptage_Model_Operation $op operation à charger
	 * @throws InvalidArgumentException si argument invalide
	 */
	public function getFullOperation($op)
	{
		if ($op instanceof Comptage_Model_Operation) {
			$operation = $op;
		}
		elseif (is_numeric($op)) {
			$operation = $this->get($op);
		}
		else {
			throw new InvalidArgumentException('Type argument invalide');
		}

		$operation->setCibles(Comptage_Model_Cible_Dao::getInstance()->getByOperation($operation->getId()));

		return $operation;
	}

	public function save(Comptage_Model_Operation $op, Comptage_Model_Campagne $cpg = NULL)
	{
		if ($op->getId() && strpos($op->getId(), 'tmp') === false) {
			$this->_update($op, $cpg);
		}
		else {
			if (!$cpg) {
				throw new \InvalidArgumentException('Pas de campagne fournie en paramètre');
			}
			$this->_create($op, $cpg);
		}

		return true;
	}

	public function saveFullOperation(Comptage_Model_Operation $op, Comptage_Model_Campagne $cpg = NULL)
	{
		$this->save($op, $cpg);

		// Gestion des cibles
		$tabNotDelete = array();
		foreach ($op as $cible) {
			Comptage_Model_Cible_Dao::getInstance()->save($op, $cible);
			$tabNotDelete[] = (int) $cible->getId();
		}
		$this->_deleteCibles($op, $tabNotDelete);

		return true;
	}

	private function _create(Comptage_Model_Operation $op, Comptage_Model_Campagne $cpg = NULL)
	{
		$ct = $this->_db->intOrNull($op->getComptage());

		$sql =
'INSERT INTO cpg_operation (id_campagne, id_cibletype, id_operationtype, id_statut, id_donneestype, id_contact, comptage)
VALUES (' .
		(int) $cpg->getId() . ', ' .
		$this->_db->escape_string_add_quotes($op->getCibleType()) . ', ' .
		$this->_db->escape_string_add_quotes($op->getType()) . ', ' .
		$this->_db->escape_string_add_quotes($op->getStatut()) . ', ' .
		$this->_db->escape_string_add_quotes($op->getDonneesType()) . ', ' .
		(int) $op->getContactId() . ', ' .
		$ct . '
) RETURNING id_operation;';
		$res = $this->_db->query($sql);
		if (!($row = $this->_db->fetchRow($res))) {
			throw new Db_UnexpectedResultException('Impossible de récupérer l\'opération créée', $this->_db, $sql);
		}
		$op->setId($row['id_operation']);

		return true;
	}

	private function _update(Comptage_Model_Operation $op, Comptage_Model_Campagne $cpg = NULL)
	{
		$ct = $this->_db->intOrNull($op->getComptage());

		$sql =
'UPDATE cpg_operation SET
	id_cibletype		= ' . $this->_db->escape_string_add_quotes($op->getCibleType()) . ',
	id_operationtype	= ' . $this->_db->escape_string_add_quotes($op->getType()) . ',
	id_statut			= ' . $this->_db->escape_string_add_quotes($op->getStatut()) . ',
	id_donneestype		= ' . $this->_db->escape_string_add_quotes($op->getDonneesType()) . ',
	id_contact			= ' . (int) $op->getContactId() . ',
	comptage			= ' . $ct . '
WHERE id_operation = ' . (int) $op->getId();
		$res = $this->_db->query($sql);

		return true;
	}

	/**
	 * Supprime les Cible
	 * @param Comptage_Model_Operation $op
	 * @param array $list d'ids à conserver
	 */
	private function _deleteCibles(Comptage_Model_Operation $op, array $list)
	{
		$sql =
'DELETE FROM cpg_cible
WHERE id_operation = ' . (int) $op->getId();
		if (!empty($list)) {
			$sql .=
'	AND id_cible NOT IN (' . implode(', ', $list) . ')';
		}
		$res = $this->_db->query($sql);

		return true;
	}

	/**
	 * Instancie une operation à partir d'un enregistrement
	 * @param array $row un enregistrement
	 * @return Comptage_Model_Operation
	 */
	private function _getFromResult($row)
	{
		$obj = new Comptage_Model_Operation();
		$obj->setId($row['id_operation']);
		$obj->setStatut($row['id_statut']);
		$obj->setType($row['id_operationtype']);
		$obj->setCibleType($row['id_cibletype']);
		$obj->setDonneesType($row['id_donneestype']);
		$obj->setContactId($row['id_contact']);
		$obj->setComptage($this->_db->nullIfEmpty($row['comptage']));
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
		$sql = 'SELECT * FROM cpg_operation';

		$join  = array();
		$where = array();
		$order = array();
		switch ($param) {
			case 'id':
				$where[] = 'cpg_operation.id_operation = ' . (int) $value;
				break;

			case 'campagne':
				$where[] = 'cpg_operation.id_campagne = ' . (int) $value;
				break;

			default:
				throw new \BadMethodCallException("Paramètre non géré $param");
			break;
		}

		//if (isset($others) && isset($others['order'])) {
		//	switch ($others['order']) {
		//	}
		//}

		$order[] = 'cpg_operation.id_operation';
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
				throw new Db_UnexpectedResultException('L\'operation avec l\'id ' . $value . ' n\'existe pas', $this->_db, $sql);
			}
			return $this->_getFromResult($row);
		}
		else {
			$list = new ArrayObject();//array();
			$res = $this->_db->query($sql);
			while ($row = $this->_db->fetchRow($res)) {
				$list[$row['id_operation']] = $this->_getFromResult($row);
			}
			return $list;
		}
	}

}