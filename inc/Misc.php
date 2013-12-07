<?php

/** class Misc
 *   \brief contient des methodes statiques utilitaires
 */
class Misc
{
	/**
	 * REGEX pour valider un n° de téléphone
	 * @var CONST
	 */
	CONST VALID_TEL_REGEX = '#^((\+[0-9]{2} ?[0-9])|([0-9]{2}))( ?[0-9]{2}){4}$#';

	/**
	 * REGEX pour valider un Code Postal
	 * @var CONST
	 */
	CONST VALID_CP_REGEX  = '#^[0-9]{5}$#';

	/**
	 * REGEX pour valider un mot de passe
	 * @var CONST
	 */
	CONST VALID_PASS_REGEX = '#(?=.*[a-zA-Z])(?=.*[0-9]).{8,}#';



	/**
	 * ----------------------------------------------------------
	 * Validation et filtrage
	 * ----------------------------------------------------------
	 */


	/**
	 * Valide une donnée suivant une REGEX
	 * @param string $regex
	 * @param strin $value
	 * @return bool
	 */
	public static function validate($regex, $value)
	{
		return preg_match($regex, $value);
	}

	/**
	 * Valide un n° de téléphone
	 * @param string $tel
	 * @return bool
	 */
	public static function validTel($tel)
	{
		return self::validate(self::VALID_TEL_REGEX, $tel);
	}

	/**
	 * Valide un Code Postal
	 * @param string $cp
	 * @return bool
	 */
	public static function validCp($cp)
	{
		return self::validate(self::VALID_CP_REGEX, $cp);
	}

	/**
	 * Valide un mot de passe
	 * @param string $pass
	 * @return bool
	 */
	public static function validPass($pass)
	{
		return self::validate(self::VALID_PASS_REGEX, $pass);
	}

	public static function isValidinteger($i)
	{
		return (is_numeric($i) && !preg_match('/[[:^digit:]]/', $i) ? true : false);
	}

	/** \fn public function validEmail($email)
	 *  \brief verifie que email est valide
	 *
	 *  la verification est faite sur la structure de l'email et sur l'existence du DNS associe
	 *
	 *  \param $email l'email a valider
	 *
	 *  \return false si l'email est invalide
	 *  \return true si l'email est valide
	 */
	public static function validEmail($email)
	{
		$isValid = true;
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex)
		{
			$isValid = false;
		}
		else
		{
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64)
			{
				// local part length exceeded
				$isValid = false;
			}
			else if ($domainLen < 1 || $domainLen > 255)
			{
				// domain part length exceeded
				$isValid = false;
			}
			else if ($local[0] == '.' || $local[$localLen-1] == '.')
			{
				// local part starts or ends with '.'
				$isValid = false;
			}
			else if (preg_match('/\\.\\./', $local))
			{
				// local part has two consecutive dots
				$isValid = false;
			}
			else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
			{
				// character not valid in domain part
				$isValid = false;
			}
			else if (preg_match('/\\.\\./', $domain))
			{
				// domain part has two consecutive dots
				$isValid = false;
			}
			else
			{
				if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local)))
				{
					// character not valid in local part unless
					// local part is quoted
					if (!preg_match('/^"(\\\\"|[^"])+"$/',
					str_replace("\\\\","",$local)))
					{
						$isValid = false;
					}
				}
			}
			if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
			{
				// domain not found in DNS
				$isValid = false;
			}
		}
		return $isValid;
	}

	public static function removeAccent($text)
	{
		$text = htmlentities($text, ENT_NOQUOTES, 'utf-8');
		$text = preg_replace('#\&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring)\;#', '\1', $text);
		$text = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $text);
		$text = preg_replace('#\&[^;]+\;#', '', $text);
		return $text;
	}


	/**
	 * ----------------------------------------------------------
	 * Divers
	 * ----------------------------------------------------------
	 */

	/**
	 * Encode un mot de passe
	 * @param string $pwd mot de passe
	 * @throws RuntimeException si la constante "SALT" n'est pas défini
	 * @return srting hash
	 */
	public static function encodePassword($pwd)
	{
		if (!defined('SALT')) {
			throw new \RuntimeException('SALT not defined');
		}
		return md5($pwd . SALT);
	}
	
}