<?php
/**
 * @package Requeteur\Model\Extraction
 */
class Comptage_Model_Extraction_Exception extends Model_Exception
{
	CONST CHAMPS_OBLIG_CONTACT      = 0x0001;
	CONST CHAMPS_OBLIG_QUANTITE     = 0x0002;
	CONST CHAMPS_OBLIG_FICHIERTYPE  = 0x0003;
	CONST CHAMPS_OBLIG_ENVOI        = 0x0004;
	CONST CHAMPS_OBLIG_QUANTITESUP  = 0x0005;
	CONST CHAMPS_OBLIG_CHAMPS       = 0x0006;
	CONST FICHIER_FORMAT_INCORRECT  = 0x0007;

	protected $tabMsg = array(
		self::CHAMPS_OBLIG_CONTACT      => 'Vous devez choisir un destinataire',
		self::CHAMPS_OBLIG_QUANTITE     => 'Le nombre d\'adresses est obligatoire',
		self::CHAMPS_OBLIG_QUANTITESUP  => 'Le nombre d\'adresses ne peut pas être supérieure à la valeur du comptage',
		self::CHAMPS_OBLIG_FICHIERTYPE  => 'Le format de fichier est obligatoire',
		self::CHAMPS_OBLIG_ENVOI        => 'La méthode d\'envoi est obligatoire',
		self::CHAMPS_OBLIG_CHAMPS       => 'Le nombre de champs à extraire ne peut être nul',
		self::FICHIER_FORMAT_INCORRECT  => 'Le format de fichier sélectionné est limité à %d lignes. Merci de choisir un autre format.',
	);
}