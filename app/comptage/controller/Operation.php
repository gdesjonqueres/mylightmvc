<?php

class Comptage_Controller_Operation extends ActionController
{
	/**
	 * Campagne en cours
	 * @var Comptage_Model_Campagne
	 */
	protected $_campagne;

	/**
	 * Opération en cours
	 * @var Comptage_Model_Operation
	 */
	protected $_operation;

	protected function _setRights()
	{
		$this->_restrictedLevel = 'comptage';
	}

	protected function _init()
	{
		$campagne = Session::getCampagne();
		if (!$campagne) {
			throw new \RuntimeException('Pas de campagne en cours !');
		}
		$this->_campagne = $campagne;
		$this->_operation = $campagne->getCurrentOperation();
	}

	/**
	 * Formulaire choix d'un contact parmi la liste des contacts
	 */
	public function listcontacts()
	{
		if ($this->_request->getMethod() == 'POST') {
			$contact = $this->getParam('contact');
			if (!empty($contact)) {
				$this->_operation->setContactId($contact);
			}
			else {
				$this->message = 'Veuillez sélectionner un contact';
			}
		}

		$this->listeContacts = Comptage_Model_Contact::getList();
	}

	/**
	 * Formulaire d'ajout d'un contact
	 */
	public function addcontact()
	{
		if ($this->_request->getMethod() == 'POST') {
			$societe = $this->getParam('societe');
			$nom = $this->getParam('nom');
			if (!empty($societe) && !empty($nom)) {
				$contact = array('societe'	=> $societe,
								'nom'		=> $nom,
								'prenom'	=> $this->getParam('prenom'),
								'email'		=> $this->getParam('email'),
								'tel'		=> $this->getParam('tel'),
								'adresse'	=> $this->getParam('adresse'),);
				$id = Comptage_Model_Contact::save($contact);
				$this->_operation->setContactId($id);
			}
			else {
				$this->message = 'Veuillez renseigner au moins la société et le nom';
			}
		}
	}

	/**
	 * Retourne le résultat du comptage et sauvegarde la campagne
	 * Appelé en ajax
	 */
	public function comptage()
	{
		// Permet de relacher le lock sur le fichier de session et donc de ne plus bloquer le chargement des pages
		// Mais du coup la session n'est pas mise à jour
		//session_write_close();

		$opRequeteur = new Comptage_Model_Operation_Requeteur($this->_operation);
		$ct = $opRequeteur->getComptage();
		$this->_operation->setComptage($ct);
		$this->_operation->setStatut('STA02');
		Comptage_Model_Campagne_Dao::getInstance()->saveFullCampagne($this->_campagne);

		if ($this->_request->isAjax()) {
			$return = array('ok' => true,
							'comptage' => $ct);
			if ($this->_user->isDebugMode()) {
				$return['debug'] = array('logs' => Logger::getLogEntries());
			}
			return toJson($return);
		}
		return $ct;
	}

	/**
	 * Dévérouille une opération après un comptage
	 */
	public function seteditable()
	{
		$this->_operation->setComptage(NULL);
		$this->_operation->setStatut('STA01');
		Comptage_Model_Operation_Dao::getInstance()->save($this->_operation);
		$this->redirect('comptage:cible:edit');
	}

}