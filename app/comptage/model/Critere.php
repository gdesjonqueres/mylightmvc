<?php
/**
 * Classe représentant l'entité CRITERE
 *
 * @package Requeteur\Model\Critere
 */
class Comptage_Model_Critere
{
	protected $_id;
	protected $_code;
	protected $_libelle;
	protected $_libelleCourt;
	protected $_champSql;
	protected $_codeSql;

	/**
	 * Type de donnée entier ou chaîne
	 * @var string (string|numeric)
	 */
	protected $_typeDonnee;

	/**
	 * Ordre d'affichage du critère
	 * @var int
	 */
	protected $_ordre;

	/**
	 * TRUE si critère enrichi par scoring
	 * @var bool
	 */
	protected $_isScoring;

	/**
	 * TRUE si critère uniquement en location
	 * @var bool
	 */
	protected $_isLocation;

	/**
	 * TRUE si critère désactivé
	 * @var bool
	 */
	protected $_isDesactive;

	/**
	 * Type de valeur (liste|unique|intervalle|switch)
	 * @var string
	 */
	protected $_typeValeur;

	/**
	 * Type de critère (pro, part, géo...)
	 * @var array
	 */
	protected $_type;

	/**
	 * Liste des types de cible associés au critère
	 * @var array
	 */
	protected $_tabCibleType;

	/**
	 * Liste des arguments du critère indéxés sur le mode de saisie
	 * @var array
	 */
	protected $_tabModeSaisieArgs;

	/**
	 * Liste des modes de saisie associés au critère
	 * @var array
	 */
	protected $_tabModeSaisie;

	public function __construct()
	{
		$this->_isValueRemovable = false;
		$this->_tabModeSaisieArgs = array();
	}

	public function getId()
	{
		return $this->_id;
	}

	public function setId($id)
	{
		$this->_id = $id;
		return $this;
	}

	public function getCode()
	{
		return $this->_code;
	}

	public function setCode($code)
	{
		$this->_code = $code;
		return $this;
	}

	public function getLibelle()
	{
		return $this->_libelle;
	}

	public function setLibelle($libelle)
	{
		$this->_libelle = $libelle;
		return $this;
	}

	public function getLibelleCourt()
	{
		return $this->_libelleCourt;
	}

	public function setLibelleCourt($libelle)
	{
		$this->_libelleCourt = $libelle;
		return $this;
	}

	public function getChampSql()
	{
		return $this->_champSql;
	}

	public function setChampSql($champSql)
	{
		$this->_champSql = $champSql;
		return $this;
	}

	public function getCodeSql()
	{
		return $this->_codeSql;
	}

	public function setCodeSql($codeSql)
	{
		$this->_codeSql = $codeSql;
		return $this;
	}

	/**
	 * Retourne le type de critère (Particulier, Géographique, ...)
	 * @return array avec les clés (id, code, libelle)
	 */
	public function getType()
	{
		return $this->_type;
	}

	public function setType($type)
	{
		$this->_type = $type;
		return $this;
	}

	/**
	 * Retourne le type de valeur (liste, unitaire, intervalle, ...)
	 * @return string
	 */
	public function getTypeValeur()
	{
		return $this->_typeValeur;
	}

	public function setTypeValeur($type)
	{
		$this->_typeValeur = $type;
		return $this;
	}

	/**
	 * Retourne la liste des types de cible associés au critère
	 * @return array
	 */
	public function getListCibleType()
	{
		if (!isset($this->_tabCibleType)) {
			$this->_tabCibleType = Comptage_Model_Cible_Dao::getInstance()->getListTypeByCritere($this);
		}
		return $this->_tabCibleType;
	}

	public function setListCibleType(array $list)
	{
		$this->_tabCibleType = $list;
		return $this;
	}

