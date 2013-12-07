<?php
/**
 * Classe pour gérer une requête HTTP
 *
 * @package Fw\Mvc
 */
class Request
{
	/**
	 * Regex d'accès à une action, au format "module?:controller?:action?"
	 * @var string
	 */
	CONST REGEX_MCA_URL = '#^(?P<module>\w*)\:(?P<controller>\w*)\:(?P<action>\w*)$#';

	/**
	 * Nom du module courant
	 * @var string
	 */
	private $_module;

	/**
	 * Nom du controller courant
	 * @var string
	 */
	private $_controller;

	/**
	 * Nom de l'action courante
	 * @var string
	 */
	private $_action;

	/**
	 * Liste des paramètres
	 * @var array
	 */
	private $_tabParams;

	/**
	 * Renvoi TRUE si requête AJAX, sinon FALSE
	 * @return bool
	 */
	public function isAjax()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			return true;
		}
		return false;
	}

	/**
	 * Met à jour le nom du module
	 * @param string $value
	 */
	public function setModule($value)
	{
		$this->_module = $value;
		return $this;
	}

	/**
	 * Retourne le nom du module en cours
	 * @return string
	 */
	public function getModule()
	{
		if (is_null($this->_module)) {
			$this->_module = $this->getParam('m');
		}
		return $this->_module;
	}

	/**
	 * Met à jour le nom du controleur en cours
	 * @param string $value
	 */
	public function setController($value)
	{
		$this->_controller = $value;
		return $this;
	}

	/**
	 * Retourne le nom du controleur en cours
	 * @return string
	 */
	public function getController()
	{
		if (is_null($this->_controller)) {
			$this->_controller = $this->getParam('c');
		}
		return $this->_controller;
	}

	/**
	 * Met à jour le nom de l'action
	 * @param string $value
	 */
	public function setAction($value)
	{
		$this->_action = $value;
		return $this;
	}

	/**
	 * Retourne le nom de l'action en cours
	 * @return string
	 */
	public function getAction()
	{
		if (is_null($this->_action)) {
			$this->_action = $this->getParam('a');
		}
		return $this->_action;
	}

	/**
	 * Met à jour un paramètre
	 * @param string $key nom du paramètre
	 * @param mixed $value valeur
	 */
	public function setParam($key, $value)
	{
		$this->_tabParams[$key] = $value;
		return $this;
	}

	/**
	 * Retourne un paramètre
	 * @param string $key nom du paramètre
	 * @param mixed $default valeur par défaut
	 * @param bool $sanitize TRUE par défaut la valeur est filtrée
	 * @return mixed
	 */
	public function getParam($key, $default = null, $sanitize = true)
	{
		if (!isset($this->_tabParams[$key])) {
			if (($value = $this->_getTaintedParam($key)) !== false) {
				if (is_string($value)) {
					$value = trim($value);
				}
				$this->_tabParams[$key] = $sanitize ? filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)
													: $value;
			}
			else {
				return $default;
			}
		}
		return $this->_tabParams[$key];
	}

	/**
	 * Retourne un paramètre sous forme de tableau
	 * @param string $key nom du paramètre
	 * @param mixed $default valeur par défaut
	 * @return array
	 */
	public function getArray($key, $default = array())
	{
		$values = (array) $this->getParam($key, $default, false);

		// élimine les valeurs à chaîne vide
		return array_filter($values, function($value) {
										return ($value !== '');
		});
	}

	/**
	 * Retourne la valeur d'un paramètre d'abord dans la source POST, ensuite GET
	 * @param string $key nom du paramètre
	 * @return mixed
	 */
	private function _getTaintedParam($key)
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$key])) {
			return $_POST[$key];
		}
		elseif (isset($_GET[$key])) {
			return $_GET[$key];
		}
		else {
			return false;
		}
	}

	/**
	 * Retourne la méthode (POST|GET)
	 * @return string
	 */
	public function getMethod()
	{
		return (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
	}

	/**
	 * Retourne l'url correspondant à la route demandée
	 * @param string $route route au format "module:controller:action"
	 * @param array $args liste de paramètres nommés
	 * @param $base base de l'url (ex: /gdesj/requeteur/, http://dataneo.fr/, ...)
	 */
	public static function createUri($route, array $args = null, $base = null)
	{
		if (is_null($base)) {
			$base = Application::getInstance()->URL_ROOT;
		}

		$qStr = '';
		if (preg_match(self::REGEX_MCA_URL, $route, $matches)) {
			if (isset($matches['module']) && !empty($matches['module'])){
				$qStr .= 'm=' . rawurlencode($matches['module']) . '&';
			}
			if (isset($matches['controller']) && !empty($matches['controller'])){
				$qStr .= 'c=' . rawurlencode($matches['controller']) . '&';
			}
			if (isset($matches['action']) && !empty($matches['action'])){
				$qStr .= 'a=' . rawurlencode($matches['action']) . '&';
			}
		}
		if (isset($args)) {
			$args = (array) $args;
			foreach ($args as $key => $val) {
				$qStr .= filter_var($key, FILTER_SANITIZE_STRING) . '=' .
						rawurlencode($val) . '&';
			}
		}

		$url = $base;
		if (!empty($qStr)) {
			$url .= '?' . substr($qStr, 0, -1);
		}
		return $url;
	}

	/**
	 * Retourne l'adrresse URL correspondante à la route demandée
	 * @param string $route route au format "module:controller:action"
	 * @param array $args liste de paramètres nommés
	 */
	public static function createUrl($route, array $args = null)
	{
		return self::createUri($route, $args, Application::getInstance()->URL_BASE);
	}

	public function getRequestedUri()
	{
		return $_SERVER['REQUEST_URI'];
	}
}
