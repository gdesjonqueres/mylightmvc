<?php
/**
 * Classe représentant les différentes actions de la societe
 *
 * @package Requeteur\Model\Societe
 */
class Admin_Model_Societe extends Model  implements Admin_Model_Acces_Interface
{
	protected $idSociete;
	protected $raisonSociale;
	protected $adresse1;
	protected $adresse2;
	protected $cp;
	protected $droits;
	protected $ville;
	protected $tel;
	protected $idContact;
	protected $userMsg;

	protected $idGroupe;

	/**
	 * Groupe de la société
	 * @var Admin_Model_Groupe
	 */
	protected $groupe;

	/**
	 * Constructeur
	 *
	 * @param int|null $id id société à charger
	 */
	public function __construct($id = null)
	{
		if (!empty($id)) {
			$this->idSociete = $id;
			$this->load();
		}
		else {
			$this->idSociete		= -1;
			$this->raisonSociale	= '';
			$this->adresse1			= '';
			$this->adresse2			= '';
			$this->cp				= '';
			$this->ville			= '';
			$this->tel				= '';
			$this->error			= '';
			$this->idGroupe			= null;
			$this->dateCreation		= date('Y-m-d H:i:s');//date('d/m/Y H:i:s');
		}

		//$this->userMsg = '';
	}

