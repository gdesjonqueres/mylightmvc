<?php
/**
 * Script pour afficher un fichier extrait dans le navigateur
 */

require_once '../../inc/bootstrap.inc.php';

$app = Application::getInstance();

session_name($app->SESSION_NAME);
session_start();

if (!($user = Session::getUser()) || !$user->isAllowedAcces('telechargement')) {
	header('Location: ../index.php');
}

$idOp = !empty($_GET['id']) ? (int) $_GET['id'] : '';
if (!$idOp) {
	die("Id operation incorrect");
}

// @todo: contrôler que l'utilisateur a bien le droit d'accéder au fichier

$op = Comptage_Model_Operation_Dao::getInstance()->get($idOp);
$file = $op->getExtraction()->getFichier();
$path = $app->PATH_FILES . '/' . $file->nom;

if (!file_exists($path)) {
	die('Fichier inexistant');
}

header('Content-disposition: attachment; filename=' . $file->nom); //Pour indiquer le nom du fichier côté client
header('Content-Type: application/force-download'); //Force le téléchargement
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($path)); //Pour indiquer la taille du fichier, permet au navigateur d'évaluer le temps de téléchargement
ob_clean();
flush();
readfile($path); //On lit le fichier et on balance tout dans le flux
exit;
