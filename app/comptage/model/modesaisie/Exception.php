<?php
/**
 * @package Requeteur\Model\ModeSaisie
 */
class Comptage_Model_ModeSaisie_Exception extends Model_Exception
{
	CONST CHAMPS_OBLIG_CODE    = 0x0001;
	CONST CHAMPS_OBLIG_LABEL   = 0x0002;

	protected $tabMsg = array(
		self::CHAMPS_OBLIG_CODE  => 'Le code est obligatoire',
		self::CHAMPS_OBLIG_LABEL => 'Le libellé est obligatoire',
	);
}