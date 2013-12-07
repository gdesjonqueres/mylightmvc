<?php
/**
 * @package Requeteur\Model\Acces
 */
interface Admin_Model_Acces_Interface
{
	public function getDroitsAcces();
	public function getDroitsCritere();
	public function getDroitsDE();

	public function loadAcces();
	public function loadCritere();
	public function loadDE();

	public function isAllowedAcces($acces);
	public function isAllowedCritere($acces);
	public function isAllowedDE($acces);

	public function addAcces($id, $libelle);
	public function addCritere($id, $libelle);
	public function addDE($id, $libelle);

	public function removeAcces($libelle);
	public function removeCritere($libelle);
	public function removeDE($libelle);

	public function saveAcces();
	public function saveCritere();
	public function saveDE();
}