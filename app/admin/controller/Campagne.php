<?php

class Admin_Controller_Campagne extends ActionController
{

	protected function _setRights()
	{
		$this->_restrictedLevel = 'gestion_campagnes';
	}

	public function listcampagnes()
	{
		$flag = $this->getParam('flag');

		if ($flag == 'enattente') {
			$this->listCpg = Comptage_Model_Campagne_Dao::getInstance()->getList(array('statut' => 'STA03'));
		}
		else {
			$this->listCpg = Comptage_Model_Campagne_Dao::getInstance()->getList();
		}
		$this->flag = $flag;
	}

	public function extraire()
	{
		if (!$this->_user->isAllowedAcces('lance_extraction')) {
			throw new Access_Exception('Vous n\'avez pas le droit de lancer une extraction de données');
		}

		$idCpg = $this->getParam('cpgid');
		if (empty($idCpg)) {
			throw new \InvalidArgumentException('Id campagne non fourni');
		}
		$idOp = $this->getParam('opid');
		if (empty($idOp)) {
			throw new \InvalidArgumentException('Id opération non fourni');
		}

		$app = Application::getInstance();

		$op = Comptage_Model_Operation_Dao::getInstance()->get($idOp)->setStatut('STA04');
		Comptage_Model_Operation_Dao::getInstance()->save($op);

		// Lance l'extraction en arrière-plan
		$cmd = 'ENV=' . $app->APP_ENV . ' /usr/bin/php5 ' . $app->PATH_EXEC . "/extraire.php $idCpg $idOp > /dev/null &";
		exec($cmd);

		if ($this->_request->isAjax()) {
			return toJson(array('ok' => true));
		}
		else {
			return true;
		}
	}
}