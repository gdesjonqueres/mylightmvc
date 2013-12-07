<?php
/**
 * Fonctions globales
 *
 * @package Fw
 */

/**
 * Envoi par mail aux administrateurs de l'application le contenu d'une exception
 * @param Exception $e
 */
function mailException(Exception $e)
{
	$app = Application::getInstance();

	$view = new View();
	$html = $view->render(	$app->DEFAULT_MODULE . '/view/error/mail.php',
							array(	'user' => Session::getUser(),
									'exception' => $e));

	// @: appel silencieux car Pear Mail provoque une notice "Strictly standards: Only variable references should be returned by reference"
	@sendMail($app->MAIL_ADMIN, array('Subject' => 'Erreur dans l\'application ' . $app->APP_NAME), $html);

	return true;
}

/**
 * Enregistre une exception dans le fichier de log
 * @param Exception $e
 */
function logException(Exception $e)
{
	$str  = '---------------------------------------------------------------------';
	$str .= "\n" . '--Uncaught Exception';
	$str .= "\n" . get_class($e) . ': ' . $e->getCode();
	$str .= "\n" . $e->getMessage();
	$str .= "\n" . 'Dans le fichier ' . $e->getFile();
	$str .= "\n" . 'en ligne ' . $e->getLine();
	if (isset($_SESSION) && ($myUser = Session::getUser()) !== null) {
		$str .= "\n" . '--Utilisateur:';
		$str .= "\n" . $myUser->getPrenom() . ' ' . $myUser->getNom() . '(' . $myUser->getId() . ')';
	}
	if ($e instanceof Db_Exception) {
		$str .= "\n" . '--Requete:';
		$str .= "\n" . $e->getQuery();
		$str .= "\n" . '--Connexion:';
		$str .= "\n" . print_r($e->getConnection(), true);
	}
	$str .= "\n" . '--Trace:';
	$str .= "\n" . print_r($e->getTrace(), true);

	error_log($str);

	return true;
}

/**
 * Envoi un mail au format HTML
 * @param string $recipient adresse email
 * @param array $headers en-têtes, "From": expéditeur; "Subject": sujet du mail
 * @param string $html contenu html
 */
function sendMail($recipient, $headers, $html)
{
	$app = Application::getInstance();

	$headers = array_merge(array('From' => $app->DEFAULT_MAIL_FROM,
								'Subject' => 'Service Client Dataneo',
								'Content-Type'  => 'text/html; charset=UTF-8'),
							$headers);
	if ($app->APP_ENV != 'prod') {
		$headers['Subject'] .= ' [' . $app->APP_ENV . ']';
	}
	$crlf = "\n";

	require_once 'Mail.php';
	require_once 'Mail/mime.php';

	$mime_params = array(
		'text_encoding' => '7bit',
		'text_charset'  => 'UTF-8',
		'html_charset'  => 'UTF-8',
		'head_charset'  => 'UTF-8'
	);

	$mime = new Mail_mime($crlf);
	$mime->setHTMLBody($html);
	$body = $mime->get($mime_params);
	$headers = $mime->headers($headers);

	$mail = Mail::factory('mail');
	$mail->send($recipient, $headers, $body);

	return true;
}

/**
 * Parse le nom d'une classe et renvoie le chemin correspondant vers le fichier de classe
 * suivant le modèle: Admin_Model_User correspond à {chemin d'inclusion}/admin/model/User.php
 * @param string $className nom de la classe
 * @return string chemin
 */
function resolveClassPath($className)
{
	if (($pos = strrpos($className, '_')) !== false) {
		$path = strtolower(substr(str_replace('_', '/', $className), 0, ($pos + 1))) .
				substr($className, ($pos + 1)) .
				'.php';
	}
	else {
		$path = $className . '.php';
	}
	return $path;
}

/**
 * Renvoie TRUE si un fichier existe (prend en compte les chemins d'inclusion), FALSE sinon
 * @param string $path
 * @return bool
 */
