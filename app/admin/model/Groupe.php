<?php
/**
 * Gestion des groupes de sociétés
 *
 * @package Requeteur\Model\Societe
 */
class Admin_Model_Groupe extends Model
{
	protected $id;
	protected $libelle;

	public function __construct($id = null)
	{
		if (isset($id)) {
			$this->setId($id);
			$this->load();
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = (int) $id;
		return $this;
	}

	public function getLibelle()
	{
		return $this->libelle;
	}

	public function setLibelle($libelle)
	{
		$this->libelle = $libelle;
		return $this;
	}

	public function register($libelle)
	{
		try {
			$db = Db::getInstance('Requeteur', $this);

			$query = 'INSERT INTO groupe (libelle) VALUES (' .
			$db->escape_string_add_quotes($libelle) . ') RETURNING id_groupe;';
			$res = $db->query($query);
			if (!($row = $db->fetchRow($res))) {
				throw new Db_UnexpectedResultException('Impossible de récupérer le groupe créé', $db, $query);
			}
			$this->setId($row['id_groupe']);
			$this->load();
		}
		catch (Db_Exception $e) {
			throw $e;
		}

		return true;
	}

	public function save()
	{
		try {
			$db = Db::getInstance('Requeteur', $this);

			$query = 'UPDATE groupe SET
						libelle = ' . $db->escape_string_add_quotes($this->getLibelle()) . '
					WHERE id_groupe = ' . (int) $this->getId();
			$res = $db->query($query);
			$row = $db->fetchRow($res);
		}
		catch (Db_Exception $e) {
			throw $e;
		}

		return true;
	}

	public function load()
	{
		try {
			$db = Db::getInstance('Requeteur', $this);

			$query = 'SELECT id_groupe, libelle FROM groupe WHERE id_groupe = ' . (int) $this->getId();
			$res = $db->query($query);
			if (!($row = $db->fetchRow($res))) {
				throw new Db_UnexpectedResultException('Le groupe avec l\'id ' . $this->getId() . 'n\'existe pas', $db, $query);
			}
			$this->setLibelle($row['libelle']);
		}
		catch (Db_Exception $e) {
			throw $e;
		}

		return true;
	}

	/**
	 * Retourne la liste des groupes
	 * @return array
	 */
	public static function getList()
	{
		$list = array();

		try {
			$db = Db::getInstance('Requeteur');

			$query = 'SELECT id_groupe FROM groupe';
			$res = $db->query($query);
			while ($row = $db->fetchRow($res)) {
				$list[] = new self($row['id_groupe']);
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return $list;
	}

}