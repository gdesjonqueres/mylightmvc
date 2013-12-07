<?php
/**
 * Classe pour gérer l'entité EXTRACTION en BDD
 * Motifs de conception utilisés: DAO, SINGLETON
 *
 * @package Requeteur\Model\Extraction
 */
class Comptage_Model_Extraction_Dao
{
	/**
	 * Singleton
	 * @var Comptage_Model_Extraction_Dao
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
	 * @return Comptage_Model_Extraction_Dao
	 */
	public static function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Retourne l'extraction avec l'id demandé
	 * @param int $id id extraction
	 * @return Comptage_Model_Extraction
	 */
	public function get($id)
	{
		return $this->_getBy('id', $id);
	}

	public function save(Comptage_Model_Extraction $ext)
	{
		if ($ext->valid()) {
			if ($ext->getId()) {
				$this->_update($ext);
			}
			else {
				$this->_create($ext);
			}
			$this->_saveChamps($ext, $ext->getChamps());
		}

		return true;
	}

	private function _create(Comptage_Model_Extraction $ext)
	{
		$fichier = $ext->getFichier();
		$options = toJson($fichier->options);

		$sql =
'INSERT INTO cpg_extraction (id_operation, id_contact, envoi, quantite, commentaire, fichiertype, fichieroptions)
VALUES (' .
		(int) $ext->getOperationId() . ', ' .
		(int) $ext->getContactId() . ', ' .
		$this->_db->escape_string_add_quotes($ext->getEnvoi()) . ', ' .
		$this->_db->escape_string_add_quotes($ext->getQuantite()) . ', ' .
		$this->_db->escape_string_add_quotes($ext->getCommentaire()) . ', ' .
		$this->_db->escape_string_add_quotes($fichier->type) . ', ' .
		$this->_db->escape_string_add_quotes($options) . '
) RETURNING id_extraction;';
		$res = $this->_db->query($sql);
		if (!($row = $this->_db->fetchRow($res))) {
			throw new Db_UnexpectedResultException('Impossible de récupérer l\'extraction créée', $this->_db, $sql);
		}
		$ext->setId($row['id_extraction']);

		return true;
	}

	private function _update(Comptage_Model_Extraction $ext)
	{
		$fichier = $ext->getFichier();
		$options = toJson($fichier->options);

		$sql =
'UPDATE cpg_extraction SET
	id_contact			= ' . (int) $ext->getContactId() . ',
	envoi				= ' . $this->_db->escape_string_add_quotes($ext->getEnvoi()) . ',
	quantite			= ' . $this->_db->escape_string_add_quotes($ext->getQuantite()) . ',
	commentaire			= ' . $this->_db->escape_string_add_quotes($ext->getCommentaire()) . ',
	fichiertype			= ' . $this->_db->escape_string_add_quotes($fichier->type) . ',
	fichieroptions		= ' . $this->_db->escape_string_add_quotes($options) . ',
	fichiernom			= ' . $this->_db->escape_string_add_quotes($fichier->nom) . '
WHERE id_extraction = ' . (int) $ext->getId();
		$res = $this->_db->query($sql);

		return true;
	}

	/**
	 * Instancie une extraction à partir d'un enregistrement
	 * @param array $row un enregistrement
	 * @return Comptage_Model_Extraction
	 */
	private function _getFromResult($row)
	{
		$obj = new Comptage_Model_Extraction();
		$obj->setId($row['id_extraction']);
		$obj->setContactId($row['id_contact']);
		$obj->setCommentaire($row['commentaire']);
		$obj->setEnvoi($row['envoi']);
		$obj->setOperationId($row['id_operation']);
		$obj->setQuantite($row['quantite']);

		$obj->getFichier()->type = $row['fichiertype'];
		$obj->getFichier()->nom = $row['fichiernom'];
		$obj->getFichier()->options = json_decode($row['fichieroptions'], true);

		/*$fichier = new Comptage_Model_Extraction_Fichier();
		$fichier->type = $row['fichiertype'];
		$fichier->nom = $row['fichiernom'];
		$fichier->options = json_decode($row['fichieroptions'], true);
		$obj->setFichier($fichier);*/

		$obj->setChamps($this->_getChamps($obj));

		return $obj;
	}

