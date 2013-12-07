<?php
/**
 * Classe de gestion des vues HTML
 *
 * @package Fw\View
 */
class View
{
	/**
	 * Regex pour identifier l'appel à une méthode d'accès à un Placeholder
	 * @var string
	 */
	CONST REGEX_CALL_PLACEHOLDER = '#^(?P<prefix>set|get|add)(?P<placeHolder>\w+)$#';

	/**
	 * Regex pour identifier la demande d'affichage d'un Helper
	 * @var string
	 */
	CONST REGEX_CALL_HELPER = '#^(?P<prefix>display)(?P<helper>\w+)$#';

	/**
	 * Référence vers l'objet Request
	 * @var Request
	 */
	//protected $_request;
	public $_request;

	/**
	 * Chemin vers le fichier de layout
	 * @var string
	 */
	protected $_layout;

	/**
	 * Liste des place holders (simples listes comme: readyEvent, etc...)
	 * @var array
	 */
	protected $_tabPlaceHolders = array();

	/**
	 * Liste des helpers (simples méthodes de vues comme: displayRadio, displayIntervalle, etc...)
	 * @var array
	 */
	protected $_tabHelpers = array();

	/**
	 * Liste des CSS (fichier ou script)
	 * @var array
	 */
	protected $_tabCss = array();

	/**
	 * Liste des JS (fichier ou script)
	 * @var array
	 */
	protected $_tabJs = array();

	/**
	 * TRUE si la vue doit charger les scripts minimisés s'ils existent
	 * @var bool
	 */
	protected $_minimizeScripts;

	/**
	 * TRUE si la vue doit combiner les scripts
	 * @var bool
	 */
	protected $_combineScripts;

	/**
	 * Versionne les scripts par rapport à un numéro de build (astuce pour éviter d'utiliser le cache quand le script a été modifié)
	 * @var string
	 */
	protected $_cacheQuery;

	protected $_pathCss;

	protected $_pathJs;

	protected $_urlCss;

	protected $_urlJs;

	protected $_pathPlgn;

	protected $_urlPlgn;

	public function __construct(Request $request = null)
	{
		$this->_request = $request;

// 		if (defined('USE_SCRIPT_MINIMIZE')) {
// 			$this->_minimizeScripts = USE_SCRIPT_MINIMIZE;
// 		}
// 		if (defined('USE_SCRIPT_COMBINE')) {
// 			$this->_combineScripts = USE_SCRIPT_COMBINE;
// 		}
// 		if (defined('USE_SCRIPT_VERSION') && USE_SCRIPT_VERSION == true && defined('APP_BUILD')) {
// 			$this->_cacheQuery = '?bld=' . APP_BUILD; // Ajoute une query derrière l'url des scripts
// 		}
		$app = Application::getInstance();

		$this->_pathCss  = $app->PATH_CSS;
		$this->_pathJs   = $app->PATH_JS;
		$this->_pathPlgn = $app->PATH_PLGN;
		$this->_urlCss   = $app->URL_CSS;
		$this->_urlJs    = $app->URL_JS;
		$this->_urlPlgn  = $app->URL_PLGN;

		$this->_minimizeScripts = $app->USE_SCRIPT_MINIMIZE;
		$this->_combineScripts = $app->USE_SCRIPT_COMBINE;
		if ($app->USE_SCRIPT_VERSION && isset($app->APP_BUILD)) {
			$this->_cacheQuery = '?bld=' . $app->APP_BUILD; // Ajoute une query derrière l'url des scripts
		}
		else {
			$this->_cacheQuery = '';
		}
	}

	public function __call($funct, $args)
	{
		if (preg_match(self::REGEX_CALL_PLACEHOLDER, $funct, $matches)) {
			return $this->_managePlaceHolders($matches['prefix'], $matches['placeHolder'], $args);
		}
		elseif (preg_match(self::REGEX_CALL_HELPER, $funct, $matches)) {
			return $this->_manageHelpers($matches['helper'], $args);
		}
		throw new \BadMethodCallException("Méthode inconnue " . __CLASS__ . "::$funct");
	}

