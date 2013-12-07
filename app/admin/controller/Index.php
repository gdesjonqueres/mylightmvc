<?php
class Admin_Controller_Index extends ActionController
{
	protected function _setRights()
	{
		$this->_restrictedLevel = 'administration';
	}

	public function index()
	{

	}

	public function toggleDebug()
	{
		$this->_user->changeDebugMode();
		$url_source = $_SERVER['HTTP_REFERER'];
		$this->redirect($url_source);
		return false;
	}

	public function phpinfo()
	{
		if (!$this->_user->isAdmin()) {
			throw new Access_Exception('Accès restreint');
		}

		return phpinfo();
	}
}