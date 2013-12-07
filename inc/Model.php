<?php
/**
 * Super classe pour les entités du modèle
 *
 * @package Fw
 */
abstract class Model
{
	protected $dateCreation;
	protected $dateModif;
	protected $error;

	abstract public function save();
	abstract public function load();
}