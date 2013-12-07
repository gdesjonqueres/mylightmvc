<?php
/**
 * @package Requeteur\Model\Societe
 */
class Admin_Model_Societe_Exception extends Model_Exception
{
	CONST SOCIETE_INCONNUE = 0x0001;
	CONST CHAMPS_OBLIG	   = 0x0002;
	CONST NON_VALIDE_TEL   = 0x0003;
	CONST NON_VALIDE_CP    = 0x0004;

	protected $tabMsg = array(
		self::SOCIETE_INCONNUE => 'La société avec l\'id %d n\'existe pas',
		self::CHAMPS_OBLIG 	   => 'Veuillez renseigner tous les champs obligatoires',
		self::NON_VALIDE_CP    => 'Le code postal doit contenir cinq chiffres',
		self::NON_VALIDE_TEL   => 'Le téléphone n\'est pas valide ou n\'est pas au bon format (exemple : 0123456789)',

	);
}