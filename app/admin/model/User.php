<?php
/**
 * Classe servant à la gestion des utilisateurs
 *
 * @package Requeteur\Model\User
 */
class Admin_Model_User extends Model  implements Admin_Model_Acces_Interface
{
	protected $idUtilisateur; /*!< id utilisateur issue de la table */
	protected $email; /*!< email servant de login */
	protected $civilite;
	protected $prenom;
	protected $nom;
	protected $adresse1;
	protected $adresse2;
	protected $debugMode; /*Permet de definir si la navigation est en mode debug*/
	protected $droits;
	protected $cp;
	protected $ville;
	protected $tel;

	/**
	 * Type de l'utilisateur
	 * @var Admin_Model_UserType
	 */
	protected $type;

	/**
	 * Société de l'utilisateur
	 * @var Admin_Model_Societe
	 */
	protected $societe;

	protected $idGroupe;

	/**
	 * Groupe de l'utilisateur
	 * @var Admin_Model_Groupe
	 */
	protected $groupe;


	protected $userMsg;

	/**
	 * Crée un utilisateur vide ou à partir d'une référence client
	 * si ce constructeur est utilisé avec un paramètre, il simule un compte client pour les besoins des admins et les chargés de clientèles
	 * sinon il est appelé sans paramètre pour un utilisateur quelconque
	 *
	 * @param int|null $id id de l'utilisateur à charger
	 */
	public function __construct($id = null)
	{
		if ($id != null) {
			$this->idUtilisateur = $id;
			$this->load();
		}
		else {
			$this->idUtilisateur = -1;
			$this->civilite 	 = '';
			//$this->droits 		 = new Admin_Model_Acces($this->idUtilisateur, 'utilisateur');
			$this->prenom 		 = '';
			$this->nom 			 = '';
			$this->type 		 = null;
			$this->societe 		 = null;
			$this->error 		 = '';
			$this->idGroupe		 = null;
			$this->groupe		 = null;
		}

		$this->debugMode	= false;
		$this->dateCreation = date('Y-m-d H:i:s');//date('d/m/Y H:i:s');
		$this->userMsg 		= '';
	}

	/**
	 * Enregistre un utilisateur (mise à jour)
	 *
	 * @throws Admin_Model_User_Exception USER_INCONNU
	 * @return bool
	 */
	public function save()
	{
		if ($this->idUtilisateur == -1) {
			throw new Admin_Model_User_Exception(Admin_Model_User_Exception::USER_INCONNU, array($this->idUtilisateur));
		}
		else {
			try {
				$pg = Db::getInstance('Requeteur', $this);

				$sql = 'UPDATE utilisateur
							SET
								id_societe = ' . (int) $this->societe->getId() . ',
								adresse1   = ' . $pg->escape_string_add_quotes($this->adresse1) . ',
								adresse2   = ' . $pg->escape_string_add_quotes($this->adresse2) . ',
								cp		   = ' . $pg->escape_string_add_quotes($this->cp) . ',
								ville      = ' . $pg->escape_string_add_quotes($this->ville) . ',
								id_type    = ' . (int) $this->type->getId() . ',
								tel        = ' . $pg->escape_string_add_quotes($this->tel) . ',
								email      = ' . $pg->escape_string_add_quotes($this->email) . ',
								id_groupe  = ' . $pg->intOrNull($this->getIdGroupe()) . ',
								date_modification = localtimestamp(0)
							WHERE id_utilisateur = ' . (int) $this->idUtilisateur . ';';
				$pg->query($sql);
			}
			catch (Db_Exception $e) {
				//Misc::handleException($e);
				throw $e;
			}
		}

		return true;
	}

