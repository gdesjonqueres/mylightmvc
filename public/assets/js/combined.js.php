<?php
/**
 * Combine tous les scripts demandés via la session afin de ne servir plus qu'un seul fichier
 */
require_once realpath(dirname(__FILE__) . '/../../../inc') . '/bootstrap.inc.php';

$app = Application::getInstance();

session_name($app->SESSION_NAME);
session_start();

$tabScript = Session::getCombinedScript('js');
$buffer = '';
if (!empty($tabScript)) {
	foreach ($tabScript as $script) {
		$buffer .= file_get_contents($script);
	}
}
Session::unsetCombinedScript('js');

// Enable GZip encoding.
ob_start("ob_gzhandler");

// Enable caching
header('Pragma: public');
header('Cache-Control: public');

// Expire in one day
//header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
// Expire in 31 days
header("Cache-Control: max-age=2678400");
header("Expires: " . date("D, j M Y H:i:s", strtotime("now + 31 days")) . " GMT");

// Set the correct MIME type, because Apache won't set it for us
header("Content-type: application/javascript");

// Write everything out
echo($buffer);