<?php
/**
 * Classe pour gérer les accès à une BDD Postgre
 * Motfis de conception: Adapter
 *
 * @package Fw\Db
 */
class Db_Postgre extends Db
{

	/**
	 * @throws Db_Exception si impossible de se connecter au server
	 */
	public function connect()
	{
		$str_connect = 'host=' . $this->_db['host'] .
						' port=' . $this->_db['port'] .
						' dbname=' . $this->_db['base'] .
						' user=' . $this->_db['user'] .
						' password=' . $this->_db['password'];
		$conn = @pg_connect($str_connect);

		if (!$conn) {
			throw new Db_Exception('Erreur de connexion au serveur "host=' . $this->_db['host'] .
									' port=' . $this->_db['port'] . ' dbname=' . $this->_db['base'] . '"');
		}

		return $conn;
	}

	/**
	 * Execute une requête
	 *
	 * @throws SqlException si erreur sur la requête
	 * @return int resource
	 */
	public function query($sql)
	{
		$res = @pg_query($this->_connection, $sql);

		if (!$res) {
			throw new Db_Exception(pg_last_error($this->_connection), $this, $sql);
		}

		return $res;
	}

	public function fetchRow($res)
	{
		return @pg_fetch_array($res);
	}

	public function numRow($res)
	{
		return pg_num_rows($res);
	}

	/**
	 * Retourne un résultat sous forme de tableau
	 *
	 * @param int $res resource
	 * @throws UnexpectedResultSqlException si aucun résultat
	 * @return array
	 */
	public function fetchToArray($res)
	{
		$i = 0;
		$array = array();
		while ($col = @pg_fetch_all_columns($res, $i)) {
			$name = @pg_field_name($res, $i);
			$array[$name] = $col;
			$i++;
		}
		if (empty($array)) {
			throw new Db_UnexpectedResultException('erreur postgre: array vide', $this);
		}

		return $array;
	}

	public function affectedRows($res)
	{
		// ne returne pas de message d'erreur
		return pg_affected_rows($res);
	}

	public function commit()
	{
		// ne travaille pas en transaction
	}

	public function escape_string($string)
	{
		return pg_escape_string($this->_connection, $string);
	}

	/**
	 * Echappe une chaine et ajoute des guillemets simples
	 *
	 * @param string $string
	 * @return string
	 */
	public function escape_string_add_quotes($string)
	{
		return "'" . $this->escape_string($string) . "'";
	}

	/**
	 * Renvoi la constante SQL "NULL" si vide, sinon renvoi la valeur entiere
	 * @param mixed $val
	 */
	public function intOrNull($val)
	{
		return ((empty($val) && $val !== '0' && $val !== 0) ? 'NULL' : (int) $val);
	}

	public function nullIfEmpty($val)
	{
		return ((empty($val) && $val !== '0' && $val !== 0) ? NULL : $val);
	}

	public function rollback()
	{
		// impossible d'annuler la requete
		// pg ne travaille pas en transaction
	}

	public function getLastError()
	{
		return pg_last_error($this->_connection);
	}

}

