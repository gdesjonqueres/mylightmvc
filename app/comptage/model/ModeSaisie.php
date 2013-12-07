<?php
/**
 * Classe représentant l'entité MODE DE SAISIE
 *
 * @package Requeteur\Model\ModeSaisie
 */
class Comptage_Model_ModeSaisie
{
	protected $_id;
	protected $_code;
	protected $_libelle;
	protected $_description;

	/**
	 * Liste des arguments
	 * @var array
	 */
	protected $_tabArgs;

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

	public function getDescription()
	{
		return $this->_description;
	}

	public function setDescription($description)
	{
		$this->_description = $description;
		return $this;
	}

	public function setListArgs(array $list)
	{
		$this->_tabArgs = $list;
		return $this;
	}

	/**
	 * Retourne la liste des arguments associés à la saisie
	 * @return array
	 */
	public function getListArgs()
	{
		return $this->_tabArgs;
	}

	/**
	 * Retourne VRAI si un mode de saisie est valide pour l'enregistrement
	 * @throws Comptage_Model_ModeSaisie_Exception quand le mode n'est pas valide avec le message d'erreur associé
	 */
	public function valid()
	{
		if (empty($this->_code)) {
			throw new Comptage_Model_ModeSaisie_Exception(Comptage_Model_ModeSaisie_Exception::CHAMPS_OBLIG_CODE);
		}
		if (empty($this->_libelle)) {
			throw new Comptage_Model_ModeSaisie_Exception(Comptage_Model_ModeSaisie_Exception::CHAMPS_OBLIG_LABEL);
		}
		return true;
	}
}