function fileExists($path)
{
	if (file_exists($path)) {
		return true;
	}
	if (stream_resolve_include_path($path) !== false) {
		return true;
	}
	return false;
}

/**
 * Convertit les caractères spéciaux d'une chaîne en HTML
 * @param string $html chaîne convertir
 * @param string $charset encodage d'origine de la chaîne
 * @return string
 */
function toHtmlEntities($html, $charset = 'UTF-8')
{
	return htmlentities($html, ENT_QUOTES, strtoupper($charset));
}

/**
 * Encode en JSON
 * @param mixed $values valeurs à encoder
 * @return string
 */
function toJson($values)
{
	return json_encode($values, JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Renvoi TRUE si une chaîne est encodée en JSON, FALSE sinon
 * @param string $string
 * @return bool
 */
function isJson($string)
{
	// pas top mais seule méthode viable en attendant d'avoir une fonction PHP pour le faire
	@json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Renvoi TRUE si une chaîne est sérialisée en PHP, FALSE sinon
 * @param string $string
 * @return bool
 */
function isSerialized($string)
{
	// pas top mais seule méthode viable
	$data = @unserialize($string);
	if ($data !== false || $string === 'b:0;') {
		return true;
	}
	return false;
}

/**
 * Renvoi un tableau contenant tous les éléments dont l'indice est soit:
 *  - compris entre min et max
 *  - >= min
 *  - <= max
 * utilisé typiquement pour obtenir la liste des tranches d'âges
 * @param array $tab tableau de valeurs
 * @param null|int $min
 * @param null|int $max
 */
function getSlices($tab, $min = null, $max = null)
{
	$keys = array_keys($tab);
	$invKeys = array_flip($keys);

	$slices = array();
	if (isset($min)) {
		$start = $invKeys[$min];
	}
	else {
		$start = 0;
	}
	if (isset($max)) {
		$end = $invKeys[$max];
	}
	else {
		$end = count($keys);
	}
	for ($i = $start; $i < $end; $i++) {
		$slices[$keys[$i]] = $tab[$keys[$i]];
	}

	return $slices;
}

/**
 * Retourne une chaîne formatée en html du dump d'un tableau
 * Applique les classes définies dans debug.css sur les différents mots clés
 * @param array $val tableau
 * @return string
 */
function formatHtmlDump($val)
{
	$val = print_r($val, true);
	$val = preg_replace(array('/\[([\w\d-_]+)(:[\w-_]+)*\]/',		// index
								'/([\w-_]+)\sObject/',				// mot-clé object
								'/=>\s(.*)/',						// valeurs
								'/(Array)/',						// mot-clé Array
								'/\040\040\040\040/',				// tabluations
								'/\n\n/'),							// sauts de ligne
						array('[<span class="pname">$1</span>]',
								'<span class="container">$1 Object</span>',
								'=> <span class="pvalue">$1</span>',
								'<span class="container">$1</span>',
								'  ',
								"\n"),
						$val);
	return '<pre>' . $val . '</pre>';
}

/**
 * Retourne une chaîne formatée en html à partir d'une requête SQL
 * Applique les classes définies dans debug.css sur les différents mots clés
 * @param array $sql tableau
 * @return string
 */
function formatHtmlSql($sql)
{
	$sql = nl2br($sql);
	$sql = preg_replace(array('/(select|from|where|order by|group by|returning|update|set|insert)/i', // mot-clés du langage sql
								'/(join|inner|left|right)/i', 	// mot-clés pour les jointures
								'/\s+(and|or|=)\s+/i',			// opérateurs
								'/(\(|\)|;|,)/',				// parenthèses, virgules, point-virgules
								'/\'([^\']+)\'/',				// chaînes de caractères
								'/\040\040\040\040/'),			// tabulations
						array('<span class="keyword">$1</span>',
								'<span class="operator">$1</span>',
								' <span class="operator">$1</span> ',
								'<span class="punctuation">$1</span>',
								'<span class="string">\'$1\'</span>',
								'  '),
						$sql);
	return $sql;
	//return '<pre>' . $sql . '</pre>';
	//return nl2br($sql);
}