	/**
	 * Sauvegarde la société en base. A utiliser apres modification de l'objet.
	 *
	 * @throws Admin_Model_Societe_Exception SOCIETE_INCONNUE
	 * @return bool
	 */
	public function save()
	{
		if ($this->idSociete == -1) {
			throw new Admin_Model_Societe_Exception(Admin_Model_Societe_Exception::SOCIETE_INCONNUE, array($this->idSociete));
		}

		try {
			$pg = Db::getInstance('Requeteur', $this);

			$sql = 'UPDATE societe SET
							raison_sociale    = ' . $pg->escape_string_add_quotes($this->raisonSociale) . ',
							adresse1          = ' . $pg->escape_string_add_quotes($this->adresse1) . ',
							adresse2          = ' . $pg->escape_string_add_quotes($this->adresse2) . ',
							ville             = ' . $pg->escape_string_add_quotes($this->ville) . ',
							tel               = ' . $pg->escape_string_add_quotes($this->tel) . ',
							id_contact        = ' . (int) $this->idContact . ',
							id_groupe		  = ' . $pg->intOrNull($this->getIdGroupe()) . ',
							date_modification = localtimestamp(0)
						WHERE id_societe      = ' . (int) $this->idSociete . ';';
			$pg->query($sql);
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return true;
	}

	/**
	 * Créé et enregistre une nouvelle société dans la base
	 *
	 * @param string $raisonSociale
	 * @param string $adresse1
	 * @param string $adresse2
	 * @param string $cp
	 * @param string $ville
	 * @param string $tel
	 * @param int 	 $idContact
	 * @param int 	 $idGroupe
	 * @throws Admin_Model_Societe_Exception CHAMPS_OBLIG
	 * @throws Admin_Model_Societe_Exception NON_VALIDE_TEL
	 * @throws Admin_Model_Societe_Exception NON_VALIDE_CP
	 * @return bool
	 */
	public function register($raisonSociale, $adresse1, $adresse2, $cp, $ville, $tel, $idContact, $idGroupe)
	{
		if (empty($raisonSociale) || empty($adresse1) || empty($tel) || empty($idContact)) {
			throw new Admin_Model_Societe_Exception(Admin_Model_Societe_Exception::CHAMPS_OBLIG);
		}
		if (!Misc::validTel($tel)) {
			throw new Admin_Model_Societe_Exception(Admin_Model_Societe_Exception::NON_VALIDE_TEL);
		}
		if (!Misc::validCp($cp)) {
			throw new Admin_Model_Societe_Exception(Admin_Model_Societe_Exception::NON_VALIDE_CP);
		}

		try {
			$pg = Db::getInstance('Requeteur', $this);

			$sql = 'INSERT INTO societe (
							raison_sociale, adresse1, adresse2, cp, ville, tel, id_contact, date_creation, id_groupe
						)
						VALUES (' .
								$pg->escape_string_add_quotes($raisonSociale) . ', ' .
								$pg->escape_string_add_quotes($adresse1) . ', ' .
								$pg->escape_string_add_quotes($adresse2) . ', ' .
								$pg->escape_string_add_quotes($cp) . ', ' .
								$pg->escape_string_add_quotes($ville) . ', ' .
								$pg->escape_string_add_quotes($tel) . ', ' .
								(int) $idContact . ', ' .
								"'" . $this->dateCreation . "'" . ', ' .
								$pg->intOrNull($idGroupe) . '
						)
						RETURNING id_societe;';
			$res = $pg->query($sql);
			$tabId = $pg->fetchRow($res);

			$this->idSociete	 = $tabId[0];
			$this->raisonSociale = $raisonSociale;
			$this->adresse1 	 = $adresse1;
			$this->adresse2      = $adresse2;
			$this->cp 			 = $cp;
			$this->ville 	     = $ville;
			$this->tel 		     = $tel;
			$this->error 		 = '';
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		$this->error = '';

		return true;
	}

	/**
	 * Recupere les informations de la société dont l'identifiant est fournit en parametre
	 *
	 * @throws Admin_Model_Societe_Exception SOCIETE_INCONNUE
	 * @return bool
	 */
	public function load()
	{
		try {
			$pg = Db::getInstance('Requeteur', $this);

			$sql = 'SELECT * FROM societe WHERE id_societe = ' . (int) $this->idSociete;
			$res = $pg->query($sql);
			if ($row = $pg->fetchRow($res)) {
				$this->raisonSociale = $row['raison_sociale'];
				$this->adresse1 	 = $row['adresse1'];
				$this->adresse2 	 = $row['adresse2'];
				$this->cp 			 = $row['cp'];
				$this->ville 	     = $row['ville'];
				$this->tel 			 = $row['tel'];
				$this->idContact 	 = $row['id_contact'];
				$this->dateCreation  = $row['date_creation'];
				$this->droits		 = new Admin_Model_Acces($this->idSociete, 'societe');

				$this->setIdGroupe($row['id_groupe']);
			}
			else {
				throw new Admin_Model_Societe_Exception(Admin_Model_Societe_Exception::SOCIETE_INCONNUE, array($this->idSociete));
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return true;
	}

	/**
	 * Recupere la liste des types d'utilisateurs de la société.
	 *
	 * @return array liste de Admin_Model_UserType indexés sur le id UserType
	 */
	public function getUserTypes()
	{
		$userTypes = array();

		try {
			$pg = Db::getInstance('Requeteur', $this);

			$query = 'SELECT id_assoc FROM societe_usertype WHERE id_societe = ' . (int) $this->idSociete;
			$res = $pg->query($query);
			while ($row =$pg->fetchRow($res)) {
				$ut = new Admin_Model_UserType($row['id_assoc']);
				$userTypes[$ut->getIdType()] = $ut;
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return $userTypes;
	}

	/**
	 * Retourne la liste des utilisateurs liés à la société
	 *
	 * @return array
	 */
	public function getUsers()
	{
		$users = array();

		try {
			$pg = Db::getInstance('Requeteur', $this);

			$sql = 'SELECT id_utilisateur FROM utilisateur WHERE id_societe = ' . (int) $this->idSociete;
			$res = $pg->query($sql);
			while ($row = $pg->fetchRow($res)) {
				$users[] = new Admin_Model_User($row['id_utilisateur']);
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return $users;
	}

	/**
	 * @return Admin_Model_Acces
	 */
	public function getDroits()
	{
		return $this->droits;
	}

	/************************ GESTION DES ACCES *********************************/

	public function loadAcces()
	{
		$this->droits = new Admin_Model_Acces($this->idSociete, 'societe');
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

	public function addAcces($id, $libelle)
	{
		return $this->droits->addAcces($id, $libelle);
	}

	public function removeAcces($libelle)
	{
		return $this->droits->removeAcces($libelle);
	}

	public function isAllowedAcces($acces)
	{
		return $this->droits->isAllowedAcces($acces);
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

	public function addDE($id, $libelle)
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

	/*********************** DROIT CRITERE ******************/

	public function loadCritere()
	{
		return $this->droits->loadCritere();
	}

	public function saveCritere()
	{
		return $this->droits->saveCritere();
	}

	public function addCritere($id, $libelle)
	{
		return $this->droits->addCritere($id, $libelle);
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

	/************************ GETTERS & SETTERS *********************************/

	public function getRaisonSociale()
	{
		return $this->raisonSociale;
	}

	public function getContact()
	{
		return $this->idContact;
	}

	public function getAdresse1()
	{
		return $this->adresse1;
	}

	public function getAdresse2()
	{
		return $this->adresse2;
	}

	public function getCp()
	{
		return $this->cp;
	}

	public function getVille()
	{
		return $this->ville;
	}

	public function getTel()
	{
		return $this->tel;
	}

	public function setRS($val)
	{
		$this->raisonSociale = $val;
		return $this;
	}

	public function setContact($val)
	{
		$this->idContact = $val;
		return $this;
	}

	public function setAdresse1($val)
	{
		$this->adresse1 = $val;
		return $this;
	}

	public function setAdresse2($val)
	{
		$this->adresse2 = $val;
		return $this;
	}

	public function setCp($val)
	{
		$this->cp = $val;
		return $this;
	}

	public function setVille($val)
	{
		$this->ville = $val;
		return $this;
	}

	public function setTel($val)
	{
		$this->tel = $val;
		return $this;
	}
	public function getId()
	{
		return $this->idSociete;
	}

	public function getError()
	{
		return $this->error;
	}

	public function getUserMsg()
	{
		return $this->userMsg;
	}

	public function setError($val)
	{
		$this->error = $val;
		return $this;
	}

	public function setIdGroupe($id = null)
	{
		if (empty($id)) {
			$this->idGroupe = null;
		}
		else {
			$this->idGroupe = (int) $id;
		}
		$this->groupe = null;
		return $this;
	}

	public function getIdGroupe()
	{
		return $this->idGroupe;
	}

	public function getGroupe()
	{
		if ($this->groupe === null && ($id = $this->getIdGroupe())) {
			$this->groupe = new Admin_Model_Groupe($id);
		}
		return $this->groupe;
	}

	public function setGroupe(Admin_Model_Societe $groupe = null)
	{
		if ($groupe === null) {
			$this->groupe = null;
			$this->idGroupe = null;
		}
		else {
			$this->groupe = $groupe;
			$this->idGroupe = $groupe->getId();
		}
		return $this;
	}

	public static function getListByGroupe($groupe)
	{
		$list = array();

		if ($groupe instanceof Admin_Model_Groupe) {
			$id = $groupe->getId();
		}
		else {
			$id = $groupe;
		}

		try {
			$db = Db::getInstance('Requeteur');

			$query = 'SELECT id_societe FROM societe WHERE id_groupe = ' . (int) $id;
			$res = $db->query($query);
			while ($row = $db->fetchRow($res)) {
				$list[] = new self($row['id_societe']);
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return $list;
	}
}
?>