<?php
/**
 * @package Requeteur\Model\Extraction
 */
class Comptage_Model_Extraction
{
	// Nombre de lignes max pour Excel
	CONST EXCEL_LINES_LIMIT  = 1048576;
	CONST EXCEL5_LINES_LIMIT = 65535;

	protected $_id;
	protected $_contactId;
	protected $_operationId;
	protected $_envoi;
	protected $_commentaire;
	protected $_quantite;
	protected $_fichier;
	protected $_tabChamps;

	protected static $tabEnvoi = array('mail' => 'Email',
										'ftpdataneo' => 'FTP/SFTP Dataneo',
										'ftpclient' => 'FTP/SFTP Client');

	public function __construct()
	{
		$this->_fichier = new Comptage_Model_Extraction_Fichier();
		$this->_tabChamps = array();
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

	/**
	 * Retourne l'id du contact destinataire de l'extraction
	 * @return int
	 */
	public function getContactId()
	{
		return $this->_contactId;
	}

	public function setContactId($contactId)
	{
		$this->_contactId = $contactId;
		return $this;
	}

	/**
	 * Retourne l'id de l'opération associée à l'extraction
	 */
	public function getOperationId()
	{
		return $this->_operationId;
	}

	public function setOperationId($operationId)
	{
		$this->_operationId = $operationId;
		return $this;
	}

	public function getCommentaire()
	{
		return $this->_commentaire;
	}

	public function setCommentaire($commentaire)
	{
		$this->_commentaire = $commentaire;
		return $this;
	}

	public function getQuantite()
	{
		return $this->_quantite;
	}

	public function setQuantite($quantite)
	{
		$this->_quantite = $quantite;
		return $this;
	}

	public function getEnvoi()
	{
		return $this->_envoi;
	}

	public function setEnvoi($envoi)
	{
		$this->_envoi = $envoi;
		return $this;
	}

	/**
	 * @return Comptage_Model_Extraction_Fichier
	 */
	public function getFichier()
	{
		return $this->_fichier;
	}

	public function setFichier($fichier)
	{
		$this->_fichier = $fichier;
		return $this;
	}

	/**
	 * Retourne la liste des champs à extraire
	 * @return array of Comptage_Model_Extraction_Champ
	 */
	public function getChamps()
	{
		return $this->_tabChamps;
	}

	public function setChamps($champs)
	{
		$this->_tabChamps = $champs;
		return $this;
	}

	/**
	 * Retourne la liste des méthodes d'envoi
	 * @return array
	 */
	public static function getListEnvoi()
	{
		return self::$tabEnvoi;
	}

	/**
	 * Retourne VRAI si une extraction est valide pour l'enregistrement
	 * @throws Comptage_Model_Extraction_Exception quand l'extraction n'est pas valide avec le message d'erreur associé
	 */
	public function valid()
	{
		if (empty($this->_contactId)) {
			throw new Comptage_Model_Extraction_Exception(Comptage_Model_Extraction_Exception::CHAMPS_OBLIG_CONTACT);
		}
		if (empty($this->_envoi)) {
			throw new Comptage_Model_Extraction_Exception(Comptage_Model_Extraction_Exception::CHAMPS_OBLIG_ENVOI);
		}
		if (empty($this->_fichier) || empty($this->_fichier->type)) {
			throw new Comptage_Model_Extraction_Exception(Comptage_Model_Extraction_Exception::CHAMPS_OBLIG_FICHIERTYPE);
		}
		if (empty($this->_quantite)) {
			throw new Comptage_Model_Extraction_Exception(Comptage_Model_Extraction_Exception::CHAMPS_OBLIG_QUANTITE);
		}
		if (empty($this->_tabChamps)) {
			throw new Comptage_Model_Extraction_Exception(Comptage_Model_Extraction_Exception::CHAMPS_OBLIG_CHAMPS);
		}
		if ($this->_fichier->type == 'excel') {

			if ($this->_fichier->options['format'] == 'excel5' && $this->_quantite > self::EXCEL5_LINES_LIMIT) {
				throw new Comptage_Model_Extraction_Exception(Comptage_Model_Extraction_Exception::FICHIER_FORMAT_INCORRECT, array(self::EXCEL5_LINES_LIMIT));
			}
			else if ($this->_quantite > self::EXCEL_LINES_LIMIT) {
				throw new Comptage_Model_Extraction_Exception(Comptage_Model_Extraction_Exception::FICHIER_FORMAT_INCORRECT, array(self::EXCEL_LINES_LIMIT));
			}
		}
		return true;
	}
}