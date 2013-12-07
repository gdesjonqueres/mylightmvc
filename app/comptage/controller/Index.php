<?php

class Comptage_Controller_Index extends ActionController
{
	/**
	 * Campagne en cours
	 * @var Comptage_Model_Campagne
	 */
	//protected $_campagne;

	protected function _setRights()
	{
		$this->_restrictedLevel = 'comptage';
	}

	/**
	 * Index
	 */
	public function index()
	{
		//$this->myCampagne = Session::getCampagne();
		if (Session::getCampagne()) {
			$this->redirect('comptage:cible:');
		}
		else {
			$this->redirect('comptage:campagne:add');
		}
	}

}