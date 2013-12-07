<?php
$return['debug'] = array();
$return['debug']['erreur'] = $exception->getMessage();
if ($e instanceof Db_Exception) {
	$return['debug']['requete'] = $exception->getQuery();
	$return['debug']['connexion'] = $exception->getConnection();
}
$return['debug']['trace'] = $exception->getTrace();