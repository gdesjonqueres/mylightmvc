<?php
/**
 * Fichier de configuration pour le mode WEB
 *
 * @package Requeteur
 */

require_once 'Application.php';
$app = Application::getInstance();

/**
 * Nom de l'application (utilisé notamment pour construire l'url de base)
 */
$app->APP_NAME = 'mylightmvc';

/**
 * N° de build de l'application
 * devrait être incrémenté lors de chaque mise en prod si les js/css ont été modifiés et que le versionning des scripts est activé
 */
$app->APP_BUILD = '0.1';

if (empty($_SERVER['APPLICATION_ENV'])) {
	throw new Exception("Pas d'environnement défini");
}

// Détermine l'environnement Web (=dev|int|prod|local)
if ($_SERVER['APPLICATION_ENV'] == 'production') {
	$app->APP_ENV = 'prod';
}
else if ($_SERVER['APPLICATION_ENV'] == 'integration') {
	$app->APP_ENV = 'int';
}
else if ($_SERVER['APPLICATION_ENV'] == 'development') {
	$app->APP_ENV = 'dev';
	$devDir = '';
	// Détermine le répertoire développeur sur le dev
	if (preg_match('#^/(?P<devDir>\w{4})/' . $app->APP_NAME . '#', $_SERVER['REQUEST_URI'], $matches)) {
		$devDir = $matches['devDir'] . '/';
	}
}
else if ($_SERVER['APPLICATION_ENV'] == 'local') {
	$app->APP_ENV = 'local';
}
else {
	throw new Exception('Environnement non pris en charge');
}


// ------------------------------------------------------------
// Fichiers
// ------------------------------------------------------------

// Chemins sur le disque
$app->PATH       = realpath(dirname(__FILE__) . '/..');    // répertoire racine du requeteur (chemin absolu)
$app->PATH_APP   = $app->PATH . '/app';                    // chemin vers le répertoire des modules (contenant controllers, models, views...)
$app->PATH_INC   = $app->PATH . '/inc';                    // contient toutes les classes techniques, notamment pour gérer le MVC
$app->PATH_IMG   = $app->PATH . '/public/assets/img';      // fichiers image
$app->PATH_JS    = $app->PATH . '/public/assets/js';       // fichiers javascript
$app->PATH_CSS   = $app->PATH . '/public/assets/css';      // fichiers css
$app->PATH_PLGN  = $app->PATH . '/public/assets/plugin';   // contient les "plugins", widgets du domaine public
$app->PATH_FILES = $app->PATH . '/files';                  // répertoire où sont créés les fichiers d'extraction d'adresses
$app->PATH_EXEC  = $app->PATH . '/exec';                   // répertoire contenant les scripts exécutés en ligne de commande
$app->PATH_TEMP  = $app->PATH . '/tmp';                    // répertoire pour les fichiers temporaires comme le fichier error.log du requeteur

// Rajoute PATH_APP et PATH_INC dans le chemin d'inclusion de PHP
// étant donné que toutes les classes du requeteur se trouvent dans l'arborescence de ces deux répertoires
set_include_path(implode(PATH_SEPARATOR, array(
	$app->PATH_APP,
	$app->PATH_INC,
	get_include_path(),
)));

// Définit la fonction d'auto chargement des classes du requeteur (appelée à chaque fois que PHP tente de charger la définition d'une classe)
spl_autoload_register(function ($class) {
	$path = resolveClassPath($class);
	if (!fileExists($path)) {
		throw new Exception('Impossible de charger le fichier de classe pour: ' . $class . ' (' . $path . ')');
	}
	require_once $path;
});


// ------------------------------------------------------------
// Environnement PHP
// ------------------------------------------------------------

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Mémoire allouée
ini_set("memory_limit", "300M");
ini_set("max_execution_time", 30);

// Gestion des erreurs
if ($app->APP_ENV == 'dev' || $app->APP_ENV == 'int' || $app->APP_ENV == 'local') {
	ini_set('display_errors', 1);
	error_reporting(-1);
}
else {
	ini_set('display_errors', 0);
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set('log_errors', 1);
	ini_set('error_log', $app->PATH_TEMP . '/errors.log');
}


// ------------------------------------------------------------
// Web
// ------------------------------------------------------------

