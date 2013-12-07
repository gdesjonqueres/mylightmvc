<?php
/**
 * Combine tous les scripts demandés via la session afin de ne servir plus qu'un seul fichier
 */

require_once realpath(dirname(__FILE__) . '/../../../inc') . '/bootstrap.inc.php';

$app = Application::getInstance();

session_name($app->SESSION_NAME);
session_start();

$tabScript = Session::getCombinedScript('css');
$buffer = '';
if (!empty($tabScript)) {
	foreach ($tabScript as $script) {
		$content = file_get_contents($script);

		// Résoud le problème des url des images relatives au dossier du fichier css
		// (les scripts et combined peuvent ne pas être au même niveau dans l'arborescence)

		// Le script se trouve dans le dossier plugins/{nom-du-plugin}
		if (strpos($script, $app->PATH_PLGN) === 0) {
			$pluginName = substr($script, strlen($app->PATH_PLGN) + 1);
			$pluginName = substr($pluginName, 0, strpos($pluginName, '/'));
			$content = str_replace('url(', "url(../plugin/$pluginName/", $content);
		}
		// Sinon dans le
		else {
			$content = preg_replace('#(url\()(\.\.\/){2,}#', 'url(../', $content); // relative paths (e.g.) url(../../foo.png)
		}

		$buffer .= $content;
	}
}
Session::unsetCombinedScript('css');

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
header("Content-type: text/css");

// Write everything out
echo($buffer);