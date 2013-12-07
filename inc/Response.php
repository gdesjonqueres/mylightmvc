<?php
/**
 * Classe de gestion d'une reponse HTTP
 *
 * @package Fw\Mvc
 */
class Response
{
	/**
	 * Liste des variables
	 * @var array
	 */
	private $_vars = array();

	/**
	 * Liste des en-têtes HTTP
	 * @var array
	 */
	private $_headers = array();

	/**
	 * Corps de la réponse
	 * @var string
	 */
	private $_body;

	/**
	* Code de réponse HTTP
	*
	* @var int
	*/
	private $_httpResponseCode = 200;

	/**
	 * Ajoute une variable à la réponse
	 * @param string $key
	 * @param string $value
	 */
	public function addVar($key, $value)
	{
		$this->_vars[$key] = $value;
		return $this;
	}

	/**
	 * Retourne une valeur
	 * @param string $key
	 */
	public function getVar($key)
	{
		return $this->_vars[$key];
	}

	/**
	 * Retourne la liste des variables
	 * @return array
	 */
	public function getVars()
	{
		return $this->_vars;
	}

	/**
	 * Met à jour le corps de la réponse
	 * @param string $value
	 */
	public function setBody($value)
	{
		$this->_body = $value;
		return $this;
	}

	/**
	 * Ajoute un header à la réponse
	 * @param string $key
	 * @param string $value
	 */
	public function addHeader($key, $value)
	{
		$this->_headers[$key] = $value;
		return $this;
	}

	/**
	* Définir le code de réponse HTTP
	*
	* @param int $code Code de réponse HTTP
	*/
	public function setHttpResponseCode($code)
	{
		$this->_httpResponseCode = $code;
		return $this;
	}

	/**
	 * Créé les en-têtes pour effectuer une redirection
	 * @param unknown_type $url
	 * @param unknown_type $permanent
	 */
	public function redirect($url, $permanent = false)
	{
		if ($permanent) {
			$this->_headers['Status'] = '301 Moved Permanently';
			$this->_httpResponseCode = 301;
		}
		else {
			$this->_headers['Status'] = '302 Found';
			$this->_httpResponseCode = 302;
		}
		$this->_headers['location'] = $url;
		return $this;
	}

	private function _sendHeaders()
	{
		foreach ($this->_headers as $key => $value) {
			if ($this->_httpResponseCode) {
				header($key. ':' . $value, true, $this->_httpResponseCode);
			}
			else {
				header($key. ':' . $value);
			}
		}

		header('HTTP/1.1 ' . $this->_httpResponseCode);
	}

	/**
	 * Envoie au navigateur le corps de la réponse avec les en-têtes
	 */
	public function printOut()
	{
		$this->_sendHeaders();
		echo $this->_body;
	}
}
