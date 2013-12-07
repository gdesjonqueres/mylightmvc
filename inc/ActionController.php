<?php
/**
 * Classe abstraite ActionController
 * Super classe pour tous les controleurs d'action
 *
 * @package Fw\Mvc
 */
class ActionController
{
	/**
	 * Requête HTTP
	 * @var Request
	 */
	protected $_request;

	/**
	 * Réponse
	 * @var Response
	 */
	protected $_response;

	/**
	 * True si une redirection est demandée
	 * @var bool
	 */
	protected $_redirected;

	/**
	 * True si l'utilisateur doit être connecté pour accéder au controller, True par défaut
	 * @var bool
	 */
	protected $_isLoginRequired = true;

	/**
	 * Intitulé du droit d'accès controller
	 * @var string
	 */
	protected $_restrictedLevel;

	/**
	 * Référence vers l'objet User courant
	 * @var Admin_Model_User
	 */
	protected $_user;

	/**
	 * Chemin vers le répertoire des vues
	 * @var string
	 */
	protected $_viewPath;

	/**
	 * Constructeur
	 * @param Request $request requête HTTP
	 * @param Response $response réponse HTTP
	 */
	public function __construct(Request $request, Response $response)
	{
		$this->_request  = $request;
		$this->_response = $response;
		$this->_redirected = false;
	}

	/**
	 * Méthode de gestion des accès à redéfinir dans chaque contrôleur
	 * @throws RuntimeException si la méthode n'est pas redifinie
	 */
	protected function _setRights()
	{
		throw new \RuntimeException('Méthode "_setRights()" non redéfinie dans le controller');
	}

	/**
	 * Construit le nom de classe du contrôleur à instancier pour la factory
	 * @param Request $request
	 * @return string
	 */
	protected static function _resolveClassName(Request $request)
	{
		return ucfirst($request->getModule()) . '_Controller_' . ucfirst($request->getController());
	}

	/**
	 * Construit le chemin vers les vues du contrôleur
	 */
	protected function _resolveViewPath()
	{
		return $this->_request->getModule() . '/view/' . strtolower($this->_request->getController()) . '/';
	}

	/**
	 * Retourne le chemin vers les vues du contrôleur
	 * @return string
	 */
	public function getViewPath()
	{
		if (!isset($this->_viewPath)) {
			$this->_viewPath = $this->_resolveViewPath();
		}
		return $this->_viewPath;
	}

	/**
	 * Construit le contrôleur d'action suivant la requête HTTP
	 * @param Request $request requête HTTP
	 * @param Response $response reponse à fournir au navigateur
	 * @throws Mvc_UnknownControllerException si le controleur demandé n'existe pas
	 * @throws Access_Exception si l'utilisateur n'a pas accès au controleur ou s'il tente d'accéder à un domain restreint sans être loggé
	 * @return Response
	 */
	public static function process(Request $request, Response $response)
	{
		// Détermine le controller requis
		$class = self::_resolveClassName($request);

		// Si le controller n'existe pas
		if (!fileExists(resolveClassPath($class))) {
			throw new Mvc_UnknownControllerException('contrôleur introuvable ' .
													$request->getModule() . ':' .
													$request->getController());
		}

		// Récupère l'utilisateur connecté (si existe)
		/* @var $user User*/
		$user = Session::getUser();

		/* @var $controller ActionController */
		$controller = new $class($request, $response);

		// Appelle la méthode de mise à jour des droits d'accès
		$controller->_setRights();

		// Si l'utilisateur a besoin d'être connecté
		if ($controller->_isLoginRequired && !$user) {
			throw new Access_NoSessionException('L\'utilisateur doit être connecté pour accéder à ' .
												$request->getModule() . ':' .
												$request->getController());
		}

		// Vérifie le niveau d'accès
		if (!empty($controller->_restrictedLevel)) {
			if (!$user || !$user->isAllowedAcces($controller->_restrictedLevel)) {
				throw new Access_Exception('Violation de droits d\'accès vers ' .
											$request->getModule() . ':' .
											$request->getController());
			}
		}

		// Ajoute l'utilisateur au controller et à la réponse (=> dispo pour la vue)
		if ($user !== null) {
			$controller->_user = $user;
			$controller->_response->addVar('user', $user); // Attention au risque d'écrasement de la variable

			// Si en mode debug, ajoute certaines infos à la réponse
			if ($user->isDebugMode()) {
				$controller->_response->addVar('debugInfos', array('restrictedLevel' => $controller->_restrictedLevel,
																	'isLoginRequired' => $controller->_isLoginRequired));
			}
		}

		// Ajoute l'objet Application à la vue
		$controller->_response->addVar('appVars', Application::getInstance()); // Attention au risque d'écrasement de la variable

		// Exécute l'init si défini
		if (method_exists($controller, '_init')) {
			$controller->_init();
		}

		// Exécute l'action requise
		return $controller->launch();
	}

	/**
	 * Traite une exception
	 * @param Request $request requête HTTP
	 * @param Response $response objet réponse
	 * @param Exception $e exception levée lors du traitement de la requête
	 * @return Response
	 */
	public static function processException(Request $request, Response $response, Exception $e)
	{
		// Log les exceptions (fichier + mail). Ne log pas quand une session expire
		if (ini_get('log_errors') && !($e instanceof Access_NoSessionException)) {
			logException($e);
			mailException($e);
		}

		// controller d'erreur
		$request->setModule(Application::getInstance()->DEFAULT_MODULE);
		if ($request->isAjax()) {
			$request->setController('errorjson');
		}
		else {
			$request->setController('error');
		}

		$controller = new self($request, $response);

		// Redirige vers l'accueil si pas de session en cours
		if ($e instanceof Access_NoSessionException && !$request->isAjax()) {
			// Garde en mémoire l'url demandée pour rediriger une fois l'utilisateur loggé
			if ($request->getParam('redirect') == 'true') {
				Session::setRequestedUri($request->getRequestedUri());
			}
			$controller->redirect('::');
		}

		return $controller->launchException($e);
	}

