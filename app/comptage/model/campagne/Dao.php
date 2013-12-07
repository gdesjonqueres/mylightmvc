<?php
/**
 * Classe pour gérer l'entité CAMPAGNE en BDD
 * Motifs de conception utilisés: DAO, SINGLETON
 *
 * @package Requeteur\Model\Campagne
 */
class Comptage_Model_Campagne_Dao
{
	/**
	 * Singleton
	 * @var Comptage_Model_Campagne_Dao
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
	 * @return Comptage_Model_Campagne_Dao
	 */
	public static function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		self::$_instance->_db->setContext(self::$_instance);
		return self::$_instance;
	}

	/**
	 * Retourne la campagne avec l'id demandé
	 * @param int $id id campagne
	 * @return Comptage_Model_Campagne
	 */
	public function get($id)
	{
		return $this->_getBy('id', $id);
	}

	/**
	 * Retourne une campagne complète contenant toutes les opérations, cibles, ...
	 * @param int|Comptage_Model_Campagne $cpg campagne à charger
	 * @throws InvalidArgumentException si argument invalide
	 */
	public function getFullCampagne($cpg)
	{
		if ($cpg instanceof Comptage_Model_Campagne) {
			$campagne = $cpg;
		}
		elseif (is_numeric($cpg)) {
			$campagne = $this->get($cpg);
		}
		else {
			throw new InvalidArgumentException('Type argument invalide');
		}

		$campagne->setOperations(Comptage_Model_Operation_Dao::getInstance()->getByCampagne($campagne->getId()));
		foreach ($campagne as $operation) {
			Comptage_Model_Operation_Dao::getInstance()->getFullOperation($operation);
		}

		return $campagne;
	}

	/**
	 * Retourne la liste de toutes les campagnes
	 * @return array liste de Comptage_Model_Campagne
	 */
	//public function getList()
	//{
	//	return $this->_getBy('all');
	//}

	/**
	 * Effectue la sauvegarde en base (création ou mise à jour)
	 * @param Comptage_Model_Campagne $cpg
	 */
	public function save(Comptage_Model_Campagne $cpg)
	{
		if ($cpg->getId() && strpos($cpg->getId(), 'tmp') === false) {
			$this->_update($cpg);
		}
		else {
			$this->_create($cpg);
		}
		return true;
	}

	/**
	 * Effectue la sauvegarde en base de la campagne mais aussi des objets imbriqués
	 * @param Comptage_Model_Campagne $cpg
	 */
	public function saveFullCampagne(Comptage_Model_Campagne $cpg)
	{
		$this->save($cpg);

		// Gestion des opérations
		foreach ($cpg as $op) {
			Comptage_Model_Operation_Dao::getInstance()->saveFullOperation($op, $cpg);
		}

		return true;
	}

	private function _create(Comptage_Model_Campagne $cpg)
	{
		$userId = Session::getUser()->getId();

		$sql =
'INSERT INTO cpg_campagne (id_utilisateur, libelle, date_creation, id_statut)
VALUES (' .
		(int) $cpg->getUserId() . ', ' .
		$this->_db->escape_string_add_quotes($cpg->getLibelle()) . ', ' .
		$this->_db->escape_string_add_quotes(date('Y-m-d H:i:s', $cpg->getDateCreation())) . ', ' .
		$this->_db->escape_string_add_quotes($cpg->getStatut()) . '
) RETURNING id_campagne;';
		$res = $this->_db->query($sql);
		if (!($row = $this->_db->fetchRow($res))) {
			throw new Db_UnexpectedResultException('Impossible de récupérer la campagne créée', $this->_db, $sql);
		}
		$cpg->setId($row['id_campagne']);

		return true;
	}

	private function _update(Comptage_Model_Campagne $cpg)
	{
		$sql =
'UPDATE cpg_campagne SET
	libelle 	= ' . $this->_db->escape_string_add_quotes($cpg->getLibelle()) . ',
	id_statut 	= ' . $this->_db->escape_string_add_quotes($cpg->getStatut()) . '
WHERE id_campagne = ' . (int) $cpg->getId();
		$res = $this->_db->query($sql);

		return true;
	}

	/**
	 * Supprime les Operations
	 * @param Comptage_Model_Campagne $cpg
	 * @param array $list d'ids à conserver
	 */
	private function _deleteOperations(Comptage_Model_Campagne $cpg, array $list)
	{
		$sql =
'DELETE FROM cpg_operation
WHERE id_campagne = ' . (int) $op->getId();
		if (!empty($list)) {
			$sql .=
'	AND id_operation NOT IN (' . implode(', ', $list) . ')';
		}
		$res = $this->_db->query($sql);

		return true;
	}

	/**
	 * Instancie une campagne à partir d'un enregistrement
	 * @param array $row un enregistrement
	 * @return Comptage_Model_Campagne
	 */
	private function _getFromResult($row)
	{
		$obj = new Comptage_Model_Campagne();
		$obj->setId($row['id_campagne']);
		$obj->setDateCreation(strtotime($row['date_creation']));
		$obj->setLibelle($row['libelle']);
		$obj->setStatut($row['id_statut']);
		$obj->setUserId($row['id_utilisateur']);
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
	 * Retourne une ou plusieurs instance(s) de Comptage_Model_Campagne
	 * Appelée sous la forme dao->getBy{Param}({value})
	 * @param string $param nom du paramètre (cibletype|id)
	 * @param mixed $value valeur du paramètre
	 * @param array $others liste de paramètres nommés
	 * @throws \BadMethodCallException si param non géré
	 * @return array|Comptage_Model_Campagne
	 */
	private function _getBy($param, $value = null, $others = null)
	{
		$sql = 'SELECT * FROM cpg_campagne c';

		$join  = array();
		$where = array();
		$order = array();
		switch ($param) {
			case 'id':
				$where[] = 'c.id_campagne = ' . (int) $value;
				break;

			case 'user':
				$where[] = 'c.id_utilisateur = ' . (int) $value;
				break;

			case 'all':
				break;

			default:
				throw new \BadMethodCallException("Paramètre non géré $param");
			break;
		}

		if (isset($others['statut'])) {
			$join[] = 'INNER JOIN cpg_operation o ON o.id_campagne = c.id_campagne';
			$exp = new Db_QueryBuilder_Expression();
			if (is_array($others['statut'])) {
				$where[] = $exp->in('o.id_statut', $others['statut'], array($this->_db, 'escape_string_add_quotes'));
			}
			else {
				$where[] = $exp->eq('o.id_statut', $this->_db->escape_string_add_quotes($others['statut']));
			}
		}

		if (isset($others) && isset($others['order'])) {
			switch ($others['order']) {
				case 'id desc':
					$order[] = 'c.id_campagne DESC';
			}
		}

		$order[] = 'c.libelle';
		if (!empty($join)) {
			$sql .=  "\n" . implode("\n", $join);
		}
		if (!empty($where)) {
			$sql .=  "\n" . 'WHERE ' . implode('AND ', $where);
		}
		if (!empty($order)) {
			$sql .= "\n" . 'ORDER BY ' . implode(', ', $order);
		}

		if (isset($others) && isset($others['limit'])) {
			$sql .= "\n" . 'LIMIT ' . $others['limit'];
		}

		// Logger::log($sql, 'sql');

		if ($param == 'id') {
			$res = $this->_db->query($sql);
			$row = $this->_db->fetchRow($res);
			if (empty($row)) {
				throw new Db_UnexpectedResultException('La campagne avec l\'id ' . $value . ' n\'existe pas', $this->_db, $sql);
			}
			return $this->_getFromResult($row);
		}
		else {
			$list = array();
			$res = $this->_db->query($sql);
			while ($row = $this->_db->fetchRow($res)) {
				$list[$row['id_campagne']] = $this->_getFromResult($row);
			}
			return $list;
		}
	}

	/**
	 * Retourne une liste de campagnes
	 * @param array $filters liste de filtres, valeurs possibles: "statut", "user"
	 * @param array $orders liste d'éléments pour ordonner les résultats, valeurs: "id", "date"
	 * @param int $limit nombre de lignes max
	 * @return array
	 */
	public function getList($filters = array(), $orders = array(), $limit = null)
	{
		$qb = new Db_QueryBuilder();

		$qb	->from('cpg_campagne c')
			->join('INNER JOIN cpg_operation o ON o.id_campagne = c.id_campagne')
			->join('INNER JOIN statut s ON s.id_statut = o.id_statut')
			->join('INNER JOIN cibletype ct ON ct.id_cibletype = o.id_cibletype')
			->join('INNER JOIN operationtype ot ON ot.id_operationtype = o.id_operationtype')
			->join('INNER JOIN donneestype dt ON dt.id_donneestype = o.id_donneestype')
			->join('INNER JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur')
			->join('LEFT JOIN contact co ON co.id_contact = o.id_contact')
			->join('LEFT JOIN cpg_extraction ex ON ex.id_operation = o.id_operation');

		$qb	->select('c.id_campagne')
			->select('c.libelle')
			->select('to_char(c.date_creation, \'dd/mm/yyyy\') AS date')
			->select('(u.prenom || \' \' || u.nom) AS utilisateur')
			->select('s.libelle AS statut')
			->select('o.id_statut AS statut_id')
			->select('o.id_operation')
			->select('o.comptage')
			->select('ct.libelle AS cible')
			->select('ot.libelle AS type')
			->select('dt.libelle AS donnees')
			->select('co.societe AS contact_societe')
			->select('(co.prenom || \' \' || co.nom) AS contact_nom')
			->select('co.email AS contact_email')
			->select('co.telephone AS contact_tel')
			->select('ex.quantite');

		$exp = new Db_QueryBuilder_Expression();
		foreach ($filters as $k => $v) {
			if ($k == 'statut') {
				if (is_array($v)) {
					$qb->andWhere($exp->in('o.id_statut', $v, array($this->_db, 'escape_string_add_quotes')));
				}
				else {
					$qb->andWhere($exp->eq('o.id_statut', $this->_db->escape_string_add_quotes($v)));
				}
			}
			else if ($k == 'user') {
				if (is_array($v)) {
					$qb->andWhere($exp->in('c.id_utilisateur', $v));
				}
				else {
					$qb->andWhere($exp->eq('c.id_utilisateur', $v));
				}
			}
		}

		if (!empty($orders)) {
			foreach ($orders as $k => $v) {
				if ($k == 'id') {
					$qb->orderBy('c.id_campagne', $v);
				}
				else if ($k == 'date') {
					$qb->orderBy('c.date_creation', $v);
				}
			}
		}
		else {
			$qb	->orderBy('c.date_creation', 'DESC')
				->orderBy('c.libelle');
		}

		if ($limit) {
			$qb->limit($limit);
		}

		Logger::log($qb->getQuery(), 'sql');

		$list = array();
		$res = $this->_db->query($qb->getQuery());
		while ($row = $this->_db->fetchRow($res)) {
			$list[$row['id_campagne']] = $row;
		}

		return $list;
	}

}