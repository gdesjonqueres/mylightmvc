<?php
/**
 * @package Requeteur\Model\Extraction
 */
class Comptage_Model_Extraction_Fichier
{
	public $type;
	public $nom;
	public $options;

	public static $tabType = array('excel' => 'Excel',
									'txt' => 'Texte',);

	public static $tabOptions = array(	'txt' => array(
											'separator' => array(
												'label' => 'Séparateur',
												'values' => array(
													'44' => 'Virgule',			// Code ASCII pour ","
													'59' => 'Point-virgule',	// Code ASCII pour ";"
													'124' => 'Pipe',			// Code ASCII pour "|"
													'9' => 'Tabulation'))),		// Code ASCII pour "\t"
										'excel' => array(
											'format' => array(
												'label' => 'Format',
												'values' => array(
													'excel2007' => 'Excel 2007 (.xlsx)',
													'excel5' => 'Excel 95 (.xls)',))));

	public function __construct()
	{
		$this->options = array();
	}

	/**
	 * Retourne la liste des types de fichier
	 * @return array
	 */
	public static function getListType()
	{
		return self::$tabType;
	}

	/**
	 * Retourne la liste des options (format, séparateur, ...) pour chaque type de fichier
	 * @return array
	 */
	public static function getListOptions()
	{
		return self::$tabOptions;
	}
}