	private function _getChamps(Comptage_Model_Extraction $ext)
	{
		$list = array();

		$sql = 'SELECT * FROM cpg_extraction_champ WHERE id_extraction = ' . (int) $ext->getId() . ' ORDER BY ordre';
		$res = $this->_db->query($sql);
		while ($row = $this->_db->fetchRow($res)) {
			$champ = new Comptage_Model_Extraction_Champ();
			$champ->champId = $row['id_champ'];
			//$champ->extractionId = $extractionId;
			$champ->libelle = $row['libelle'];
			$champ->ordre = $row['ordre'];
			$list[] = $champ;
		}

		return $list;
	}

	private function _saveChamps(Comptage_Model_Extraction $ext, $tabChamps)
	{
		$sql = 'DELETE FROM cpg_extraction_champ WHERE id_extraction = ' . (int) $ext->getId();
		$this->_db->query($sql);

		if (!empty($tabChamps)) {
			$sql = 'INSERT INTO cpg_extraction_champ (id_extraction, id_champ, libelle, ordre) VALUES';
			/* @var $champ Comptage_Model_Extraction_Champ */
			foreach ($tabChamps as $champ) {
				$sql .=  "\n" . '(' . (int) $ext->getId() . ', ' . (int) $champ->champId . ', ' .
					$this->_db->escape_string_add_quotes($champ->libelle) . ', ' . (int) $champ->ordre . '),';
			}
			$sql = substr($sql, 0, -1) . ';';
			$this->_db->query($sql);
		}

		return true;
	}

	public function getListChampsSql(Comptage_Model_Extraction $ext)
	{
		$list = array();

		$sql =
'SELECT upper(c.champ_sql) AS champ_sql, ec.libelle
FROM champ c
	INNER JOIN cpg_extraction_champ ec ON ec.id_champ = c.id_champ
WHERE ec.id_extraction = ' . (int) $ext->getId() . '
ORDER BY ec.ordre';
		$res = $this->_db->query($sql);
		while ($row = $this->_db->fetchRow($res)) {
			$list[$row['champ_sql']] = $row['libelle'];
		}

		return $list;
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
	 * Retourne une ou plusieurs instance(s) de Comptage_Model_Extraction
	 * Appelée sous la forme dao->getBy{Param}({value})
	 * @param string $param nom du paramètre (operation|id)
	 * @param mixed $value valeur du paramètre
	 * @param array $others liste de paramètres nommés
	 * @throws \BadMethodCallException si param non géré
	 * @return array|Comptage_Model_Extraction
	 */
	private function _getBy($param, $value = null, $others = null)
	{
		$sql = 'SELECT * FROM cpg_extraction';

		$join  = array();
		$where = array();
		$order = array();
		switch ($param) {
			case 'id':
				$where[] = 'cpg_extraction.id_extraction = ' . (int) $value;
				break;

			case 'operation':
				$where[] = 'cpg_extraction.id_operation = ' . (int) $value;
				break;

			default:
				throw new \BadMethodCallException("Paramètre non géré $param");
			break;
		}

		//if (isset($others) && isset($others['order'])) {
		//	switch ($others['order']) {
		//	}
		//}

		$order[] = 'cpg_extraction.id_extraction';
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
				throw new Db_UnexpectedResultException('L\'extraction avec l\'id ' . $value . ' n\'existe pas', $this->_db, $sql);
			}
			return $this->_getFromResult($row);
		}
		else {
			//$list = new ArrayObject();//array();
			$list = array();
			$res = $this->_db->query($sql);
			while ($row = $this->_db->fetchRow($res)) {
				$list[$row['id_extraction']] = $this->_getFromResult($row);
			}
			return $list;
		}
	}

}