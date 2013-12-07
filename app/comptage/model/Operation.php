<?php
/**
 * Classe représentant l'entité OPERATION
 *
 * @package Requeteur\Model\Operation
 */
class Comptage_Model_Operation implements \IteratorAggregate, \Countable, \ArrayAccess
{
	CONST TYPE_DEFAULT = 'ACHAT';
	CONST DONNEE_DEFAULT = 'POSTAL';
	CONST STATUT_DEFAULT = 'STA01';

	protected $_id;
	protected $_type;
	protected $_statut;
	protected $_cibletype;
	protected $_donneesType;
	protected $_idContact;

	/**
	 * Résultat du comptage
	 * @var int
	 */
	protected $_comptage;

	/**
	 * Pointeur sur la cible en cours
	 * @var int
	 */
	protected $_currentCibleId;

	/**
	 * Liste de cibles
	 * @var ArrayObject
	 */
	protected $_tabCibles;

	/**
	 * Liste des types de cible
	 * @var array
	 */
	protected static $_tabCibleType;

	/**
	 * Liste des types d'operation
	 * @var array
	 */
	protected static $_tabTypes;

	/**
	 * Liste des types de données
	 * @var array
	 */
	protected static $_tabDonneesTypes;

	/**
	 * Extraction associée à l'opération
	 * @var Comptage_Model_Extraction
	 */
	protected $_extraction;

	/**
	 * Liste des états d'une opération
	 * @var array
	 */
	protected static $_tabStates = array(	'STA01' => array('editable' => true),
											'STA02' => array('editable' => true),);

