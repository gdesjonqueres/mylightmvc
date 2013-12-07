<?php
/**
 * Gestion des erreurs de niveau utilisateur
 *
 * @package Fw
 */
abstract class Model_Exception extends Exception
{
	protected $tabMsg;

	public function __construct($code, array $args = null)
	{
		parent::__construct($this->getErrorLabel($code, $args), $code);
	}

	/**
	 * Retourne le libellé de l'erreur correspondant à un code donné
	 * @param int 		 $code code erreur
	 * @param array|null $args arguments à formater
	 * @return string|bool
	 */
	public function getErrorLabel($code, array $args = null)
	{
		if (!isset($this->tabMsg) || !isset($this->tabMsg[$code])) {
			return false;
		}

		if (isset($args) && is_array($args)) {
			return vsprintf($this->tabMsg[$code], $args);
		}

		return $this->tabMsg[$code];
	}
}