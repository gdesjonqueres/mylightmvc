<?php
/**
 * Super classe de gestion des accès à des bases de données hétérogènes
 * Gère un pool de connexion
 * Motifs de conception: Registry
 *
 * @package Fw\Db
 */
abstract class Db
{
	/**
	 * Tableau statique des connexions (registre)
	 * @var array
	 */
	private static $_tabInstances;

	/**
	 * Paramètres de connexion à une base
	 * @var array
	 */
	protected $_db;

	/**
	 * Resource de connexion
	 * @var resource
	 */
	protected $_connection;

	/**
	 *
	 * Contexte objet
	 * @var Model
	 */
	protected $_context;


	/**
	 * Contrôleur privé pour éviter l'instanciation directe
	 */
	private function __construct()
	{
	}

	/**
	 * Retourne un singleton de connexion
	 * @param string $ref nom de la connexion
	 * @param Model $context objet Model dans lequel est utilisée la connexion
	 * @throws InvalidArgumentException si la connexion demandée n'est pas définie dans le tableau global des connexions
	 * @throws RuntimeException si l'adaptateur pour gérer ce type de connexion n'existe pas
	 * @return Db
	 */
	public static function getInstance($ref, $context = null)
	{
		if (!isset(self::$_tabInstances[$ref])) {
			$dbConn = $GLOBALS['dbConn'];
			if (!isset($dbConn[$ref])) {
				throw new InvalidArgumentException('La connexion ' . $ref . ' n\'existe pas');
			}
			$dbParams = $dbConn[$ref];
			$class = 'Db_' . $dbParams['type'];
			if (!fileExists(resolveClassPath($class))) {
				throw new RuntimeException('Pas d\'adapter défini pour ' . $dbParams['type']);
			}
			self::$_tabInstances[$ref] = new $class();
			self::$_tabInstances[$ref]->_db = $dbParams;
			self::$_tabInstances[$ref]->_connection = self::$_tabInstances[$ref]->connect();
		}
		self::$_tabInstances[$ref]->setContext($context);
		return self::$_tabInstances[$ref];
	}

	/**
	 * Met à jour l'objet Model du contexte
	 * @param Model $context
	 */
	public function setContext($context = null)
	{
		$this->_context = $context;
		return $this;
	}

	/**
	 * Retourne l'objet Model du contexte
	 * @return Model
	 */
	public function getContext()
	{
		return $this->_context;
	}

	/**
	 * Effectue la connexion à la base
	 */
	abstract protected function connect();


	/** \fn public function query($sql)
	 *   \brief permet l'execution d'une requete
	 *
	 *   \param $sql la requete a executer
	 *
	 *   \return false en cas d'erreur
	 *   \return une ressource resultat de la requete en cas de succes
	 **/
	abstract public function query($sql);

	/** \fn public function commit()
	 *   \brief commit la transaction lorsqu'elle existe
	 */
	abstract public function commit();

	/** \fn public function rollback()
	 *   \brief annule la transaction lorsqu'elle existe
	 */
	abstract public function rollback();

	/** \fn public function fetchRow($res)
	 *   \brief recupere une ligne du resultat sous forme d'un tableau numerique et associatif
	 *
	 *   \param $res ressource issue de de la fonction query($sql)
	 *
	 *   \return une ligne resultat
	 *   \return false lorsque l'on atteint la fin des resultats
	 *
	 *   \see Database#query($sql)
	 **/
	abstract public function fetchRow($res);

	/** \fn public function fetchToArray($res)
	 *   \brief recupere le resultat sous forme d'un tableau associatif (colonnes) et numerique (lignes)
	 *
	 *   \param $res ressource issue de de la fonction query($sql)
	 *
	 *   \return un tableau resultat à double entrée, meme si seule colonne est attendue.
	 *   \return false en cas d'erreur ou de tableau vide
	 *
	 *   \see Database#query($sql)
	 **/
	abstract public function fetchToArray($res);

	/** \fn public function affectedRows($res)
	 *   \brief recupere le nombre de ligne affecte par la derniere requete insert,update et delete
	 *
	 *   \param $res ressource issue de de la fonction query($sql)
	 *
	 *   \return le nombre de ligne affecte
	 *   \return -1 en cas d'erreur
	 *
	 *   \see Database#query($sql)
	 **/
	abstract public function affectedRows($res);

	/** \fn public function getError()
	 *   \brief recupere le message d'erreur sous forme de string
	 *   \return l'erreur ou le warning
	 */
	abstract public function getLastError();

	/** \fn public function escape_string($string)
	 *   \brief echappe certains caracteres speciaux
	 *
	 *   \param $string est la chaine a echapper
	 *
	 *   \return le string echape
	 */
	abstract public function escape_string($string);

	/** \fn public function escape_string($string)
	 *   \brief echappe certains caracteres speciaux et ajoute des guillemets simples
	 *
	 *   \param $string est la chaine a echapper
	 *
	 *   \return le string echape
	 */
	abstract public function escape_string_add_quotes($string);
}
