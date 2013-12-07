<?php
/**
 * Classe représentant l'entité CAMPAGNE
 *
 * @package Requeteur\Model\Campagne
 */
class Comptage_Model_Campagne implements \IteratorAggregate, \Countable, \ArrayAccess
{
	CONST LIBELLE_DEFAULT = 'Nouvelle campagne';
	CONST STATUT_DEFAULT = 'STA01';

	protected $_id;
	protected $_libelle;
	protected $_dateCreation;
	protected $_statut;
	protected $_userId;

	/**
	 * Pointeur sur l'opération en cours
	 * @var int
	 */
	protected $_currentOpId;

	/**
	 * Liste des opérations
	 * @var ArrayObject
	 */
	protected $_tabOperations;

	/**
	 * Liste des états d'une campagne
	 * @var array
	 */
	protected static $_tabStates = array(	'STA01' => array('editable' => true),
											'STA02' => array('editable' => true),);

	public function __construct()
	{
		$this->_tabOperations= new ArrayObject();
		$this->_id = uniqid('tmp');
		$this->_libelle = self::LIBELLE_DEFAULT;
		$this->_dateCreation = strtotime('now');
		$this->_statut = self::STATUT_DEFAULT;
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

	public function getLibelle()
	{
		return $this->_libelle;
	}

	public function setLibelle($libelle)
	{
		$this->_libelle = $libelle;
		return $this;
	}

	public function getDateCreation()
	{
		return $this->_dateCreation;
	}

	public function setDateCreation($dateCreation)
	{
		$this->_dateCreation = $dateCreation;
		return $this;
	}

	/**
	 * Retourne le code du statut de la campagne
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
	 * Retourne le libellé du statut de la campagne
	 * @return string
	 */
	public function getStatutLibelle()
	{
		return Comptage_Model_Common::getStatutLibelle($this->_statut);
	}

	/**
	 * Retourne l'id de l'utilisateur ayant créé la campagne
	 */
	public function getUserId()
	{
		return $this->_userId;
	}

	public function setUserId($id)
	{
		$this->_userId = $id;
		return $this;
	}

	/**
	 * Positionne le pointeur d'opération
	 * @param Comptage_Model_Operation $op opération en cours
	 */
	public function setCurrentOperation(Comptage_Model_Operation $op)
	{
		if (!isset($this[$op->getId()])) {
			$this[$op->getId()] = $op;
		}
		$this->_currentOpId = $op->getId();
		return $this;
	}

	/**
	 * Retourne l'opération en cours
	 * @return Comptage_Model_Operation
	 */
	public function getCurrentOperation()
	{
		if (empty($this->_currentOpId) || !isset($this[$this->_currentOpId])) {
			throw new RuntimeException('Il n\'y a pas d\'opération en cours !!');
		}
		return $this[$this->_currentOpId];
	}

	/**
	 * Affecte une liste d'opérations à la campagne
	 * @param ArrayObject|array $list liste d'opérations
	 * @param int $currentId id de l'opération sur laquelle positionner le flag "en cours"
	 */
	public function setOperations($list, $currentId = null)
	{
		if ($list instanceof  ArrayObject) {
			$this->_tabOperations = $list;
		}
		else {
			$this->_tabOperations = new ArrayObject((array) $list);
		}
		if ($currentId && isset($this->_tabOperations[$currentId])) {
			$this->_currentOpId = $currentId;
		}
		else {
			$this->setCurrentOperation(current($this->_tabOperations));
		}
		return $this;
	}

	/**
	 * Retourne VRAI si la campagne a un statut qui permet l'édition
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
		return count($this->_tabOperations);
	}


	/**
	 * Implémentation de \IteratorAggregate
	 */

	public function getIterator()
	{
		return $this->_tabOperations;
	}


	/**
	 * Implémentation de \ArrayAccess
	 */

	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->_tabOperations[] = $value;
		}
		else {
			$this->_tabOperations[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->_tabOperations[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->_tabOperations[$offset]);
		// resélectionne le dernier élément
		if (count($this) >= 1) {
			$arr = array_keys($this->_tabOperations->getArrayCopy());
			$lastKey = array_pop($arr);
			$this->setCurrentOperation($this[$lastKey]);
		}
	}

	public function offsetGet($offset)
	{
		return isset($this->_tabOperations[$offset]) ? $this->_tabOperations[$offset] : null;
	}

}