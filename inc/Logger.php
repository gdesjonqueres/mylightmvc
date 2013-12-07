<?php
/**
 * Classe de logger basique
 *
 * Singleton
 * Pas de persistance
 *
 * @package Fw
 */
class Logger
{
	private static $_logs;

	public static function log($val, $type = 'infos')
	{
		self::$_logs[] = array(
			'infos' => array(
				'datetime' => date('d/m/Y H:i:s'),
				'type' => $type),
			'entry' => $val);
	}

	public static function getLogEntries()
	{
		return self::$_logs;
	}

}