	/**
	 * Retourne la liste de modes de saisie associés au critère
	 * @return array tableau de Comptage_Model_ModeSaisie
	 */
	public function getListModeSaisie()
	{
		if (!isset($this->_tabModeSaisie)) {
			$this->_tabModeSaisie = Comptage_Model_ModeSaisie_Dao::getInstance()->getByCritere($this);
		}
		return $this->_tabModeSaisie;
	}

	/**
	 * Retourne la liste des arguments associés au critère pour un mode de saisie
	 * @param string|int $modeSaisie id ou code du mode de saisie
	 * @return array indexé sur nom, {nom, valeur, type}
	 */
	public function getModeSaisieArgs($modeSaisie)
	{
		if (!isset($this->_tabModeSaisieArgs[$modeSaisie])) {
			$this->_tabModeSaisieArgs[$modeSaisie] = Comptage_Model_Critere_Dao::getInstance()->
																				getModeSaisieArgs($this, $modeSaisie);
		}
		return $this->_tabModeSaisieArgs[$modeSaisie];
	}

	/**
	 * Met à jour la liste des arguments associés au critère pour un mode de saisie
	 * @param string|int $modeSaisie id ou code du mode de saisie
	 * @param array $args liste des arguments du mode de saisie
	 */
	public function setModeSaisieArgs($modeSaisie, array $args)
	{
		$this->_tabModeSaisieArgs[$modeSaisie] = $args;
		return $this;
	}

	public function setScoring($val)
	{
		$this->_isScoring = (bool) $val;
		return $this;
	}

	/**
	 * Retourne VRAI si les valeurs du critère sont calculées par scoring, sinon FAUX
	 */
	public function isScoring()
	{
		return $this->_isScoring;
	}

	public function setLocation($val)
	{
		$this->_isLocation = (bool) $val;
		return $this;
	}

	/**
	 * Retourne VRAI si les valeurs du critère ne sont disponibles qu'à la location, sinon FAUX
	 */
	public function isLocation()
	{
		return $this->_isLocation;
	}

	public function setDesactive($val)
	{
		$this->_isDesactive = (bool) $val;
		return $this;
	}

	/**
	 * Retourne VRAI si le critère est en sommeil
	 */
	public function isDesactive()
	{
		return $this->_isDesactive;
	}

	public function setTypeDonnee($val)
	{
		$this->_typeDonnee = $val;
		return $this;
	}

	/**
	 * Retourne le type de donnée (chaîne ou numérique)
	 * @return string (string|numeric)
	 */
	public function getTypeDonnee()
	{
		return $this->_typeDonnee;
	}

	public function setOrdre($val)
	{
		$this->_ordre = $val;
		return $this;
	}

	/**
	 * Retourne l'ordre d'affichage du critère
	 */
	public function getOrdre()
	{
		return $this->_ordre;
	}

	/**
	 * Retourne VRAI si un critère est valide pour l'enregistrement
	 * @throws Comptage_Model_Critere_Exception quand le critère n'est pas valide avec le message d'erreur associé
	 */
	public function valid()
	{
		if (empty($this->_code)) {
			throw new Comptage_Model_Critere_Exception(Comptage_Model_Critere_Exception::CHAMPS_OBLIG_CODE);
		}
		if (empty($this->_libelle)) {
			throw new Comptage_Model_Critere_Exception(Comptage_Model_Critere_Exception::CHAMPS_OBLIG_LABEL);
		}
		if (empty($this->_type)) {
			throw new Comptage_Model_Critere_Exception(Comptage_Model_Critere_Exception::CHAMPS_OBLIG_TYPE);
		}
		if (empty($this->_typeValeur)) {
			throw new Comptage_Model_Critere_Exception(Comptage_Model_Critere_Exception::CHAMPS_OBLIG_TYPEVAL);
		}
		if (empty($this->_tabCibleType)) {
			throw new Comptage_Model_Critere_Exception(Comptage_Model_Critere_Exception::CHAMPS_OBLIG_CIBLETYPE);
		}
		return true;
	}

}