	/**
	 * Gestion des place holders (mise à jour et récupération)
	 * @param string $method (get|set|add)
	 * @param string $placeHolderName
	 * @param array $args
	 */
	private function _managePlaceHolders($method, $placeHolderName, $args)
	{
		$placeHolderName = strtolower($placeHolderName);
		switch ($method) {
			case 'set' :
				$this->_tabPlaceHolders[$placeHolderName] = $args[0];
				return $this;
				break;

			case 'get' :
				if (array_key_exists($placeHolderName, $this->_tabPlaceHolders)) {
					return $this->_tabPlaceHolders[$placeHolderName];
				}
				else {
					return false;
				}
				break;

			case 'add' :
				if (array_key_exists($placeHolderName, $this->_tabPlaceHolders)) {
					$this->_tabPlaceHolders[$placeHolderName] = (array) $this->_tabPlaceHolders[$placeHolderName];
					$this->_tabPlaceHolders[$placeHolderName][] = $args[0];
					$this->_tabPlaceHolders[$placeHolderName] = array_unique($this->_tabPlaceHolders[$placeHolderName]);
				}
				else {
					$this->_tabPlaceHolders[$placeHolderName] = array($args[0]);
				}
				return $this;
				break;
		}
	}

	/**
	 * Gestion des view helper
	 * @param string $helperName nom du helper
	 * @param array $args arguments
	 * @throws \BadMethodCallException si le helper n'existe pas
	 */
	private function _manageHelpers($helperName, $args)
	{
		if (!isset($this->_tabHelpers[$helperName])) {
			$helperExists = false;
			// Helper défini au niveau du module courant ?
			if (!$helperExists) {
				$class = $this->_request->getModule() . '_View_Helper_' . ucwords($helperName);
				if (fileExists(resolveClassPath($class))) {
					$this->_tabHelpers[$helperName] = new $class($this);
					$helperExists = true;
				}
			}
			// Helper défini dans le module par défaut ?
			if (!$helperExists) {
				$class = Application::getInstance()->DEFAULT_MODULE . '_View_Helper_' . ucwords($helperName);
				if (fileExists(resolveClassPath($class))) {
					$this->_tabHelpers[$helperName] = new $class($this);
					$helperExists = true;
				}
			}
			// Helper standard ?
			if (!$helperExists) {
				$class = 'View_Helper_' . ucwords($helperName);
				if (fileExists(resolveClassPath($class))) {
					$this->_tabHelpers[$helperName] = new $class($this);
					$helperExists = true;
				}
			}
			// Erreur
			if (!$helperExists) {
				throw new \BadMethodCallException("Helper inconnu $helperName");
			}
		}
		return call_user_func_array(array($this->_tabHelpers[$helperName], 'display' . $helperName), $args);
	}

	/**
	 * Retourne le nom du script minifié (sous la forme: {nom-script}.min.{extension})
	 * @param string $scriptName nom du script css ou js
	 * @return string|NULL
	 */
	protected function _getMinifiedScriptName($scriptName)
	{
		if (preg_match('#(\.|-)min(\.js|\.css)$#', $scriptName)) {
			return $scriptName;
		}
		if (preg_match('#(\.js|\.css)$#', $scriptName, $matches)) {
			return substr($scriptName, 0, strrpos($scriptName, $matches[1])) . '.min' . $matches[1];
		}
		return null;
	}

	/**
	 * Renvoi TRUE si le script est externe (non hébergé sur le serveur, comme un script servi par un cdn par ex)
	 * Considère que l'url des scripts internes est forcément en relatif
	 * @param string $url url du script
	 * @return boolean
	 */
	protected function _isScriptExternal($url)
	{
		return (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '//') === 0);
	}

	/**
	 * Ajoute un fichier CSS
	 * @param string $src   Nom du fichier .css ou code source css
	 * @param bool $prepend à TRUE le css sera placé en premier dans le head
	 * @param string $media Media de destination (screen, print, projection, braille, speech, all)
	 * @param string $title Titre de la feuille de styles
	 * @param string $url url de base vers les fichiers css
	 * @param string $path chemin de base vers les fichiers css
	 * @return View Instance courante
	 */
