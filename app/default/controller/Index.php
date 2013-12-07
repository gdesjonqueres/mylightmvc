<?php
class Default_Controller_Index extends ActionController
{
	protected function _setRights()
	{
		$this->_isLoginRequired = false;
	}

	public function index()
	{
		if (!Session::getUser()) {
			$this->redirect('::login');
		}
	}

	public function login()
	{
		if ($this->_request->getMethod() == 'POST') {
			$login = $this->getParam('login');
			$pwd   = $this->getParam('mdp');
			if (!empty($login) && !empty($pwd)) {
				$user = new Admin_Model_User();
				try {
					if ($user->login($login, $pwd)) {
						Session::setUser($user);
						if (($uri = Session::getRequestedUri()) != null) {
							$this->redirect($uri);
						}
						else {
							$this->redirect('::');
						}
						return;
					}
				}
				catch (Model_Exception $e) {
					$this->message = $e->getMessage();
				}
			}
			else {
				$this->message = 'Veuillez saisir un login et un mot de passe';
			}
		}
	}

	public function logout()
	{
		session_destroy();

		$this->redirect('::login');
	}

	public function changeSociete()
	{
		if (!$this->_user || !$this->_user->isAllowedAcces('change_societe')) {
			throw new Access_Exception('Accès interdit au changement de société');
		}

		$id = (int) $this->getParam('id_societe');
		if (!$id) {
			throw new \InvalidArgumentException('Id société invalide');
		}

		$this->_user->setSociete($id)
					->save();

		$url_source = $_SERVER['HTTP_REFERER'];
		$this->redirect($url_source);
	}
}