<?php
/**
 * Classe référençant les exceptions liées à la saisie d'un utilisateur
 *
 * @package Requeteur\Model\User
 */
class Admin_Model_User_Exception extends Model_Exception
{
	CONST COMPTE_BLOQUE    = 0x00001;
	CONST COMPTE_DESACTIVE = 0x00002;
	CONST ERREUR_LOGIN     = 0x00003;
	CONST USER_INCONNU	   = 0x00004;
	CONST CHAMPS_OBLIG	   = 0x00005;
	CONST NON_VALIDE_MAIL  = 0x00006;
	CONST NON_VALIDE_CP    = 0x00007;
	CONST NON_VALIDE_MDP   = 0x00008;
	CONST NON_VALIDE_TEL   = 0x00009;
	CONST MAIL_EXISTE_DEJA = 0x0000a;

	protected $tabMsg = array(
		self::COMPTE_BLOQUE	   => 'Compte bloqué après 5 tentatives infructueuses. Veuillez attendre 10 minutes',
		self::COMPTE_DESACTIVE => 'Ce compte est désactivé, veuillez contacter notre service commercial',
		self::ERREUR_LOGIN 	   => 'Login ou mot de passe incorrect',
		self::USER_INCONNU 	   => 'L\'utilisateur avec l\'id %d n\'existe pas',
		self::CHAMPS_OBLIG 	   => 'Veuillez renseigner tous les champs obligatoires',
		self::NON_VALIDE_MAIL  => 'L\'email n\'est pas valide',
		self::NON_VALIDE_CP    => 'Le code postal doit contenir cinq chiffres',
		self::NON_VALIDE_MDP   => 'Le mot de passe doit contenir au moins 8 caracteres dont un chiffre et une lettre',
		self::NON_VALIDE_TEL   => 'Le téléphone n\'est pas valide ou n\'est pas au bon format (exemple : 0123456789)',
		self::MAIL_EXISTE_DEJA => 'L\'email existe deja, veuillez en saisir un autre',
	);

}