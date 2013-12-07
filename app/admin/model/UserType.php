<?php
/**
 * Gestion des types d'utilisateur
 *
 * @package Requeteur\Model\User
 */
class Admin_Model_UserType implements Admin_Model_Acces_Interface
{
	/**
	 * Id userType pour le profil administrateur
	 * @var integer
	 */
	CONST ID_ADMIN = 1;

	protected $id;
	protected $droits;
	protected $idType;
	protected $societe;
	protected $libelle;

	public function __construct($id = null)
	{
		if ($id) {
			$this->id = $id;
			$this->load();
		}
	}

	public function load()
	{
		try {
			$pg = Db::getInstance('Requeteur', $this);
			$sql =
					'SELECT *
					FROM societe_usertype asut
					INNER JOIN usertype ut
						ON ut.id_usertype = asut.id_usertype
					WHERE asut.id_assoc = ' . (int) $this->id;
			$res = $pg->query($sql);
			if ($row = $pg->fetchRow($res)) {
				$this->idType  = $row['id_usertype'];
				$this->libelle = $row['libelle'];
				$this->societe =  new Admin_Model_Societe($row['id_societe']);
			}
			else {
				throw new Db_UnexpectedResultException('Aucun type utilisateur avec le ref asut ' . $this->id);
			}
		}
		catch (Db_Exception $e) {
			throw $e;
		}

		return $this->loadAcces();
	}

	public function create($idSociete, $idType)
	{
		try {
			$pg = Db::getInstance('Requeteur', $this);
			$sql =
					'INSERT INTO societe_usertype(id_societe, id_usertype)
					VALUES (' . (int) $idSociete . ', ' . (int) $idType . ') returning id_assoc;
					';
			$res = $pg->query($sql);
			if ($row = $pg->fetchRow($res)) {
				$this->id = $row['id_assoc'];
				$this->load();
			}
			else {
				throw new Db_UnexpectedResultException('Impossible de creer le user type ' . $this->id);
			}
		}
		catch (Db_Exception $e) {
			throw $e;
		}

		return true;

	}

	/**
	 * @return Admin_Model_Acces
	 */
	public function getDroits()
	{
		return $this->droits;
	}


	/*************** ACCES *************************************/

	public function loadAcces()
	{
		$this->droits= new Admin_Model_Acces($this->id, 'userType');
		$this->droits->loadAcces();
		return true;
	}

	public function saveAcces()
	{
		return $this->droits->saveAcces();
	}

	public function getDroitsAcces()
	{
		return $this->droits->getDroitsAcces();
	}

	public function addAcces($id,$libelle)
	{
		return $this->droits->addAcces($id,$libelle);
	}

	public function removeAcces($libelle)
	{
		return $this->droits->removeAcces($libelle);
	}

	public function isAllowedAcces($acces)
	{
		return $this->droits->isAllowedAcces($acces);
	}

	/*********************** DROIT CRITERE ******************/

	public function loadCritere()
	{
		return $this->droits->loadCritere();
	}

	public function saveCritere()
	{
		return $this->droits->saveCritere();
	}

	public function addCritere($id,$libelle)
	{
		return $this->droits->addCritere($id,$libelle);
	}

	public function removeCritere($libelle)
	{
		return $this->droits->removeCritere($libelle);
	}

	public function isAllowedCritere($acces)
	{
		return $this->droits->isAllowedCritere($acces);
	}

	public function getDroitsCritere()
	{
		return $this->droits->getDroitsCritere();
	}

	/*********************** DROIT DE ******************/

	public function loadDE()
	{
		return $this->droits->loadDE();
	}

	public function saveDE()
	{
		return $this->droits->saveDE();
	}

	public function addDE($id,$libelle)
	{
		return $this->droits->addDE($id,$libelle);
	}

	public function removeDE($libelle)
	{
		return $this->droits->removeDE($libelle);
	}

	public function isAllowedDE($acces)
	{
		return $this->droits->isAllowedDE($acces);
	}

	public function getDroitsDE()
	{
		return $this->droits->getDroitsDE();
	}

	/*************** GETTERS & SETTERS *************************************/

	public function getId()
	{
		return $this->id;
	}
	public function getIdType()
	{
		return $this->idType;
	}

	public function getSociete()
	{
		return $this->societe;
	}

	public function getLibelle()
	{
		return $this->libelle;
	}

	/******************** Listes ********************************************/

	public static function getList()
	{
		$tab = array();

		$pg = Db::getInstance('Requeteur');
		$query = 'SELECT * FROM usertype;';
		$res = $pg->query($query);
		while($row = $pg->fetchRow($res)) {
			$tab[$row['id_usertype']] = $row['libelle'];
		}

		return $tab;
	}
}
