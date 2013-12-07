<?php

class Comptage_Controller_Extraction extends ActionController
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

	/**
	 * @var Comptage_Model_Extraction
	 */
	protected $_extraction;

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

		$extraction = $this->_operation->getExtraction();
		if (!$extraction) {
			$extraction = new Comptage_Model_Extraction();
			$this->_operation->setExtraction($extraction);
		}
		$this->_extraction = $extraction;
	}

	/**
	 * Formulaire d'édition des caractéristiques d'une extraction
	 * @throws Comptage_Model_Extraction_Exception Si quantité commandée > potentiel
	 */
	public function index()
	{
		$listChamps = $this->_user->getDroitsDE();

		// Enregistrement
		if ($this->_request->getMethod() == 'POST') {
			$this->_extraction	->setCommentaire($this->getParam('commentaire'))
								->setEnvoi($this->getParam('envoi'))
								->setQuantite($this->getParam('quantite'));

			$this->_extraction->getFichier()->type = $this->getParam('fichiertype');
			$this->_extraction->getFichier()->options = $this->getArray('options');

			// Ajoute la liste des champs demandés
			$tabChamps = array();
			$champs = $this->getArray('champs');
			foreach ($champs as $ordre => $id) {
				$champ = new Comptage_Model_Extraction_Champ();
				$champ->champId = $id;
				$champ->ordre = $ordre;
				$champ->libelle = $listChamps[$id];
				$tabChamps[] = $champ;
			}
			$this->_extraction->setChamps($tabChamps);

			// Enregistre en base
			try {
				if ($this->getParam('quantite') > $this->_operation->getComptage()) {
					throw new Comptage_Model_Extraction_Exception(Comptage_Model_Extraction_Exception::CHAMPS_OBLIG_QUANTITESUP);
				}
				Comptage_Model_Extraction_Dao::getInstance()->save($this->_extraction);

				// Change le statut de l'opération en "Extraction demandée"
				$this->_operation->setStatut('STA03');
				Comptage_Model_Operation_Dao::getInstance()->save($this->_operation);

				// Envoi l'alerte mail de demande d'extraction
				$this->_sendAlert();

				$id = $this->_campagne->getId();
				Session::unsetCampagne();
				$this->setFlashMessage('Votre commande a bien été enregistrée et va être prise en charge par un opérateur.');
				$this->redirect('comptage:campagne:recap', array('id' => $id));
				return;
			}
			catch (Model_Exception $e) {
				$this->message = $e->getMessage();
				if ($this->_extraction->getId()) {
					// Recharge l'extraction
					$this->_operation->setExtraction(NULL);
					$this->_extraction = $this->_operation->getExtraction();
				}
			}
		}

		// Passage des données à la vue

		$this->myOperation = $this->_operation;
		$this->myCampagne = $this->_campagne;
		$this->myExtraction = $this->_extraction;

		// Contact
		if ($this->_extraction->getContactId()) {
			$this->contact = Comptage_Model_Contact::get($this->_extraction->getContactId());
		}

		// Liste des champs
		$tabChamps = $this->_extraction->getChamps();
		$tabIds = array();
		foreach ($tabChamps as $champ) {
			$tabIds[$champ->champId] = $champ->libelle;
		}
		$this->listChampsDispo = array_diff($listChamps, $tabIds);
		$this->listChampsAjoutes = $tabIds;

		$this->quantite = $this->_extraction->getQuantite() ? $this->_extraction->getQuantite() : $this->_operation->getComptage();
		$this->commentaire = $this->_extraction->getCommentaire();
		$this->envoi = $this->_extraction->getEnvoi();

		$fichier = $this->_extraction->getFichier();
		$this->myFichierType = !empty($fichier->type) 	? $fichier->type
														: ($this->quantite > Comptage_Model_Extraction::EXCEL_LINES_LIMIT ? 'txt' : 'excel');
		$this->myFichierOptions = $fichier->options;

		// Listes de données
		$this->listEnvoi = Comptage_Model_Extraction::getListEnvoi();
		$listFichierType = Comptage_Model_Extraction_Fichier::getListType();
		$listFichierTypeOptions = Comptage_Model_Extraction_Fichier::getListOptions();

		$this->listFichierType = $listFichierType;
		$this->listFichierTypeOptions = $listFichierTypeOptions;
	}

	/**
	 * Envoi aux admins le mail de demande d'extraction
	 * @return boolean
	 */
	protected function _sendAlert()
	{
	    $app = Application::getInstance();

		$view = new View();
		$html = $view->render($this->getViewPath() . 'index/mail.php',
								array(	'user' => Session::getUser(),
										'campagne' => $this->_campagne,
										'operation' => $this->_operation,
										'extraction' => $this->_extraction));

		sendMail($app->MAIL_DEMANDES, array('Subject' => 'Commande de fichier dans ' . $app->APP_NAME), $html);

		return true;
	}

	/*public function commander()
	{
		$this->_operation->setStatut('STA03');
		Comptage_Model_Operation_Dao::getInstance()->save($this->_operation);

		if ($this->_request->isAjax()) {
			return toJson(array('ok' => true));
		}
		return true;
	}*/

	/**
	 * Formulaire de sélection d'un contact parmi une liste
	 */
	public function listcontacts()
	{
		if ($this->_request->getMethod() == 'POST') {
			$contact = $this->getParam('contact');
			if (!empty($contact)) {
				$this->_extraction->setContactId($contact);
				$this->contact = Comptage_Model_Contact::get($contact);
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
				$this->_extraction->setContactId($id);
			}
			else {
				$this->message = 'Veuillez renseigner au moins la société et le nom';
			}
		}
	}

}