	public function __construct()
	{
		$this->_tabCibles = new ArrayObject();
		$this->_id = uniqid('tmp');
		$this->_type = self::TYPE_DEFAULT;
		$this->_statut = self::STATUT_DEFAULT;
		$this->_donneesType = self::DONNEE_DEFAULT;
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
	 * Retourne le libellé du type
	 * @return string
	 */
	public function getTypeLibelle()
	{
		if (!isset(self::$_tabTypes)) {
			self::$_tabTypes = Comptage_Model_Operation_Dao::getInstance()->getListType();
		}
		return self::$_tabTypes[$this->_type];
	}

	/**
	 * Retourne le code du statut de l'opération
	 * @return string
	 */
	public function getStatut()
	{
		return $this->_statut;
	}

	public function setStatut($statut)
	{
		$this->_statut = $statut;
		return $this;
	}

	/**
	 * Retourne le libellé du statut de l'opération
	 * @return string
	 */
	public function getStatutLibelle()
	{
		return Comptage_Model_Common::getStatutLibelle($this->_statut);
	}

	/**
	 * Retourne le type de données (ex: postal)
	 * @return string
	 */
	public function getDonneesType()
	{
		return $this->_donneesType;
	}

	public function setDonneesType($type)
	{
		$this->_donneesType = $type;
		return $this;
	}

	/**
	 * Retourne l'id du contact associé à l'opération
	 * @return int
	 */
	public function getContactId()
	{
		return $this->_idContact;
	}

	public function setContactId($idContact)
	{
		$this->_idContact = $idContact;
		return $this;
	}

	/**
	 * Retourne le libellé du type de données
	 * @return string
	 */
	public function getDonneesTypeLibelle()
	{
		if (!isset(self::$_tabDonneesTypes)) {
			self::$_tabDonneesTypes = Comptage_Model_Operation_Dao::getInstance()->getListDonneesType();
		}
		return self::$_tabDonneesTypes[$this->_donneesType];
	}

	/**
	 * Retourne le type de cible (part, pro)
	 * @return string
	 */
	public function getCibleType()
	{
		return $this->_cibletype;
	}

	public function setCibleType($cibletype)
	{
		$this->_cibletype = $cibletype;
		return $this;
	}

	/**
	 * Retourne le potentiel, si déjà calculé
	 * @return int
	 */
	public function getComptage()
	{
		return $this->_comptage;
	}

	public function setComptage($comptage)
	{
		$this->_comptage = $comptage;
		return $this;
	}

	/**
	 * Retourne le libellé du type de cible
	 * @return string
	 */
	public function getCibleTypeLibelle()
	{
		if (!isset(self::$_tabCibleType)) {
			self::$_tabCibleType = Comptage_Model_Cible_Dao::getInstance()->getListType();
		}
		return self::$_tabCibleType[$this->_cibletype];
	}

	/**
	 * Positionne le pointeur de cible
	 * @param Comptage_Model_Cible $cible
	 */
	public function setCurrentCible(Comptage_Model_Cible $cible)
	{
		if (!isset($this[$cible->getId()])) {
			$this[$cible->getId()] = $cible;
		}
		$this->_currentCibleId = $cible->getId();
		return $this;
	}

	/**
	 * Retourne la cible en cours
	 * @return Comptage_Model_Cible
	 */
	public function getCurrentCible()
	{
		if (empty($this->_currentCibleId) || !isset($this[$this->_currentCibleId])) {
			throw new RuntimeException('Il n\'y a pas de cible en cours !!');
		}
		return $this[$this->_currentCibleId];
	}

	/**
	 * Retourne la liste des cibles
	 * @return array
	 */
	public function getCibles()
	{
		return $this->_tabCibles->getArrayCopy();
	}

	/**
	 * Met à jour la liste des cibles
	 * @param array $list
	 */
	public function setCibles($list, $currentId = null)
	{
		if ($list instanceof  ArrayObject) {
			$this->_tabCibles = $list;
		}
		else {
			$this->_tabCibles = new ArrayObject((array) $list);
		}
		if ($currentId && isset($this->_tabCibles[$currentId])) {
			$this->_currentCibleId = $currentId;
		}
		else {
			$this->setCurrentCible(current($this->_tabCibles));
		}
		return $this;
	}

	/**
	 * Efface la liste des cibles
	 */
	public function unsetCibles()
	{
		unset($this->_tabCibles);
		$this->_tabCibles = new ArrayObject();
		return $this;
	}

	/**
	 * Retourne l'objet extraction associé à l'opération
	 * @return Comptage_Model_Extraction
	 */
	public function getExtraction()
	{
		if (!isset($this->_extraction)) {
			/* @var $list ArrayObject */
			$list = Comptage_Model_Extraction_Dao::getInstance()->getByOperation($this->_id);
			if (!empty($list)) {
				$this->_extraction = current($list);
			}
		}
		return $this->_extraction;
	}

	/**
	 * Associe une extraction à l'opération
	 * @param Comptage_Model_Extraction $ext
	 */
	public function setExtraction(Comptage_Model_Extraction $ext = NULL)
	{
		if ($ext) {
			$ext->setOperationId($this->_id);
		}
		$this->_extraction = $ext;
		return $this;
	}

	/**
	 * Retourne VRAI si l'opération a un statut qui permet l'édition
	 * @return bool
	 */
	public function isEditable()
	{
		return self::isEditableState($this->_statut);
	}

	/**
	 * Retourne VRAI si le statut donné permet l'édition, FAUX sinon
	 * @param string $stateId code état à tester
	 * @return bool
	 */
	public static function isEditableState($stateId)
	{
		if (isset(self::$_tabStates[$stateId])) {
			return self::$_tabStates[$stateId]['editable'];
		}
		else {
			return false;
		}
	}


	/**
	 * Implémentation de \Countable
	 */

	public function count()
	{
		return count($this->_tabCibles);
	}


	/**
	 * Implémentation de \IteratorAggregate
	 */

	public function getIterator()
	{
		return $this->_tabCibles;
	}


	/**
	 * Implémentation de \ArrayAccess
	 */

	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->_tabCibles[] = $value;
		}
		else {
			$this->_tabCibles[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->_tabCibles[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->_tabCibles[$offset]);
		// resélectionne le dernier élément
		if ($offset === $this->_currentCibleId && count($this) >= 1) {
			$arr = array_keys($this->_tabCibles->getArrayCopy());
			$lastKey = array_pop($arr);
			$this->setCurrentCible($this[$lastKey]);
		}
	}

	public function offsetGet($offset)
	{
		return isset($this->_tabCibles[$offset]) ? $this->_tabCibles[$offset] : null;
	}

}