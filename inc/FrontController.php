<?php
/**
 * Classe abstraite pour dispatcher la requête HTTP reçue par l'application
 *
 * @package Fw\Mvc
 */
abstract class FrontController
{
	private static $_startedMicrotime;

	/**
	 * Dispatche la requête
	 * @param array|null $default route par défaut
	 */
	public static function dispatch($default = null)
	{
		self::$_startedMicrotime = microtime(true);

		$default = (array) $default;
		$default = array_merge(array('module'     => 'default',
									'controller' => 'index',
									'action'     => 'index'), $default);
		try {
			$request = new Request();
			if (!$request->getModule()) {
				$request->setModule($default['module']);
			}
			if (!$request->getController()) {
				$request->setController($default['controller']);
			}
			if (!$request->getAction()) {
				$request->setAction($default['action']);
			}
			$response = new Response();
			ActionController::process($request, $response)->printOut();
		}
		catch (Exception $e) {
			ActionController::processException($request, $response, $e)->printOut();
		}
	}

	/**
	 * Retoure le temps écoulé (en secondes) depuis le début du dispatch
	 * @return double
	 */
	public static function getElapsedSecs()
	{
		return microtime(true) - self::$_startedMicrotime;
	}
}