// Définit le web root (url sans host name) de l'application (ex: "/" pour prod, "/gdes/requeteur/" dans répertoire de dev de gdes)
// utilisé pour construire toutes les url internes de l'application
if ($app->APP_ENV == 'prod') {
	$app->URL_ROOT = '/';
}
else if ($app->APP_ENV == 'int') {
	$app->URL_ROOT = '/' . $app->APP_NAME . '_test/public/';
}
else if ($app->APP_ENV == 'dev') {
	$app->URL_ROOT = '/' . $devDir . $app->APP_NAME . '/public/';
}
else {
	$app->URL_ROOT = '/' . $app->APP_NAME . '/';
}
// URL de base absolue (protocol + host name + web root)
$app->URL_BASE = 'http://' . $_SERVER['HTTP_HOST'] . $app->URL_ROOT;

// Diverses URL
$app->URL_IMG     = $app->URL_ROOT . 'assets/img/';
$app->URL_CSS     = $app->URL_ROOT . 'assets/css/';
$app->URL_JS      = $app->URL_ROOT . 'assets/js/';
$app->URL_PLGN    = $app->URL_ROOT . 'assets/plugin/';
$app->URL_SCRIPTS = $app->URL_ROOT . 'scripts/';

// Nom de la session (permet d'éviter de mélanger des données de session avec ddv2)
$app->SESSION_NAME = 'Dtno_' . ucfirst($app->APP_NAME);


// ------------------------------------------------------------
// MVC
// ------------------------------------------------------------

// Routage
// Indique quel est le module à charger si pas de module spécifié
$app->DEFAULT_MODULE = 'default';

// Layout
// Indique si on utilise un layout
// (le rendu de chaque page est intégré dans un template de base contenant les en-têtes, pieds de page et l'inclusion des scripts de base)
$app->USE_LAYOUT = true;
// Chemin vers le fichier de vue du layout
$app->DEFAULT_LAYOUT_PATH = $app->DEFAULT_MODULE . '/view/layout/default.php';

// Gestion des scripts (js, css)
if ($app->APP_ENV !== 'dev' && $app->APP_ENV !== 'local') {
	/// Indique si l'on doit charger une version minimisée si elle existe (ex: comptage.min.css pour comptage.css)
	$app->USE_SCRIPT_MINIMIZE = true;

	// Indique s'il faut combiner tous les js et tous les css dans un même fichier
	$app->USE_SCRIPT_COMBINE = true;

	// Indique si on versionne les scripts pour mieux gérer la mise en cache (utilise APP_BUILD pour versionner)
	$app->USE_SCRIPT_VERSION = true;
}
else {
	$app->USE_SCRIPT_MINIMIZE = false;
	$app->USE_SCRIPT_COMBINE = false;
	$app->USE_SCRIPT_VERSION = false;
}


// ------------------------------------------------------------
// Base de données
// ------------------------------------------------------------

// Tableau des connexions aux bases de données
if ($app->APP_ENV == 'prod') {
	$dbConn = include 'bootstrap/db-prod.inc.php';
}
else if ($app->APP_ENV == 'int') {
	$dbConn = include 'bootstrap/db-int.inc.php';
}
else if ($app->APP_ENV == 'dev') {
	$dbConn = include 'bootstrap/db-dev.inc.php';
}
else if ($app->APP_ENV == 'local') {
	$dbConn = include 'bootstrap/db-local.inc.php';
}
if (!isset($dbConn)) {
	throw new Exception('Pas de BDD pour environnement ' . $app->APP_ENV);
}

// Nombre d'éléments max pour la clause IN d'un SELECT, ex: SELECT WHERE x IN (y, z, ...)
$app->DB_ORACLE_IN_LIMIT = 400;


// ------------------------------------------------------------
// Divers
// ------------------------------------------------------------

// Gestion des mails
$app->DEFAULT_MAIL_FROM = 'support@guigui.fr';
$app->MAIL_ADMIN = 'support@guigui.fr';
if ($app->APP_ENV == 'dev' || $app->APP_ENV == 'int' || $app->APP_ENV == 'local') {
    $app->MAIL_DEMANDES = 'gdesjonqueres@guigui.fr';
}
else {
    $app->MAIL_DEMANDES = 'fichier@guigui.fr';
}
$app->MAILCALLBACK = 'support@guigui.fr';
$app->MAILFROM = 'contact@guigui.fr';
$app->MAILADMIN = 'support@guigui.fr';
$app->EMAILVALIDATION = 'support@guigui.fr';
$app->MAILCONTACT = 'contact@guigui.fr';

// Divers
define('SALT', 'cd48FS2Bvcn8');
$app->SALT = 'cd48FS2Bvcn8';
$app->TVA = 19.6;


// ------------------------------------------------------------
// Inclusions
// ------------------------------------------------------------

// Fonctions de base
require_once 'functions.inc.php';