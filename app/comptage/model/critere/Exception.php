<?php
/**
 * @package Requeteur\Model\Critere
 */
class Comptage_Model_Critere_Exception extends Model_Exception
{
	CONST CHAMPS_OBLIG_CODE      = 0x0001;
	CONST CHAMPS_OBLIG_LABEL     = 0x0002;
	CONST CHAMPS_OBLIG_TYPE      = 0x0003;
	CONST CHAMPS_OBLIG_TYPEVAL   = 0x0004;
	CONST CHAMPS_OBLIG_CIBLETYPE = 0x0005;

	protected $tabMsg = array(
		self::CHAMPS_OBLIG_CODE      => 'Le code est obligatoire',
		self::CHAMPS_OBLIG_LABEL     => 'Le libellé est obligatoire',
		self::CHAMPS_OBLIG_TYPE      => 'Le type de critère est obligatoire',
		self::CHAMPS_OBLIG_TYPEVAL   => 'Le type de valeur est obligatoire',
		self::CHAMPS_OBLIG_CIBLETYPE => 'Vous devez sélectionner au moins un type de cible',
	);
}