// 	public function addCss($src, $prepend = false, $media = '', $title = '', $url = URL_CSS, $path = CSS_PATH)
	public function addCss($src, $prepend = false, $media = '', $title = '', $url = '', $path = '')
	{
		if (!$url) {
			$url = $this->_urlCss;
		}
		if (!$path) {
			$path = $this->_pathCss;
		}

		if (!array_key_exists($src, $this->_tabCss)) {
			// C'est un fichier
			if (substr($src, -4) == '.css') {
				// Script interne
				if (!$this->_isScriptExternal($src)) {
					$exists = false;
					// Vérifie si on charge la version minimisée
					if ($this->_minimizeScripts) {
						$srcMin = $this->_getMinifiedScriptName($src);
						// La version minimisée existe
						if (file_exists($path . '/' . $srcMin)) {
							$exists = true;
							$src = $srcMin;
						}
					}
					// Charge la version non minimisée
					if (!$exists) {
						if (file_exists($path . '/' . $src)) {
							$exists = true;
						}
					}
					if (!$exists) {
						throw new \RuntimeException('Fichier asset inexistant: "' . $path . '/' . $src . '"');
					}
					// Le script sera combiné
					if ($this->_combineScripts) {
						$script = array(
							'type'  => 'combined',
							'path'  => $path . '/' . $src);
					}
					// Le script sera inclus normalement
					else {
						$script = array(
							'type'  => 'file',
							'href'  => $url . $src . $this->_cacheQuery,
							'path'  => $path . '/' . $src,
							'media' => trim($media),
							'title' => trim($title));
					}
				}
				// Script externe, inclus normalement
				else {
					$script = array(
						'type'  => 'file',
						'href'  => $url,
						'media' => trim($media),
						'title' => trim($title));
				}
			}
			// Directement du code css, sera combiné et inclus dans une balise <style>
			else {
				$script = array(
					'type'  => 'script',
					'inner' => trim($src),
					'media' => trim($media),
					'title' => trim($title));
			}
			if ($prepend) {
				$this->_tabCss = array_merge(array($src => $script), $this->_tabCss);
			}
			else {
				$this->_tabCss[$src] = $script;
			}
		}

		return $this;
	}

	/**
	 * Ajoute un fichier Js
	 * @param string $src   Nom du fichier .js ou code source javascript
	 * @param bool $prepend à TRUE le js sera placé en premier dans le head
	 * @param string $charset Format de codage des caractères
	 * @param string $url url de base vers les fichiers js
	 * @param string $path chemin de base vers les fichiers js
	 * @return View Instance courante
	 */
