<?php
/**
 * Classe pour gérer les accès une BDD Oracle
 * Motifs de conception: Adapter
 *
 * @package Fw\Db
 */
class Db_Oracle extends Db
{

	protected function connect()
	{
		$conn = @oci_connect($this->_db['user'], $this->_db['password'], $this->_db['host']);

		if (!$conn) {
			throw new Db_Exception('Erreur de connexion au serveur "host=' . $this->_db['host'] .
									' avec user=' . $this->_db['user']);
		}

		return $conn;
	}

	public function query($sql)
	{
		$stmt = @oci_parse($this->_connection, $sql);
		if (!$stmt) {
			$error = oci_error($stmt);
			throw new Db_Exception($error['message'], $this, $sql);
		}
		else {
			$res = @oci_execute($stmt, OCI_DEFAULT);
			if (!$res) {
				$error = oci_error($stmt);
				throw new Db_Exception($error['message'], $this, $sql);
			}
		}
		return $stmt;
	}

	public function fetchRow($res)
	{
		return @oci_fetch_array($res);
	}

	public function fetchToArray($res)
	{
		$rowNum = @oci_fetch_all($res, $array);
		if ($rowNum == false) {
			$error = oci_error($this->_connection);
			throw new Db_Exception($error['message'], $this);
		}
		return $array;
	}

	public function affectedRows($res)
	{
		$out = oci_num_rows($res);
		if ($out >= 0) {
			return $out;
		}
		else {
			return false;
		}
	}

	public function commit()
	{
		$commit = @oci_commit($this->_connection);
		if (!$commit) {
			$error = oci_error($commit);
			throw new Db_Exception($error['message'], $this);
		}
		return true;
	}

	public function escape_string($string)
	{
		return str_replace("'", "''", $string);
	}

	public function escape_string_add_quotes($string)
	{
		return "'" . $this->escape_string($string) . "'";
	}

	public function rollback()
	{
		$rollback = @oci_roolback($this->_connection);
		if (!$rollback) {
			$error = oci_error($rollback);
			throw new Db_Exception($error['message'], $this);
		}
		return true;
	}

	/** \fn public function parse($sql)
	 *   \brief pré-verifie un bloc pl sql
	 *
	 *   \param $sql est le bloc pl sql à exécuter
	 *   les variables à binder doivent être précéder de deux points.
	 *
	 *   \return la ressource à binder ou/et à exécuter
	 *   \return false en cas d'erreur
	 */
	public function parse($sql)
	{
		$stmt = @oci_parse($this->_connection, 'begin ' . $sql . '; end;');
		if (!$stmt) {
			$error = oci_error($stmt);
			throw new Db_Exception($error['message'], $this, $sql);
		}
		return $stmt;
	}

	/** \fn public function parse_query($sql)
	 *   \brief pré-verifie une requete sql
	 *   à utiliser uniquement en cas de binding
	 *
	 *   \param $sql est la requete sql à exécuter
	 *   les variables à binder doivent être précéder de deux points.
	 *
	 *   \return la ressource à binder ou/et à exécuter
	 *   \return false en cas d'erreur
	 */
	public function parse_query($sql)
	{
		$stmt = @oci_parse($this->_connection, $sql);
		if (!$stmt) {
			$error = oci_error($stmt);
			throw new Db_Exception($error['message'], $this, $sql);
		}
		return $stmt;
	}

	/** \fn public function bind_array($stmt,$var,&$val,$size,$length,$type)
	 *   \brief bind un tableau à une table associative Oracle
	 *
	 *   \param $stmt est la ressource issue du parse()
	 *   \param $var est la variable du bloc pl sql
	 *   \param $val est le tableau à binder
	 *   \param $size est la taille maximum du tableau
	 *   \param $length est la longueur maximum des donnés du tableau
	 *   \param $type est le type de donnée stocké dans le tableau
	 *
	 *   \return true en cas de succès
	 *   \return false en cas d'erreur
	 */
	public function bind_array($stmt, $var, &$val, $size, $length, $type)
	{
		$res = @oci_bind_array_by_name($stmt, $var, $val, $size, $length, $type);
		if (!$res) {
			$error = oci_error($stmt);
			throw new Db_Exception($error['message'], $this);
		}
		return $res;
	}

	/** \fn public function bind($stmt,$var,$val,$length,$type)
	 *   \brief bind une variable php à une variable Oracle
	 *
	 *   \param $stmt est la ressource issue du parse()
	 *   \param $var est la variable du bloc pl sql
	 *   \param $val est la variable à binder
	 *   \param $length est la longueur maximum de la donnée dans la variable
	 *   \param $type est le type de donnée stocké de la variable
	 *
	 *   \return true en cas de succès
	 *   \return false en cas d'erreur
	 */
	public function bind($stmt, $var, &$val, $length, $type)
	{
		$res = @oci_bind_by_name($stmt, $var, $val, $length, $type);
		if (!$res) {
			$error = oci_error($stmt);
			throw new Db_Exception($error['message'], $this);
		}
		return $res;
	}

	/** \fn public function execute($stmt)
	 *   \brief execute la ressource sql
	 *
	 *   \param $stmt est la ressource issue du parse()
	 *
	 *   \return true en cas de succès
	 *   \return false en cas d'erreur
	 */
	public function execute($stmt)
	{
		$res = @oci_execute($stmt);
		if (!$res) {
			$error = oci_error($stmt);
			throw new Db_Exception($error['message'], $this);
		}
		return $stmt;
	}

	public function getLastError()
	{
		$error = oci_error($this->_connection);
		return $error['message'];
	}
}