<?php
/**
 * Classe pour gérer l'entité MODESAISIE en BDD
 * Motifs de conception utilisés: DAO, SINGLETON
 *
 * @package Requeteur\Model\ModeSaisie
 */
class Comptage_Model_ModeSaisie_Dao
{
	/**
	 * Singleton
	 * @var Comptage_Model_ModeSaisie_Dao
	 */
	private static $_instance;

	/**
	 * Db
	 * @var Db
	 */
	private $_db;

	private static $_tabArgType = array('string' => 'String',
										'callable' => 'Callable',
										'json' => 'Json',
										'serialized' => 'Serialized',
										'sql' => 'Sql');

	private function __construct()
	{
		$this->_db = Db::getInstance('Requeteur');
	}

	/**
	 * @return Comptage_Model_ModeSaisie_Dao
	 */
	public static function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Retourne le mode de saisie avec l'id demandé
	 * @param int $id id du mode de saisie
	 * @return Comptage_Model_ModeSaisie
	 */
	public function get($id)
	{
		return $this->_getBy('id', $id);
	}

	/**
	 * Retourne la liste de tous les modes de saisie
	 * @return array liste de Comptage_Model_ModeSaisie
	 */
	public function getList()
	{
		return $this->_getBy('all');
	}

	/**
	 * Retourne la liste des types d'arguments
	 * @return array
	 */
	public function getListArgType()
	{
		return self::$_tabArgType;
	}

	/**
	 * Effectue la sauvegarde en base (création ou mise à jour)
	 * @param Comptage_Model_ModeSaisie $mode
	 */
	public function save(Comptage_Model_ModeSaisie $mode)
	{
		if ($mode->valid()) {
			if ($mode->getId()) {
				return $this->_update($mode);
			}
			else {
				return $this->_create($mode);
			}
		}
	}

	private function _create(Comptage_Model_ModeSaisie $mode)
	{
		$args = toJson($mode->getListArgs());

		$sql =
'INSERT INTO modesaisie (libelle, description, code, arguments)
VALUES (' .
		$this->_db->escape_string_add_quotes($mode->getLibelle()) . ', ' .
		$this->_db->escape_string_add_quotes($mode->getDescription()) . ', ' .
		$this->_db->escape_string_add_quotes($mode->getCode()) . ', ' .
		$this->_db->escape_string_add_quotes($args) . '
) RETURNING id_modesaisie;';
		$res = $this->_db->query($sql);
		if (!($row = $this->_db->fetchRow($res))) {
			throw new Db_UnexpectedResultException('Impossible de récupérer le mode créé', $this->_db, $sql);
		}
		$mode->setId($row['id_modesaisie']);

		return true;
	}

	private function _update(Comptage_Model_ModeSaisie $mode)
	{
		$args = toJson($mode->getListArgs());

		$sql =
'UPDATE modesaisie SET
	libelle = ' . $this->_db->escape_string_add_quotes($mode->getLibelle()) . ',
	description = ' . $this->_db->escape_string_add_quotes($mode->getDescription()) . ',
	code = ' . $this->_db->escape_string_add_quotes($mode->getCode()) . ',
	arguments = ' . $this->_db->escape_string_add_quotes($args) . '
WHERE id_modesaisie = ' . (int) $mode->getId();
		$res = $this->_db->query($sql);

		return true;
	}

	/**
	 * Instancie un mode de saisie à partir d'un enregistrement
	 * @param array $row un enregistrement
	 * @return Comptage_Model_ModeSaisie
	 */
	private function _getFromResult($row)
	{
		$obj = new Comptage_Model_ModeSaisie();
		$obj->setId($row['id_modesaisie']);
		$obj->setCode($row['code']);
		$obj->setLibelle($row['libelle']);
		$obj->setDescription($row['description']);
		$obj->setListArgs((array) json_decode($row['arguments'], true));
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
	 * Retourne une ou plusieurs instance(s) de Comptage_Model_ModeSaisie
	 * Appelée sous la forme dao->getBy{Param}({value})
	 * @param string $param nom du paramètre (critere|id)
	 * @param mixed $value valeur du paramètre
	 * @param array $others liste de paramètres nommés
	 * @throws \BadMethodCallException si param non géré
	 * @return array|Comptage_Model_ModeSaisie
	 */
	private function _getBy($param, $value = null, $others = null)
	{
		switch ($param) {
			case 'all':
				$sql = 'SELECT * FROM modesaisie';
				break;

			case 'critere':
				$sql =
'SELECT ms.* FROM modesaisie ms
WHERE ms.id_modesaisie IN (
	SELECT distinct cm.id_modesaisie FROM critere_modesaisie cm
	INNER JOIN modesaisie ms on ms.id_modesaisie = cm.id_modesaisie
	INNER JOIN critere ct ON ct.id_critere = cm.id_critere
	WHERE true';
				if ($value instanceof Comptage_Model_Critere) {
					$sql .= ' AND ct.id_critere = ' . (int) $value->getId();
				}
				elseif (is_numeric($value)) {
					$sql .= ' AND ct.id_critere = ' . (int) $value;
				}
				else {
					$sql .= ' AND ct.code = ' . $this->_db->escape_string_add_quotes($value);
				}
				$sql .= '
)
ORDER BY ms.code';
			break;

			case 'id':
				$sql = 'SELECT * FROM modesaisie WHERE true';
				if (is_numeric($value)) {
					$sql .= ' AND id_modesaisie = ' . (int) $value;
				}
				else {
					$sql .= ' AND code = ' . $this->_db->escape_string_add_quotes($value);
				}
			break;

			default:
				throw new \BadMethodCallException("Paramètre non géré $param");
			break;
		}

		if ($param == 'id') {
			$res = $this->_db->query($sql);
			$row = $this->_db->fetchRow($res);
			if (empty($row)) {
				throw new Db_UnexpectedResultException('Le Mode de Saisie avec l\'id ' . $value . ' n\'existe pas', $this->_db, $sql);
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
	 * @param Comptage_Model_ModeSaisie $mode
	 * @param int|string $critere
	 * @return array
	 */
	public function getModeSaisieArgs(Comptage_Model_ModeSaisie $mode, $critere)
	{
		$list = array();

		$sql =
'SELECT crm.nom, crm.valeur, crm.type
FROM critere_modesaisie crm
	INNER JOIN critere c ON c.id_critere = crm.id_critere
WHERE crm.id_modesaisie = ' . (int) $mode->getId();
		if (is_numeric($critere)) {
			$sql .= '
	AND crm.id_critere = ' . (int) $critere;
		}
		else {
			$sql .= '
	AND c.code = ' . $this->_db->escape_string_add_quotes($critere);
		}
		$res = $this->_db->query($sql);
		while ($row = $this->_db->fetchRow($res)) {
			$list[$row['nom']] = array('nom'	=> $row['nom'],
										'valeur'=> $row['valeur'],
										'type'	=> $row['type']);
		}

		return $list;
	}

}