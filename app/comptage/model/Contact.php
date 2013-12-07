<?php
/**
 * Gestion des contacts
 *
 * @package Requeteur\Model\Contact
 */
class Comptage_Model_Contact
{
	/**
	 * Retourne un contact
	 * @param int $id id contact
	 * @return array|false
	 */
	public static function get($id)
	{
		$contacts = self::_get($id);
		if (!empty($contacts)) {
			return current($contacts);
		}
		return false;
	}

	/**
	 * Retourne la liste complète des contacts
	 * @return array
	 */
	public static function getList()
	{
		return self::_get();
	}

	/**
	 * Insère un contact en base de données
	 * @param array $contact tableau associatif
	 * @throws InvalidArgumentException si "societe" ou "nom" n'est pas renseigné
	 */
	public static function save($contact)
	{
		if (empty($contact['societe']) || empty($contact['nom'])) {
			throw new \InvalidArgumentException('"societe" et "nom" obligatoire pour l\'enregistrement d\'un contact');
		}
		$db = Db::getInstance('Requeteur');

		$societe	= $db->escape_string_add_quotes($contact['societe']);
		$nom		= $db->escape_string_add_quotes($contact['nom']);
		$prenom		= isset($contact['prenom'])		? $db->escape_string_add_quotes($contact['prenom']) : "''";
		$email		= isset($contact['email'])		? $db->escape_string_add_quotes($contact['email']) : "''";
		$tel		= isset($contact['tel'])		? $db->escape_string_add_quotes($contact['tel']) : "''";
		$adresse	= isset($contact['adresse'])	? $db->escape_string_add_quotes($contact['adresse']) : "''";

		$sql =
"INSERT INTO contact (societe, nom, prenom, email, telephone, adresse)
VALUES ($societe, $nom, $prenom, $email, $tel, $adresse)
RETURNING id_contact;";
		$res = $db->query($sql);
		$row = $db->fetchRow($res);

		return $row['id_contact'];
	}

	private static function _get($id = null)
	{
		$list = array();

		$db = Db::getInstance('Requeteur');
		$sql = 'SELECT * FROM contact';
		if ($id) {
			$sql .= ' WHERE id_contact = ' . (int) $id;
		}
		$res = $db->query($sql);
		while ($row = $db->fetchRow($res)) {
			$list[$row['id_contact']] = array(	'id'		=> $row['id_contact'],
												'societe'	=> $row['societe'],
												'nom'		=> $row['nom'],
												'prenom'	=> $row['prenom'],
												'email'		=> $row['email'],
												'tel'		=> $row['telephone'],
												'adresse'	=> $row['adresse']);
		}

		return $list;
	}
}