	/**
	 * Charge un utilisateur
	 *
	 * @throws Admin_Model_User_Exception USER_INCONNU
	 * @return bool
	 */
	public function load()
	{
		try {
			$pg = Db::getInstance('Requeteur', $this);

			$sql = 'SELECT * FROM utilisateur WHERE id_utilisateur = ' . (int) $this->idUtilisateur;
			$res = $pg->query($sql);
			if ($row = $pg->fetchRow($res)) {
				$this->civilite = $row['civilite'];
				$this->prenom   = $row['prenom'];
				$this->nom      = $row['nom'];
				$this->adresse1 = $row['adresse1'];
				$this->adresse2 = $row['adresse2'];
				$this->cp       = $row['cp'];
				$this->ville    = $row['ville'];
				$this->tel      = $row['tel'];
				$this->email    = $row['email'];
				$this->societe  = new Admin_Model_Societe($row['id_societe']);
				$this->droits   = new Admin_Model_Acces($this->idUtilisateur, 'utilisateur');
				$this->email    = $row['email'];
				$this->type     = new Admin_Model_UserType($row['id_type']);

				$this->setIdGroupe($row['id_groupe']);
			}
			else {
				throw new Admin_Model_User_Exception(Admin_Model_User_Exception::USER_INCONNU, array($this->idUtilisateur));
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return true;
	}

	/**
	 * Log l'utilisateur et récupère son compte et sa dernière campagne
	 *
	 * @param string $email		email
	 * @param string $pwd		mot de passe
	 * @throws Admin_Model_User_Exception COMPTE_BLOQUE
	 * @throws Admin_Model_User_Exception COMPTE_DESACTIVE
	 * @throws Admin_Model_User_Exception ERREUR_LOGIN
	 * @return bool
	 */
	public function login($email, $pwd)
	{
		// Verification du nombre d'essai
		if (empty($_SESSION['date_essai_pwd']) || $_SESSION['date_essai_pwd'] < (time() - (600))) {
			$_SESSION['date_essai_pwd'] = 1;
			$_SESSION['nb_essai_pwd']   = 0;
		}
		if ($_SESSION['nb_essai_pwd'] >= 5) {
			throw new Admin_Model_User_Exception(Admin_Model_User_Exception::COMPTE_BLOQUE);
		}

		$password = Misc::encodePassword($pwd);

		try {
			$pg = Db::getInstance('Requeteur', $this);

			$sql = 'SELECT id_utilisateur, civilite, prenom, nom, id_type, id_societe, actif, id_groupe
					FROM utilisateur
					WHERE email = ' . $pg->escape_string_add_quotes($email) . ' AND
						password = ' . $pg->escape_string_add_quotes($password);

			$res = $pg->query($sql);

			if ($row = $pg->fetchRow($res)) {
				if ($row['actif']) {
					$this->idUtilisateur = $row['id_utilisateur'];
					$this->civilite      = $row['civilite'];
					$this->prenom        = $row['prenom'];
					$this->nom           = $row['nom'];
					$this->type          = new Admin_Model_UserType($row['id_type']);
					$this->droits        = new Admin_Model_Acces($this->idUtilisateur, 'utilisateur');
					$this->societe       = new Admin_Model_Societe($row['id_societe']);
					$this->email         = $email;

					$this->setIdGroupe($row['id_groupe']);
				}
				else {
					throw new Admin_Model_User_Exception(Admin_Model_User_Exception::COMPTE_DESACTIVE);
				}
			}
			else {
				$this->userMsg			    = 'login ou mot de passe incorrect';
				$_SESSION['nb_essai_pwd']   = $_SESSION['nb_essai_pwd'] + 1;
				$_SESSION['date_essai_pwd'] = time();
				throw new Admin_Model_User_Exception(Admin_Model_User_Exception::ERREUR_LOGIN);
			}

			// Mise a jour de la date de derniere connexion
			$sql = 'UPDATE utilisateur
						SET
							last_login_date = localtimestamp(0)
						WHERE id_utilisateur = ' . (int) $row['id_utilisateur'];
			$pg->query($sql);

		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		$this->loadAcces();
		$this->userMsg = 'Utilisateur connecté';

		return true;
	}

	/**
	 *  Enregistre un nouvel utilisateur et lui associe un nouveau compte
	 *
	 * @param int 	$id_societe id société
	 * @param string $civ 		la civilité
	 * @param string $prenom 	le prénom
	 * @param string $nom 		le nom
	 * @param string $adresse1 	ligne3
	 * @param string $adresse2 	ligne4
	 * @param string $cp 		CP
	 * @param string $ville 		ville
	 * @param string $tel 		le téléphone
	 * @param string $email 		email qui servira de login
	 * @param string $pwd 		mot de passe en clair
	 * @param int 	$id_type 	type d'utilisateur
	 * @param int	$id_groupe	Id groupe de sociétés
	 * @throws Admin_Model_User_Exception CHAMPS_OBLIG
	 * @throws Admin_Model_User_Exception NON_VALIDE_TEL
	 * @throws Admin_Model_User_Exception NON_VALIDE_CP
	 * @throws Admin_Model_User_Exception NON_VALIDE_MDP
	 * @throws Admin_Model_User_Exception NON_VALIDE_MAIL
	 * @throws Admin_Model_User_Exception MAIL_EXISTE_DEJA
	 * @return bool
	 */
	public function register($id_societe, $civ, $prenom, $nom, $adresse1, $adresse2,
							$cp, $ville, $tel, $email, $pwd, $id_type, $id_groupe
	){
		// Vérification champs obligatoires
		if (empty($email) || empty($id_societe) || empty($prenom) || empty($nom) || empty($adresse1)||
		empty($cp) || empty($ville) || empty($pwd) || empty($tel)) {
			throw new Admin_Model_User_Exception(Admin_Model_User_Exception::CHAMPS_OBLIG);
		}
		if (!Misc::validTel($tel)) {
			throw new Admin_Model_User_Exception(Admin_Model_User_Exception::NON_VALIDE_TEL);
		}
		if (!Misc::validCp($cp)) {
			throw new Admin_Model_User_Exception(Admin_Model_User_Exception::NON_VALIDE_CP);
		}
		// Vérification du mot de passe
		if (!Misc::validPass($pwd)) {
			throw new Admin_Model_User_Exception(Admin_Model_User_Exception::NON_VALIDE_MDP);
		}
		// Vérification de la forme de l'email
		if (!Misc::validEmail($email)) {
			throw new Admin_Model_User_Exception(Admin_Model_User_Exception::NON_VALIDE_MAIL);
		}

		// encode le mot de passe
		$password = Misc::encodePassword($pwd);

		// Vérifications et insertion de l'utilisateur en base
		try {
			$pg = Db::getInstance('Requeteur', $this);

			// Vérification de l'adresse email unique
			$sql = 'SELECT count(*) AS cpt FROM utilisateur WHERE email = ' . $pg->escape_string_add_quotes($email);
			$res = $pg->query($sql);
			$row = $pg->fetchRow($res);
			if ($row['cpt'] > 0) {
				throw new Admin_Model_User_Exception(Admin_Model_User_Exception::MAIL_EXISTE_DEJA);
			}

			// Recuperation du type
			$sql = 'SELECT id_assoc
						FROM societe_usertype
						WHERE id_societe = ' . (int) $id_societe . ' AND
							  id_usertype = ' . (int) $id_type;
			$res = $pg->query($sql);
			if ($row = $pg->fetchRow($res)) {
				$type = $row['id_assoc'];
			}
			else {
				throw new Db_UnexpectedResultException('Impossible de récupérer le type utilisateur', $pg, $sql);
			}

			// insertion en base
			$sql = 'INSERT INTO utilisateur (
							civilite, prenom, nom, adresse1, adresse2, cp, ville, tel,
							email, password, actif, id_type, id_societe, date_creation, id_groupe
						)
						VALUES (' .
								$pg->escape_string_add_quotes($civ) . ', ' .
								$pg->escape_string_add_quotes($prenom) . ', ' .
								$pg->escape_string_add_quotes($nom) . ', ' .
								$pg->escape_string_add_quotes($adresse1) . ',' .
								$pg->escape_string_add_quotes($adresse2) . ', ' .
								$pg->escape_string_add_quotes($cp) . ', ' .
								$pg->escape_string_add_quotes($ville) . ', ' .
								$pg->escape_string_add_quotes($tel) . ', ' .
								$pg->escape_string_add_quotes($email) . ', ' .
								$pg->escape_string_add_quotes($password) . ',
								1, ' .
								(int) $type . ', ' .
								(int) $id_societe . ', ' .
								"'" . $this->dateCreation . "'" . ', ' .
								$pg->intOrNull($id_groupe) . '
						)
						RETURNING id_utilisateur;';
			$res = $pg->query($sql);
			if ($row = $pg->fetchRow($res)) {
				$this->idUtilisateur = $row['id_utilisateur'];
				$this->load($row['id_utilisateur']);
			}
			else {
				throw new Db_UnexpectedResultException('Impossible de récupérer l\'utilisateur créé', $pg, $sql);
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return true;
	}


	/*********************** ACCES ******************/

	public function loadAcces()
	{
		return $this->droits->loadAcces();
	}

	public function saveAcces()
	{
		return $this->droits->saveAcces();
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

	public function getDroitsAcces()
	{
		return $this->droits->getDroitsAcces();
	}

	public function canEditCampaign($c)
	{
		if ($c instanceof Comptage_Model_Campagne) {
			if ($c->getStatut() == 'STA03' && $this->isAdmin()) {
				return true;
			}
			else {
				return $c->isEditable();
			}
		}
		elseif (is_string($c) && strpos($c, 'STA') !== false) {
			if ($c == 'STA03' && $this->isAdmin()) {
				return true;
			}
			else {
				return Comptage_Model_Campagne::isEditableState($c);
			}
		}
		return false;
	}

	public function canEditOperation($o)
	{
		if ($o instanceof Comptage_Model_Operation) {
			if ($o->getStatut() == 'STA03' && $this->isAdmin()) {
				return true;
			}
			else {
				return $o->isEditable();
			}
		}
		elseif (is_string($o) && strpos($o, 'STA') !== false) {
			if ($o == 'STA03' && $this->isAdmin()) {
				return true;
			}
			else {
				return Comptage_Model_Campagne::isEditableState($o);
			}
		}
		return false;
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
		return $this->droits->addDE($id, $libelle);
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

	/*********************** ADMINISTRATION ******************/

	public function getUserMsg()
	{
		$msg = $this->userMsg;
		$this->userMsg = '';
		return $msg;
	}

	/**
	 * Retourne la liste des sociétés suivant les droits de l'utilisateur
	 * @return array|bool
	 */
	public function getListSociete()
	{
		if ($this->isAllowedAcces('gestion_societes')) {
			$listSociete = array();

			try {
				$pg = Db::getInstance('Requeteur', $this);

				$query = 'SELECT id_societe FROM Societe';
				$res = $pg->query($query);
				while ($row = $pg->fetchRow($res)) {
					$listSociete[] = new Admin_Model_Societe($row['id_societe']);
				}
			}
			catch (Db_Exception $e) {
				//Misc::handleException($e);
				throw $e;
			}

			return $listSociete;
		}

		return false;
	}

	/**
	 * Retourne la liste des utilisateurs suivant les droits de l'utilisateur
	 * @return array|bool
	 */
	public function getListUtilisateurs()
	{
		if ($this->isAllowedAcces('gestion_utilisateurs')) {
			$listUtilisateur = array();

			try {
				$pg = Db::getInstance('Requeteur', $this);

				$query = 'SELECT id_utilisateur FROM utilisateur';
				if (!$this->isAllowedAcces('gestion_societes')) {
					$query .= ' WHERE id_societe = ' . (int) $this->getSociete()->getId();
				}
				$res = $pg->query($query);
				while ($row = $pg->fetchRow($res)) {
					$listUtilisateur[] = new Admin_Model_User($row['id_utilisateur']);
				}
			}
			catch (Db_Exception $e) {
				//Misc::handleException($e);
				throw $e;
			}

			return $listUtilisateur;
		}

		return false;
	}

	/**
	 * Retourne l'objet UserType associé au User
	 * @return Admin_Model_UserType
	 */
	public function getUserType()
	{
		try {
			$pg = Db::getInstance('Requeteur', $this);

			$query = 'SELECT id_assoc
						FROM societe_usertype
						WHERE id_societe = ' . (int) $this->getSociete()->getId() . ' AND
								id_usertype = ' . (int) $this->getType()->getId() . ';';
			$res = $pg->query($query);
			if ($row = $pg->fetchRow($res)) {
				return new Admin_Model_UserType($row['id_assoc']);
			}
			else {
				throw new Db_UnexpectedResultException('Aucun type d\'utilisateur pour le user ' . $this->idUtilisateur, $pg, $query);
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}
	}

	public function changeDebugMode()
	{
		if ($this->debugMode) {
			$this->debugMode = false;
		}
		else {
			$this->debugMode = true;
		}
		return $this;
	}

	public function isAdmin()
	{
		return ($this->getType()->getId() == Admin_Model_UserType::ID_ADMIN);
	}

	public function isDebugMode()
	{
		return $this->debugMode;
	}

	/************************** GETTERS ET SETTERS ******************************/

	public function setUserMsg($val)
	{
		$this->userMsg = $val;
		return $this;
	}

	public function getError()
	{
		return $this->error;
	}

	public function setError($val)
	{
		$this->error = $val;
		return $this;
	}

	public function getSociete()
	{
		return $this->societe;
	}

	public function setSociete($idSoc)
	{
		$this->societe = new Admin_Model_Societe($idSoc);
		return $this;
	}

	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return Admin_Model_Acces
	 */
	public function getDroits()
	{
		return $this->droits;
	}

	public function getNom()
	{
		return $this->nom;
	}

	public function getCivilite()
	{
		return $this->civilite;
	}

	public function getId()
	{
		return $this->idUtilisateur;
	}

	public function getPrenom()
	{
		return $this->prenom;
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

	public function getEmail()
	{
		return $this->email;
	}

	public function setType($idType)
	{
		$this->type = new Admin_Model_UserType($idType);
		return $this;
	}

	public function setDroits($droits)
	{
		$this->droits = $droits ;
		return $this;
	}

	public function setNom($nom)
	{
		$this->nom = $nom ;
		return $this;
	}

	public function setCivilite($civilite)
	{
		$this->civilite = $civilite ;
		return $this;
	}

	public function setId($idUtilisateur)
	{
		$this->idUtilisateur = $idUtilisateur;
		return $this;
	}

	public function setPrenom($prenom)
	{
		$this->prenom = $prenom ;
		return $this;
	}

	public function setAdresse1($adresse1)
	{
		$this->adresse1 = $adresse1 ;
		return $this;
	}

	public function setAdresse2($adresse2)
	{
		$this->adresse2 = $adresse2 ;
		return $this;
	}

	public function setCp($cp)
	{
		$this->cp = $cp ;
		return $this;
	}

	public function setVille($ville)
	{
		$this->ville = $ville ;
		return $this;
	}

	public function setTel($tel)
	{
		$this->tel = $tel ;
		return $this;
	}

	public function setEmail($email)
	{
		$this->email = $email ;
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


}
?>