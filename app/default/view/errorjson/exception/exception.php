<?php
/*$return['debug'] = array();
$return['debug']['erreur'] = $exception->getMessage();
if ($exception instanceof Db_Exception) {
	$return['debug']['requete'] = $exception->getQuery();
	$return['debug']['connexion'] = $exception->getConnection();
}
$return['debug']['trace'] = $exception->getTrace();*/

$return['message'] = $exception->getMessage();
$return['type'] = get_class($exception);

if (ini_get('display_errors') == 1 || Session::getUser()->isAdmin()) {
	$return['debug'] = array();
	if ($exception instanceof Db_Exception) {
		$return['debug']['requete'] = $exception->getQuery();
		$return['debug']['connexion'] = $exception->getConnection();
	}
	$return['debug']['trace'] = $exception->getTrace();
}