// 	public function addJs($src, $prepend = false, $charset = 'UTF-8', $url = URL_JS, $path = JS_PATH)
	public function addJs($src, $prepend = false, $charset = 'UTF-8', $url = '', $path = '')
	{
		if (!$url) {
			$url = $this->_urlJs;
		}
		if (!$path) {
			$path = $this->_pathJs;
		}

		if (!array_key_exists($src, $this->_tabJs)) {
			// C'est un fichier
			if (substr($src, -3) == '.js') {
				// Script interne
				if (!$this->_isScriptExternal($src)) {
					$exists = false;
					// On charge la version minimisée du script ?
					if ($this->_minimizeScripts) {
						$srcMin = $this->_getMinifiedScriptName($src);
						// La version minimisée existe
						if (file_exists($path . '/' . $srcMin)) {
							$exists = true;
							$src = $srcMin;
						}
					}
					// Charge la version non minimisée
					if (!$exists) {
						if (file_exists($path . '/' . $src)) {
							$exists = true;
						}
					}
					if (!$exists) {
						throw new \RuntimeException('Fichier asset inexistant: "' . $path . '/' . $src . '"');
					}
					// Le script sera combiné
					if ($this->_combineScripts) {
						$script = array(
							'type'  => 'combined',
							'path'  => $path . '/' . $src);
					}
					// Le script sera inclus normalement
					else {
						$script = array(
							'type'    => 'file',
							'href'  => $url . $src . $this->_cacheQuery,
							'path'  => $path . '/' . $src,
							'charset' => trim($charset));
					}
				}
				// Script externe, inclus normalement
				else {
					$script = array(
						'type'    => 'file',
						'href'  => $src,
						'charset' => trim($charset));
				}
			}
			// Directement du code javascript, sera combiné et inclus dans une balise <script>
			else {
				$script = array(
					'type'  => 'script',
					'inner' => trim($src));
			}
			if ($prepend) {
				$this->_tabJs = array_merge(array($src => $script), $this->_tabJs);
			}
			else {
				$this->_tabJs[$src] = $script;
			}
		}

		return $this;
	}

	/**
	 * Ajoute les css et js liés à un "plugin"
	 * @param string $pluginName nom du plugin, doit être le même que le nom du répertoire dans assets/plugin
	 * @param array $scripts liste de fichiers css et js
	 * @return View
	 */
	public function addPlugin($pluginName, $scripts)
	{
		$scripts = (array) $scripts;
		foreach ($scripts as $script) {
			if (substr($script, -4) == '.css') {
// 				$this->addCss("$pluginName/$script", false, '', '', URL_PLGN, PLGN_PATH);
				$this->addCss("$pluginName/$script", false, '', '', $this->_urlPlgn, $this->_pathPlgn);
			}
			else if (substr($script, -3) == '.js') {
// 				$this->addJs("$pluginName/$script", false, '', URL_PLGN, PLGN_PATH);
				$this->addJs("$pluginName/$script", false, '', $this->_urlPlgn, $this->_pathPlgn);
			}
		}

		return $this;
	}

	/**
	 * Afficher les sources CSS
	 */
	public function displayCss()
	{
		$tabCombined = array();

		$strFile = '';
		$strScript = '';

		// Fichiers .css
		foreach ($this->_tabCss as $css) {
			if ($css['type'] == 'combined') {
				$tabCombined[] = $css['path'];
			}
			else {
				$others = '';
				if ($css['media'] != '') {
					$others .= 'media="' . $this->c($css['media']) . '" ';
				}
				if ($css['title'] != '') {
					$others .= 'title="' . $this->c($css['title']) . '" ';
				}

				if ($css['type'] == 'file') {
					$strFile .= '<link rel="stylesheet" type="text/css" ' . $others . 'href="' . $css['href'] . "\"/>\n";
				}
				else if ($css['type'] == 'script') {
					$strScript .= "<style " . $others . "type=\"text/css\">\n" . $css['inner'] . "\n</style>\n";
				}
			}
		}
		// Efface les sources
		$this->_tabCss = array();

		$out = $strFile . $strScript;
		if (!empty($tabCombined)) {
// 			$href = URL_CSS . 'combined.css.php';
			$href = $this->_urlCss . 'combined.css.php';
			if ($this->_cacheQuery) {
				$href .= $this->_cacheQuery . '&amp;';
			}
			else {
				$href .= '?';
			}
			$href .= 'l=' . md5(implode('', $tabCombined));
			$out .= '<link rel="stylesheet" type="text/css" href="' . $href . "\"/>\n";
			Session::setCombinedScript('css', $tabCombined);
		}

		return $out;
	}

	/**
	 * Afficher les sources Js
	 */
	public function displayJs()
	{
		$tabCombined = array();

		$strFile = '';
		$strScript = '';

		// Fichiers .js
		foreach ($this->_tabJs as $js) {
			if ($js['type'] == 'file') {
				$strFile .= '<script type="text/javascript" charset="' . $js['charset'] . '" ' . 'src="' . $js['href'] . "\"/></script>\n";
			}
			else if ($js['type'] == 'script') {
				$strScript .= "\n" . trim($js['inner']) . "\n";
			}
			else if ($js['type'] == 'combined') {
				$tabCombined[] = $js['path'];
			}
		}
		// Efface les sources
		$this->_tabJs = array();

		$out = $strFile;
		if (!empty($tabCombined)) {
// 			$href = URL_JS . 'combined.js.php';
			$href = $this->_urlJs . 'combined.js.php';
			if ($this->_cacheQuery) {
				$href .= $this->_cacheQuery . '&amp;';
			}
			else {
				$href .= '?';
			}
			$href .= 'l=' . md5(implode('', $tabCombined));
			$out .= '<script type="text/javascript" charset="UTF-8" src="' . $href . "\"/></script>\n";
			Session::setCombinedScript('js', $tabCombined);
		}
		if (!empty($strScript)) {
			$out .= "<script type=\"text/javascript\">\n//<![CDATA[\n" . $strScript . "\n//]]>\n</script>\n";
		}
		return $out;
	}

	/**
	 * Retourne une chaîne contenant un input de formulaire HTML
	 * @param array $attributes liste des attributs nommés
	 */
	public function displayFormInput($attributes)
	{
		$str = '<input';
		foreach ($attributes as $k => $v) {
			$str .= ' ' . $k . '="' . $this->c($v) . '"';
		}
		$str .= ' />';
		return $str;
	}

	public function displayScript($js) {
	    return "<script>\n//<![CDATA[\n" . $js . "\n//]]>\n</script>\n";
	}

	/**
	 * Met à jour le chemin d'accès ver le fichier de layout (motif 2 STEP VIEW)
	 * @param string $layout
	 */
	public function setLayout($layout)
	{
		$this->_layout = $layout;
		return $this;
	}

	/**
	 * Retourne le chemin d'accès vers le layout
	 */
	public function getLayout()
	{
		return $this->_layout;
	}

	/**
	 * Rend un fichier de vue
	 * @param string $file chemin d'accès complet vers le fichier de vue
	 * @param array $assigns listes des variables nommées
	 * @throws \InvalidArgumentException si le fichier de vue n'existe pas
	 * @return string la vue interprétée
	 */
	public function render($file, $assigns = array())
	{
		if (!fileExists($file)) {
			throw new \InvalidArgumentException("Le fichier de vue n'exite pas ($file)");
		}

		// extrait les variables pour la vue
		extract($assigns);

		// bufferise la sortie et interprète la vue
		ob_start();
		require($file);
		$str = ob_get_contents();
		ob_end_clean();

		return $str;
	}

	/**
	 * Encode les caractères spéciaux en HTML
	 * @param string $v chaîne à encoder
	 * @return string
	 */
	public function c($v)
	{
		return utf8_encode(toHtmlEntities($v));
	}

	/**
	 * Retourne une chaîne mise au singulier/pluriel, exemple: "n cible(s) mise(s) à jour"
	 * @param int $ct le count
	 * @param string $txt chaîne à accorder
	 * @param string $empty chaîne à retourner si vide, exemple: "Pas de cible mise à jour"
	 */
	public function plural($ct, $txt, $empty = '')
	{
		if ($ct == 0) {
			return $this->c($empty);
		}
		elseif ($ct == 1) {
			return $this->c(preg_replace(array('#%d#', '#\([^\)]+\)#'), array($ct, ''), $txt));
		}
		elseif ($ct >= 2) {
			return $this->c(str_replace(array('%d', '(', ')'), array($ct, '', ''), $txt));
		}
		else {
			return $txt;
		}
	}

	/**
	 * Tronque une chaîne de caractère pour l'affichage
	 * @param string $str chaîne à tronquer
	 * @param int $length longueur max
	 * @param string $truncStr chaîne à ajouter si troncature
	 */
	public function truncate($str, $length, $truncStr = '...')
	{
		if (strlen($str) > $length) {
			return substr($str, 0, $length) . $truncStr;
		}
		return $str;
	}

	public function formatDate($timestamp, $format = 'd/m/Y H:i')
	{
		return date($format, $timestamp);
	}

	public function formatNumber($number, $dec = 0)
	{
		return number_format($number, $dec, ',', ' ');
	}

	/**
	 * Limite le nombre d'éléments d'une liste de valeurs pour l'affichage dans un tooltip
	 * @param array $values liste de valeurs
	 * @param int $limitElements nombre max d'éléments
	 * @return array
	 */
	function formatTooltipValues(array $values, $limitElements = 5)
	{
		$myValues = $values;
		if (count($values) > $limitElements) {
			$myValues = array_slice($values, 0, $limitElements);
			$myValues['zzz'] = '[...]';
		}
		return $myValues;
	}

}
