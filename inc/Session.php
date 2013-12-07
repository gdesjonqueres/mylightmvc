<?php

if (!isset($_SESSION)) {
	throw new \RuntimeException('Pas de session');
}
if (!isset($_SESSION[Session::$ns])) {
	$_SESSION[Session::$ns] = array();
}

/**
 * Classe d'accès aux valeurs stockées en session
 *
 * @package Fw
 */
class Session
{
	/**
	 * Session namespace
	 * @var string
	 */
	public static $ns = '_mvcdtno';

	/**
	 * Retourne l'utilisateur courant
	 * @return Admin_Model_User
	 */
	public static function getUser()
	{
		if (isset($_SESSION[self::$ns]['_user']) &&
			$_SESSION[self::$ns]['_user'] instanceof Admin_Model_User &&
			$_SESSION[self::$ns]['_user']->getId() > 0
		) {
			return $_SESSION[self::$ns]['_user'];
		}
		else {
			return null;
		}
	}

	/**
	 * Met à jour l'utlisateur de la session
	 * @param Admin_Model_User $user
	 */
	public static function setUser(Admin_Model_User $user)
	{
		if ($user->getId() > 0) {
			$_SESSION[self::$ns]['_user'] = $user;
		}
		else {
			return false;
		}
	}

	/**
	 * Retourne la campagne en cours
	 * @return Comptage_Model_Campagne
	 */
	public static function getCampagne()
	{
		if (isset($_SESSION[self::$ns]['_campagne']) && $_SESSION[self::$ns]['_campagne'] instanceof Comptage_Model_Campagne) {
			return $_SESSION[self::$ns]['_campagne'];
		}
		else {
			return null;
		}
	}

	/**
	 * Met à jour la campagne en cours
	 * @param Comptage_Model_Campagne $campagne
	 */
	public static function setCampagne(Comptage_Model_Campagne $campagne)
	{
		$_SESSION[self::$ns]['_campagne'] = $campagne;
	}

	/**
	 * Supprime la campagne en cours
	 */
	public static function unsetCampagne()
	{
		unset($_SESSION[self::$ns]['_campagne']);
	}

	/**
	 * Retourne l'adresse demandée par le navigateur
	 */
	public static function getRequestedUri()
	{
		if (isset($_SESSION[self::$ns]['_requestedUri'])) {
			$uri = $_SESSION[self::$ns]['_requestedUri'];
			unset($_SESSION[self::$ns]['_requestedUri']);
			return $uri;
		}
		else {
			return null;
		}
	}

	/**
	 * Met en session l'adresse demandée par le navigateur
	 * (utile pour faire une redirection vers une page demandée par l'utilisateur après une inter-action,
	 * typiquement un lien dans un mail vers une page en accès restreint qui nécessite au préalable l'authentification de l'utilisateur)
	 * @param string $uri
	 */
	public static function setRequestedUri($uri)
	{
		$_SESSION[self::$ns]['_requestedUri'] = $uri;
	}

	public static function addCombinedScript($type, $script)
	{
		if (!isset($_SESSION[self::$ns]['_combinedScripts'])) {
			$_SESSION[self::$ns]['_combinedScripts'] = array();
		}
		if (!isset($_SESSION[self::$ns]['_combinedScripts'][$type])) {
			$_SESSION[self::$ns]['_combinedScripts'][$type] = array();
		}
		$_SESSION[self::$ns]['_combinedScripts'][$type][] = $script;
	}

	public static function setCombinedScript($type, $scripts)
	{
		if (!isset($_SESSION[self::$ns]['_combinedScripts'])) {
			$_SESSION[self::$ns]['_combinedScripts'] = array();
		}
		if (!isset($_SESSION[self::$ns]['_combinedScripts'][$type])) {
			$_SESSION[self::$ns]['_combinedScripts'][$type] = array();
		}
		$_SESSION[self::$ns]['_combinedScripts'][$type] = $scripts;
	}

	public static function getCombinedScript($type)
	{
		if (!isset($_SESSION[self::$ns]['_combinedScripts']) ||
			!isset($_SESSION[self::$ns]['_combinedScripts'][$type])
		) {
			return null;
		}
		return $_SESSION[self::$ns]['_combinedScripts'][$type];
	}

	public static function unsetCombinedScript($type)
	{
		if (!isset($_SESSION[self::$ns]['_combinedScripts']) ||
			!isset($_SESSION[self::$ns]['_combinedScripts'][$type])
		) {
			return;
		}
		unset($_SESSION[self::$ns]['_combinedScripts'][$type]);
	}

}