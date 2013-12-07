<?php
/**
 * Classe représentant l'entité CIBLE
 *
 * @package Requeteur\Model\Cible
 */
class Comptage_Model_Cible implements \IteratorAggregate, \Countable, \ArrayAccess
{
	protected $_id;
	protected $_libelle;

	/**
	 * Type de cible
	 * @var string
	 */
	protected $_cibletype;

	/**
	 * Liste des types de cible
	 * @var array
	 */
	protected static $tabCibleType;

	/**
	 * Liste des CibleCritere
	 * @var ArrayObject
	 */
	protected $_tabCibleCriteres;

	public function __construct()
	{
		$this->_tabCibleCriteres = new ArrayObject();
		$this->_id = uniqid('tmp');
	}

	public function setId($id)
	{
		$this->_id = $id;
		return $this;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function setLibelle($libelle)
	{
		$this->_libelle = $libelle;
		return $this;
	}

	public function getLibelle()
	{
		return $this->_libelle;
	}

	/**
	 * Met à jour le type de cible (part, pro)
	 * @param string $cibletype
	 */
	public function setCibleType($cibletype)
	{
		$this->_cibletype = $cibletype;
		return $this;
	}

	/**
	 * Retourne le type de cible (part, pro)
	 * @return string type de cible
	 */
	public function getCibleType()
	{
		return $this->_cibletype;
	}

	/**
	 * Retourne le libellé du type de cible
	 * @return string
	 */
	public function getCibleTypeLibelle()
	{
		if (!isset(self::$tabCibleType)) {
			self::$tabCibleType = Comptage_Model_Cible_Dao::getInstance()->getListType();
		}
		return self::$tabCibleType[$this->_cibletype];
	}

	/**
	 * Retourne le rang de la cible dans une opération
	 * @param Comptage_Model_Operation $op
	 * @throws RuntimeException si la cible n'est pas incluse dans l'opération
	 */
	public function getRank(Comptage_Model_Operation $op)
	{
		$rank = 0;
		foreach ($op as $cible) {
			if ($cible === $this) {
				return $rank;
			}
			$rank++;
		}
		throw new \RuntimeException('La cible ' . $this->getId() . 'n\'est pas incluse dans l\'opération ' . $op->getId());
	}


	/**
	 * Implémentation de \Countable
	 */

	public function count()
	{
		return count($this->_tabCibleCriteres);
	}


	/**
	 * Implémentation de \IteratorAggregate
	 */

	public function getIterator()
	{
		return $this->_tabCibleCriteres;
	}


	/**
	 * Implémentation de \ArrayAccess
	 */

	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->_tabCibleCriteres[] = $value;
		}
		else {
			$this->_tabCibleCriteres[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->_tabCibleCriteres[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->_tabCibleCriteres[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->_tabCibleCriteres[$offset]) ? $this->_tabCibleCriteres[$offset] : null;
	}
}