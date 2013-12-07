<?php
/**
 * Classe de gestion des droits d'access
 *
 * @package Requeteur\Model\Acces
 */
class Admin_Model_Acces
{
	/**
	 * Id utilisateur ou id société
	 * @var int
	 */
	protected $idEntite;

	/**
	 * Flag de contexte (=utilisateur|societe|userType)
	 * @var string
	 */
	protected $context;

	protected $droitAcces;
	protected $droitCritere;
	protected $droitDE;
	protected $error;

	/**
	 * Constructeur
	 *
	 * @param int    $id
	 * @param string $context flag de contexte (=utilisateur|societe|userType)
	 */
	public function __construct($id, $context)
	{
		$this->idEntite = $id;
		$this->context  = $context;

		$this->loadAcces();
		$this->loadCritere();
		$this->loadDE();
	}

	/**************** ACCES ******************/

	public function loadAcces()
	{
		$this->droitAcces = $this->extractAcces();
		return true;
	}

	public function extractAcces()
	{
		$tab = array();

		if ($this->context == 'utilisateur') {
			$query =
					'SELECT da.id_acces, da.code, da.libelle
					FROM acces da
					INNER JOIN droits_acces_utilisateur dau
						ON dau.id_acces = da.id_acces
					WHERE dau.id_utilisateur = ' . (int) $this->idEntite . '
					UNION
					SELECT da.id_acces, da.code, da.libelle
					FROM acces da
					INNER JOIN droits_acces_asut dat
						ON dat.id_acces = da.id_acces
					WHERE dat.id_asut =
						(SELECT id_type
						FROM utilisateur
						WHERE id_utilisateur = ' . (int) $this->idEntite . ')
					;';
		}
		elseif ($this->context == 'societe') {
			$query =
					'SELECT da.id_acces, da.code, da.libelle
					FROM acces da, droits_acces_societe das
					WHERE das.id_acces = da.id_acces
					AND das.id_societe = ' . (int) $this->idEntite . '
					;';
		}
		elseif ($this->context == 'userType') {
			$query =
					'SELECT da.id_acces, da.code, da.libelle
					FROM acces da, droits_acces_asut das
					WHERE das.id_acces = da.id_acces
					AND das.id_asut = ' . (int) $this->idEntite . '
					;';
		}
		else {
			throw new InvalidArgumentException('Le contexte donné "' . $this->context . '" n\'est associé à aucun traitement');
		}

		try {
			$pg = Db::getInstance('Requeteur', $this);

			$res = $pg->query($query);
			while ($row = $pg->fetchRow($res)) {
				//$tab[$row['libelle']] = $row['id_acces'];
				$tab[$row['id_acces']] = $row['code'];
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return $tab;
	}

	public function saveAcces()
	{
		$oldAccessTab = array_keys($this->extractAcces());
		$tabToDelete  = array_diff($oldAccessTab, array_keys($this->droitAcces));
		$tabToInsert  = array_diff(array_keys($this->droitAcces), $oldAccessTab);

		$query = '';
		if ($this->context == 'utilisateur') {
			foreach ($tabToInsert as $id) {
				$query .= 'INSERT INTO droits_acces_utilisateur (id_utilisateur, id_acces)
							VALUES (' . (int) $this->idEntite . ', ' . (int) $id . ');';
			}
			foreach ($tabToDelete as $id) {
				$query .= 'DELETE FROM droits_acces_utilisateur
							WHERE id_utilisateur = ' . (int) $this->idEntite . ' AND id_acces = ' . (int) $id . ';';
			}
		}
		elseif ($this->context == 'societe') {
			foreach ($tabToInsert as $id) {
				$query .= 'INSERT INTO droits_acces_societe (id_societe, id_acces)
							VALUES (' . (int) $this->idEntite . ', ' . (int) $id . ');';
			}
			foreach ($tabToDelete as $id) {
				$query .= 'DELETE FROM droits_acces_societe
							WHERE id_societe = ' . (int) $this->idEntite . ' AND id_acces = ' . (int) $id . ';';
			}
		}
		elseif ($this->context == 'userType') {
			foreach ($tabToInsert as $id) {
				$query .= 'INSERT INTO droits_acces_asut (id_asut, id_acces)
							VALUES (' . (int) $this->idEntite . ', ' . (int) $id . ');';
			}
			foreach ($tabToDelete as $id) {
				$query .= 'DELETE FROM droits_acces_asut
							WHERE id_asut = ' . (int) $this->idEntite . ' AND id_acces = ' . (int) $id . ';';
			}
		}
		if (!empty($query)) {
			try {
				$pg = Db::getInstance('Requeteur', $this);

				$pg->query($query);
			}
			catch (Db_Exception $e) {
				//Misc::handleException($e);
				throw $e;
			}
		}

		return true;
	}

	/*public function addAcces($id, $libelle)
	{
		$this->droitAcces[$libelle] = $id;
		return $this;
	}

	public function removeAcces($libelle)
	{
		unset($this->droitAcces[$libelle]);
		return $this;
	}

	public function isAllowedAcces($acces)
	{
		return isset($this->droitAcces[$acces]);
	}*/

	public function addAcces($id, $label)
	{
		$this->droitAcces[$id] = $label;
		return $this;
	}

	public function removeAcces($id)
	{
		unset($this->droitAcces[$id]);
		return $this;
	}

	public function isAllowedAcces($val)
	{
		if (is_numeric($val)) {
			return isset($this->droitAcces[$val]);
		}
		return in_array($val, $this->droitAcces);
	}

	/**************** Critere ******************/

	public function loadCritere()
	{
		$this->droitCritere = $this->extractCritere();
		return true;
	}

	public function extractCritere()
	{
		$tab = array();

		$query = '';
		if ($this->context == 'utilisateur') {
			$query =
					'SELECT da.id_critere, da.libelle
					FROM critere da
					INNER JOIN droits_critere_utilisateur dau
						ON dau.id_critere = da.id_critere
					WHERE dau.id_utilisateur = ' . (int) $this->idEntite . '
					UNION
					SELECT da.id_critere, da.libelle
					FROM critere da
					INNER JOIN droits_critere_asut dat
						ON dat.id_critere = da.id_critere
					WHERE dat.id_asut =
						(SELECT id_type
						FROM utilisateur
						WHERE id_utilisateur = ' . (int) $this->idEntite . ')
					;';
		}
		elseif ($this->context == 'societe') {
			$query =
					'SELECT da.id_critere, da.libelle
					FROM critere da, droits_critere_societe das
					WHERE das.id_critere = da.id_critere
					AND das.id_societe = ' . (int) $this->idEntite . '
					;';
		}
		elseif ($this->context == 'userType') {
			$query =
					'SELECT da.id_critere, da.libelle
					FROM critere da, droits_critere_asut das
					WHERE das.id_critere = da.id_critere
					AND das.id_asut = ' . (int) $this->idEntite . '
					;';
		}
		else {
			throw new InvalidArgumentException('Le contexte donné "' . $this->context . '" n\'est associé à aucun traitement');
		}

		try {
			$pg = Db::getInstance('Requeteur', $this);

			$res = $pg->query($query);
			while ($row = $pg->fetchRow($res)) {
				//$tab[$row['libelle']] = $row['id_critere'];
				$tab[$row['id_critere']] = $row['libelle'];
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return $tab;
	}

	public function saveCritere()
	{
		$oldCritereTab = array_keys($this->extractCritere());
		$tabToDelete   = array_diff($oldCritereTab, array_keys($this->droitCritere));
		$tabToInsert   = array_diff(array_keys($this->droitCritere), $oldCritereTab);

		$query = '';
		if ($this->context == 'utilisateur') {
			foreach ($tabToInsert as $id) {
				$query .= 'INSERT INTO droits_critere_utilisateur (id_utilisateur, id_critere)
							VALUES (' . (int) $this->idEntite . ', ' . (int) $id . ');';
			}
			foreach ($tabToDelete as $id) {
				$query .= 'DELETE FROM droits_critere_utilisateur
							WHERE id_utilisateur = ' . (int) $this->idEntite . ' AND id_critere = ' . (int) $id . ';';
			}
		}
		elseif ($this->context == 'societe') {
			foreach ($tabToInsert as $id) {
				$query .= 'INSERT INTO droits_critere_societe (id_societe, id_critere)
							VALUES (' . (int) $this->idEntite . ', ' . (int) $id . ');';
			}
			foreach ($tabToDelete as $id) {
				$query .= 'DELETE FROM droits_critere_societe
							WHERE id_societe = ' . (int) $this->idEntite . ' AND id_critere = ' . (int) $id . ';';
			}
		}
		elseif ($this->context == 'userType') {
			foreach ($tabToInsert as $id) {
				$query .= 'INSERT INTO droits_critere_asut (id_asut, id_critere)
							VALUES (' . (int) $this->idEntite . ', ' . (int) $id . ');';
			}
			foreach ($tabToDelete as $id) {
				$query .= 'DELETE FROM droits_critere_asut
							WHERE id_asut = ' . (int) $this->idEntite . ' AND id_critere = ' . (int) $id . ';';
			}
		}
		if (!empty($query)) {
			try {
				$pg = Db::getInstance('Requeteur', $this);

				$pg->query($query);
			}
			catch (Db_Exception $e) {
				//Misc::handleException($e);
				throw $e;
			}
		}

		return true;
	}

	/**
	 * Enregistre en base le flag "droit d'achat" sur un critère en location pour l'entité en cours d'édition
	 * @param int $idCritere id critère
	 * @param bool $val TRUE|FALSE
	 */
	public function saveAchatSurLocation($idCritere, $val)
	{
		$val = $val === true ? 't' : 'f';
		if ($this->context == 'utilisateur') {
			$sql =
'UPDATE droits_critere_utilisateur SET achatsurlocation = ' . "'$val'" . '
WHERE id_critere = ' . (int) $idCritere . ' AND id_utilisateur = ' . (int) $this->idEntite;
		}
		else if ($this->context == 'societe') {
			$sql =
'UPDATE droits_critere_societe SET achatsurlocation = ' . "'$val'" . '
WHERE id_critere = ' . (int) $idCritere . ' AND id_societe = ' . (int) $this->idEntite;
		}
		else if ($this->context == 'userType') {
			$sql =
'UPDATE droits_critere_asut SET achatsurlocation = ' . "'$val'" . '
WHERE id_critere = ' . (int) $idCritere . ' AND id_asut = ' . (int) $this->idEntite;
		}
		if (!empty($sql)) {
			$pg = Db::getInstance('Requeteur', $this);
			$pg->query($sql);
		}

		return true;
	}

	/**
	 * Renvoi TRUE si l'entité a un droit d'achat sur un critère en location, FALSE sinon
	 * @param int $idCritere id du critère
	 * @return bool
	 */
	public function isAchatSurLocation($idCritere)
	{
		if ($this->context == 'utilisateur') {
			$sql =
'SELECT achatsurlocation
FROM droits_critere_utilisateur dcu
WHERE dcu.id_critere = ' . (int) $idCritere . '
	AND dcu.id_utilisateur = ' . (int) $this->idEntite . '
UNION
SELECT achatsurlocation
FROM droits_critere_asut dca
WHERE dca.id_critere = 1 AND
	dca.id_asut = (SELECT id_type FROM utilisateur u
		WHERE u.id_utilisateur = ' . (int) $this->idEntite . ')';
		}
		else if ($this->context == 'societe') {
			$sql =
'SELECT achatsurlocation FROM droits_critere_societe
WHERE id_critere = ' . (int) $idCritere . ' AND id_societe = ' . (int) $this->idEntite;
		}
		else if ($this->context == 'userType') {
			$sql =
'SELECT achatsurlocation FROM droits_critere_asut
WHERE id_critere = ' . (int) $idCritere . ' AND id_asut = ' . (int) $this->idEntite;
		}
		if (!empty($sql)) {
			$pg = Db::getInstance('Requeteur', $this);
			$res = $pg->query($sql);
			while ($row = $pg->fetchRow($res)) {
				if ($row['achatsurlocation'] == 't') {
					return true;
				}
			}
		}

		return false;
	}

	/*public function addCritere($id, $libelle)
	{
		$this->droitCritere[$libelle] = $id;
		return $this;
	}

	public function removeCritere($libelle)
	{
		unset($this->droitCritere[$libelle]);
		return $this;
	}

	public function isAllowedCritere($acces)
	{
		return isset($this->droitCritere[$acces]);
	}*/

	public function addCritere($id, $label)
	{
		$this->droitCritere[$id] = $label;
		return $this;
	}

	public function removeCritere($id)
	{
		unset($this->droitCritere[$id]);
		return $this;
	}

	public function isAllowedCritere($val)
	{
		if (is_numeric($val)) {
			return isset($this->droitCritere[$val]);
		}
		return in_array($val, $this->droitCritere);
	}

	/************************************ DESSIN ENREGISTREMENT ******************/

	public function getDroitsDE()
	{
		return $this->droitDE;
	}

	public function loadDE()
	{
		$this->droitDE = $this->extractDE();
		return true;
	}

	public function extractDE()
	{
		$tab = array();

		if ($this->context == 'utilisateur') {
			$query =
					'(SELECT da.id_champ, da.libelle, da.ordre
					FROM champ da
					INNER JOIN droits_de_utilisateur dau
						ON dau.id_champ = da.id_champ
					WHERE dau.id_utilisateur = ' . (int) $this->idEntite . '
					UNION
					SELECT da.id_champ, da.libelle, da.ordre
					FROM champ da
					INNER JOIN droits_de_asut dat
						ON dat.id_champ = da.id_champ
					WHERE dat.id_asut =
						(SELECT id_type
						FROM utilisateur
						WHERE id_utilisateur = ' . (int) $this->idEntite . ')
					) ORDER BY ordre;';
		}
		elseif ($this->context == 'societe') {
			$query =
					'SELECT da.id_champ, da.libelle
					FROM champ da, droits_de_societe das
					WHERE das.id_champ = da.id_champ
					AND das.id_societe = ' . (int) $this->idEntite .'
					ORDER BY da.ordre;';
		}
		elseif ($this->context == 'userType') {
			$query =
					'SELECT da.id_champ, da.libelle
					FROM champ da, droits_de_asut das
					WHERE das.id_champ = da.id_champ
					AND das.id_asut = ' . (int) $this->idEntite . '
					ORDER BY da.ordre;';
		}
		else {
			throw new InvalidArgumentException('Le contexte donné "' . $this->context . '" n\'est associé à aucun traitement');
		}

		try {
			$pg = Db::getInstance('Requeteur', $this);

			$res = $pg->query($query);
			while ($row = $pg->fetchRow($res)) {
				//$tab[$row['libelle']] = $row['id_champ'];
				$tab[$row['id_champ']] = $row['libelle'];
			}
		}
		catch (Db_Exception $e) {
			//Misc::handleException($e);
			throw $e;
		}

		return $tab;
	}

	public function saveDE()
	{
		$oldDETab	 = array_keys($this->extractDE());
		$tabToDelete = array_diff($oldDETab, array_keys($this->droitDE));
		$tabToInsert = array_diff(array_keys($this->droitDE), $oldDETab);

		$query = '';
		if ($this->context == 'utilisateur'){
			foreach ($tabToInsert as $id) {
				$query .= 'INSERT INTO droits_de_utilisateur (id_utilisateur,id_champ)
							VALUES (' . (int) $this->idEntite . ', ' . (int) $id . ');';
			}
			foreach ($tabToDelete as $id) {
				$query .= 'DELETE FROM droits_de_utilisateur
							WHERE id_utilisateur = ' . (int) $this->idEntite . ' AND id_champ = ' . (int) $id . ';';
			}
		}
		elseif($this->context == 'societe'){
			foreach ($tabToInsert as $id) {
				$query .= 'INSERT INTO droits_de_societe (id_societe,id_champ)
							VALUES (' . (int) $this->idEntite . ', ' . (int) $id . ');';
			}
			foreach ($tabToDelete as $id) {
				$query .= 'DELETE FROM droits_de_societe
							WHERE id_societe = ' . (int) $this->idEntite . ' AND id_champ = ' . (int) $id . ';';
			}
		}
		elseif ($this->context == 'userType'){
			foreach ($tabToInsert as $id) {
				$query .= 'INSERT INTO droits_de_asut (id_asut,id_champ)
							VALUES (' . (int) $this->idEntite . ', ' . (int) $id . ');';
			}
			foreach ($tabToDelete as $id) {
				$query .= 'DELETE FROM droits_de_asut WHERE id_asut = ' . (int) $this->idEntite . ' AND id_champ = ' . (int) $id . ';';
			}
		}
		if (!empty($query)) {
			try {
				$pg = Db::getInstance('Requeteur', $this);

				$pg->query($query);
			}
			catch (Db_Exception $e) {
				//Misc::handleException($e);
				throw $e;
			}
		}

		return true;
	}

	/*public function addDE($id, $libelle)
	{
		$this->droitDE[$libelle] = $id;
	}

	public function removeDE($libelle)
	{
		unset($this->droitDE[$libelle]);
	}

	public function isAllowedDE($acces)
	{
		return isset($this->droitDE[$acces]);
	}*/

	public function addDE($id, $label)
	{
		$this->droitDE[$id] = $label;
		return $this;
	}

	public function removeDE($id)
	{
		unset($this->droitDE[$id]);
		return $this;
	}

	public function isAllowedDE($val)
	{
		if (is_numeric($val)) {
			return isset($this->droitDE[$val]);
		}
		return in_array($val, $this->droitDE);
	}

	/************************************ GETTERS ET SETTERS ********************/

	public function getDroitsAcces()
	{
		return $this->droitAcces;
	}

	public function getDroitsCritere()
	{
		return $this->droitCritere;
	}

	public function setIdEntite($val)
	{
		$this->idEntite = $val;
		return $this;
	}

	public function setContext($val)
	{
		$this->context = $val;
		return $this;
	}

	/******************************** Mise à jour des droits ******************************/

	public static function updateAcces(Admin_Model_Acces_Interface $entity, $tabNewRights)
	{
		return self::_updateRights($entity, $tabNewRights, 'Acces');
	}

	public static function updateCritere(Admin_Model_Acces_Interface $entity, $tabNewRights)
	{
		return self::_updateRights($entity, $tabNewRights, 'Critere');
	}

	public static function updateDe(Admin_Model_Acces_Interface $entity, $tabNewRights)
	{
		return self::_updateRights($entity, $tabNewRights, 'DE');
	}

	/*private static function _updateRights(Admin_Model_Acces_Interface $entity, $tabNewRights, $type)
	{
		$entity->{"load$type"}();
		$tabOldRights = $entity->{"getDroits$type"}();

		$getListMeth = "getList$type";
		$tabRights = self::$getListMeth();

		foreach ($tabNewRights as $key => $value) {
			$label = array_search($key, $tabRights);
			if ($value == 1 && !in_array($key, $tabOldRights)) {
				$entity->{"add$type"}($key, $label);
			}
			elseif ($value == 0 && in_array($key, $tabOldRights)) {
				$entity->{"remove$type"}($label);
			}
		}
		return $entity->{"save$type"}();
	}*/

	private static function _updateRights(Admin_Model_Acces_Interface $entity, $tabNewRights, $type)
	{
		$entity->{"load$type"}();
		$tabOldRights = $entity->{"getDroits$type"}();

		$getListMeth = "getList$type";
		$tabRights = self::$getListMeth();

		foreach ($tabNewRights as $id => $value) {
			$label = $tabRights[$id];
			if ($value == 1 && !isset($tabOldRights[$id])) {
				$entity->{"add$type"}($id, $label);
			}
			elseif ($value == 0 && isset($tabOldRights[$id])) {
				$entity->{"remove$type"}($id);
			}
		}

		return $entity->{"save$type"}();
	}

	/****************************************** Listes ***************************************/

	public static function getListAcces()
	{
		$tab = array();

		$pg = Db::getInstance('Requeteur');
		$query = 'SELECT id_acces, code FROM acces da ORDER BY code;';
		$res = $pg->query($query);
		while($row = $pg->fetchRow($res)) {
			//$tab[$row['libelle']] = $row['id_acces'];
			$tab[$row['id_acces']] = $row['code'];
		}

		return $tab;
	}

	public static function getListCritere()
	{
		$tab = array();

		$pg = Db::getInstance('Requeteur');
		$query = 'SELECT id_critere, libelle FROM critere ORDER BY libelle;';
		$res = $pg->query($query);
		while($row = $pg->fetchRow($res)) {
			//$tab[$row['libelle']] = $row['id_critere'];
			$tab[$row['id_critere']] = $row['libelle'];
		}

		return $tab;
	}

	public static function getListDE()
	{
		$tab = array();

		$pg = Db::getInstance('Requeteur');
		$query = 'SELECT id_champ, libelle FROM champ ORDER BY libelle;';
		$res = $pg->query($query);
		while($row = $pg->fetchRow($res)) {
			//$tab[$row['libelle']] = $row['id_champ'];
			$tab[$row['id_champ']] = $row['libelle'];
		}

		return $tab;
	}
}
