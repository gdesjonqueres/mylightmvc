<?php
/**
 * Classe de gestion des exceptions SQL
 *
 * @package Fw\Db
 */
class Db_Exception extends Exception
{
	private $query;
	private $connection;

	/**
	 * Constructeur
	 *
	 * @param string 	$message 	message d'erreur
	 * @param int 		$code 		code de l'erreur
	 * @param Database 	$conn 		objet de connexion à la base
	 * @param string 	$query 		requête
	 */
	public function __construct($message, Db $conn = null, $query = '')
	{
		parent::__construct($message);

		$this->setQuery($query);
		$this->setConnection($conn);
	}

	public function setQuery($query)
	{
		$this->query = $query;
		return $this;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function setConnection($conn)
	{
		$this->connection = $conn;
		return $this;
	}

	public function getConnection()
	{
		return $this->connection;
	}

}