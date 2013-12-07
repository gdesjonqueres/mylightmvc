<?php
/**
 * Fichier de config pour le mode CLI
 *
 * @package Requeteur
 */

require_once 'Application.php';
$app = Application::getInstance();

if (empty($_SERVER['ENV'])) {
	throw new Exception('Environnement non defini');
}

$app->APP_NAME = 'requeteur';
$app->APP_ENV = $_SERVER['ENV'];

// Environnement (=dev|int|prod|local)
if (!in_array($app->APP_ENV, array('local', 'dev', 'int', 'prod'))) {
	throw new Exception('Environnement ' . $app->APP_ENV . ' non gere');
}


// ------------------------------------------------------------
// Fichiers
// ------------------------------------------------------------

// Chemins
$app->PATH = realpath(dirname(__FILE__) . '/..');
$app->PATH_APP = $app->PATH . '/app';
$app->PATH_INC = $app->PATH . '/inc';
$app->PATH_FILES = $app->PATH . '/files';
$app->PATH_EXEC = $app->PATH . '/exec';
$app->PATH_TEMP = $app->PATH . '/tmp';

// Chemin d'inclusion
set_include_path(implode(PATH_SEPARATOR, array(
	$app->PATH_APP,
	$app->PATH_INC,
	get_include_path(),
)));

// Auto chargement des classes
spl_autoload_register(function ($class) {
	$path = resolveClassPath($class);
	//if (!fileExists($path)) {
	//	throw new Exception('Impossible de charger le fichier de classe pour: ' . $class . ' (' . $path . ')');
	//}
	//require_once $path;
	if (fileExists($path)) {
		require_once $path;
	}
});


// ------------------------------------------------------------
// Environnement PHP
// ------------------------------------------------------------

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Mémoire allouée
ini_set("memory_limit", "512M");
ini_set("max_execution_time", 900);

// Gestion des erreurs
error_reporting(E_ALL & ~E_NOTICE);
ini_set('log_errors', 1);
ini_set('error_log', $app->PATH_EXEC . '/errors.log');

// Gestionnaire d'exception
set_exception_handler(function(Exception $e) {
	logException($e);
});

error_log('Env: ' . $app->APP_ENV);

// ------------------------------------------------------------
// Base de données
// ------------------------------------------------------------

// Tableau des connexions aux base de données

// Tableau des connexions aux bases de données
if ($app->APP_ENV == 'prod') {
	$dbConn = require 'bootstrap/db-prod.inc.php';
}
else if ($app->APP_ENV == 'int') {
	$dbConn = require 'bootstrap/db-int.inc.php';
}
else if ($app->APP_ENV == 'dev') {
	$dbConn = require 'bootstrap/db-dev.inc.php';
}
else if ($app->APP_ENV == 'local') {
	$dbConn = require 'bootstrap/db-local.inc.php';
}
else {
	throw new Exception('Pas de BDD pour environnement ' . $app->APP_ENV);
}

error_log('Db: ' . print_r($dbConn, true));

// Nombre d'éléments max pour la clause IN d'un SELECT, ex: SELECT WHERE x IN (y, z, ...)
$app->DB_ORACLE_IN_LIMIT = 400;


// ------------------------------------------------------------
// Divers
// ------------------------------------------------------------

// Template du nom des fichiers d'adresses
$app->FILES_PATTERN = 'adresses_dataneo%d.%s';


// ------------------------------------------------------------
// Inclusions
// ------------------------------------------------------------

require_once 'functions.inc.php';