	/**
	 * Retourne la valeur d'un paramètre issu de la requête
	 * @see Request::getParam
	 * @param string $key nom du paramètre
	 * @param mixed $default valeur par défaut
	 */
	public function getParam($key, $default = null)
	{
		return $this->_request->getParam($key, $default);
	}

	/**
	 * Retourne la valeur, sous forme de tableau, d'un paramètre issu de la requête
	 * @see Request::getArray
	 * @param string $key nom du paramètre
	 * @param mixed $default valeur par défaut
	 */
	public function getArray($key, $default = array())
	{
		return $this->_request->getArray($key, $default);
	}

	/**
	 * Retourne TRUE si une action existe, FALSE sinon
	 * @param string $action
	 * @return bool
	 */
	private function _actionExists($action)
	{
		try {
			$method = new ReflectionMethod(get_class($this), $action);
			return ($method->isPublic() && !$method->isConstructor());
		}
		catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Effectue une redirection HTTP
	 * @param string $url url ou chaîne de type 'module:controller:action'
	 * @param array $args liste de paramètres nommés
	 * @throws Exception si une redirection est déjà en cours de traitement
	 */
	public function redirect($url, array $args = null)
	{
		if ($this->_redirected == true) {
			throw new Exception('Une redirection a déja été demandée');
		}
		if (preg_match(Request::REGEX_MCA_URL, $url)) {
			$url = Request::createUri($url, $args);
		}
		$this->_response->redirect($url);
		$this->_redirected = true;
	}

	/**
	 * Rend une vue
	 * @param string $file chemin relatif vers la vue
	 */
	private function _render($file)
	{
		$view = new View($this->_request);

		$app = Application::getInstance();

		// Définit un layout par défaut si existe (peut-être ensuite redéfini dans la vue elle même)
// 		if (defined('USE_LAYOUT') && USE_LAYOUT == true && defined('DEFAULT_LAYOUT_PATH')) {
		if ($app->USE_LAYOUT && isset($app->DEFAULT_LAYOUT_PATH)) {
			$view->setLayout($app->DEFAULT_LAYOUT_PATH);
		}

		// Construit le chemin complet du fichier de vue
		$path = $this->getViewPath();

		// Rend la vue avec en passant en paramètre les variables associées à la réponse
		$body = $view->render($path . $file, $this->_response->getVars());

		// Si un layout est défini, rend le layout avec la vue déjà rendue (Motif: 2 STEP VIEW)
		if (($layout = $view->getLayout())) {
			$body = $view->render($layout, array_merge($this->_response->getVars(),
														array('content' => $body)));
		}

		// Ajoute la vue rendue à la réponse
		$this->_response->setBody($body);
	}

	/**
	 * Récupère le contenu d'une variable affectée à la réponse (méthode magique pour: $this->{$param})
	 * @param string $param nom de la variable
	 * @return mixed
	 */
	public function __get($param)
	{
		return $this->_response->getVar($param);
	}

	/**
	 * Affecte une variable à la réponse (méthode magique pour: $this->{name} = $param)
	 * @param string $name
	 * @param mixed $param
	 */
	public function __set($name, $param)
	{
		$this->_response->addVar($name, $param);
	}

	/**
	 * Enregistre un message flash (message stocké en session qui s'efface une fois récupéré)
	 * @param string $msg
	 * @throws RuntimeException si aucune session
	 */
	public function setFlashMessage($msg)
	{
		if (!isset($_SESSION)) {
			throw new \RuntimeException('Pas de session !!');
		}
		$_SESSION['_flashMessage'] = $msg;
		return $this;
	}

	/**
	 * Retourne le dernier message flash
	 * @return string
	 */
	public function getFlashMessage()
	{
		$msg = '';
		if (isset($_SESSION['_flashMessage'])) {
			$msg = $_SESSION['_flashMessage'];
			unset($_SESSION['_flashMessage']);
		}
		return $msg;
	}

	/**
	 * Exécute l'action demandée
	 * @throws Mvc_UnknownActionException si action inconnue
	 * @return Response
	 */
	public function launch()
	{
		$action = $this->_request->getAction();
		if (!$this->_actionExists($action)) {
			throw new Mvc_UnknownActionException('Action introuvable ' .
												$this->_request->getModule() . ':' .
												$this->_request->getController() . ':' .
												$action . '()');
		}
		// prefiltering

		$ret = $this->$action();

		// postfiltering

		if (!$this->_redirected && $ret !== false) {
			// Si retour du controller, affecte directement le résultat à la réponse => ne rend pas de vue
			if (!empty($ret)) {
				$this->_response->setBody($ret);
			}
			// Rend la vue
			else {
				$this->_render($action . '.php');
			}
		}
		return $this->_response;
	}

	/**
	 * Affiche une exception
	 * @param Exception $e
	 */
	public function launchException(Exception $e)
	{
		if (!$this->_redirected) {
			// Ajoute l'exception à la réponse
			if (ini_get('display_errors') == 1 || Session::getUser()->isAdmin() || $this->_request->isAjax()) {
				$this->_response->addVar('exception', $e);
			}

			if ($e instanceof Mvc_Exception) {
				$this->_response->setHttpResponseCode(404);
				$this->_render('404.php');
			}
			elseif ($e instanceof Access_Exception) {
				$this->_response->setHttpResponseCode(403);
				$this->_render('403.php');
			}
			else {
				$this->_response->setHttpResponseCode(500);
				$this->_render('500.php');
			}
		}
		return $this->_response;
	}
}
