<?php
/**
 * Classe effectuant le comptage d'une opération et la génération de la commande
 *
 * @package Requeteur\Model\Operation
 */
class Comptage_Model_Operation_Requeteur
{
	/**
	 * @var Comptage_Model_Operation
	 */
	protected $_op;

	/**
	 * @var Db_Oracle
	 */
	protected $_db;
	protected $_table;
	protected $_cmdTable;
	protected $_daoCrit;

	/**
	 * Résultat du comptage
	 * @var int
	 */
	protected $_count;

	/**
	 * Séparateur de valeurs pour un critère unitaire
	 * @var string
	 */
	const SEPARATEUR_VALEURS_UNITAIRE = '-';

	const ID_CRITERE_MULTICONTACT = 24;
	const ID_CRITERE_TELOBLIG = 12;

	public function __construct(Comptage_Model_Operation $op)
	{
		$this->_op = $op;
		$this->_db = Db::getInstance('Megabase');

		if ($this->_op->getCibleType() == 'part') {
			$this->_table = 'tpart';
			$this->_cmdTable = 'cmd_part';
		}
		else if ($this->_op->getCibleType() == 'pro') {
			$this->_table = 'tprof';
			$this->_cmdTable = 'cmd_prof';
		}
		else {
			throw new \RuntimeException('Type cible invalide: ' . $this->_op->getCibleType());
		}
	}

	/**
	 * Retourne le comptage associé à l'opération
	 * @throws RuntimeException si le type de cible n'est pas géré
	 * @return int
	 */
	public function getComptage($force = false)
	{
		if (isset($this->_count) && !$force) {
			return $this->_count;
		}

		$this->_emptyTempCodes();

		$this->_daoCrit = Comptage_Model_Critere_Dao::getInstance()->setRegistryEnabled(true);
		// TODO: Ici gérer le multi-cible
		$query = $this->_cibleBuildCount($this->_op->getCurrentCible());
		$this->_daoCrit->setRegistryEnabled(false);

		Logger::log($query, 'sql');

		$res = $this->_db->query($query);
		$row = $this->_db->fetchRow($res);

		$this->_emptyTempCodes();

		return $row['COUNT'];
	}

	/**
	 * Créé la commande dans la base Oracle
	 * @param int $quantity quantité commandée
	 * @return bool
	 */
	public function createCommande($quantity)
	{
		$this->_emptyCmd();
		$this->_emptyTempCodes();

		$this->_daoCrit = Comptage_Model_Critere_Dao::getInstance()->setRegistryEnabled(true);
		$query = $this->_buildCmd($quantity);
		$this->_daoCrit->setRegistryEnabled(false);

		$sql = 'INSERT INTO ' . $this->_cmdTable . ' ' . $query;
		Logger::log($sql, 'sql');

		$this->_db->query($sql);
		$this->_db->commit();

		$this->_emptyTempCodes();

		return true;
	}

	/**
	 * Retourne le resultset des lignes d'adresses associées à la commande
	 * @param array $tabChamps liste des champs à extraire
	 * @return array resultset tableau dont le 1er élément est l'objet Db de connexion à la base
	 * 							le 2ème élément la ressource renvoyée par Db::query
	 */
	public function getCommandeResultset($tabChamps)
	{
		$query = new Db_QueryBuilder();
		$query->select($tabChamps);
		$query->from($this->_table);
		$query->where('id_pers IN (SELECT id_pers FROM ' . $this->_cmdTable . ' WHERE id_operation = ' . (int) $this->_op->getId() . ')');
		Logger::log($query->getQuery());
		$res = $this->_db->query($query->getQuery());

		return array($this->_db, $res);
	}

	/**
	 * Construit la requête de sélection des lignes pour la commande
	 * @param int $quantity
	 * @return string
	 */
	protected function _buildCmd($quantity)
	{
		// TODO: ici gérer le multi-cible

		if ($this->_op->getCibleType() == 'part') {
			$sql = $this->_cibleBuildCmdPart($this->_op->getCurrentCible());
		}
		else if ($this->_op->getCibleType() == 'pro') {
			$sql = $this->_cibleBuildCmdPro($this->_op->getCurrentCible());
		}
		$sql =
'SELECT * FROM (' . "\n" .
	$sql . '
	ORDER BY DBMS_RANDOM.VALUE)
WHERE ROWNUM <= ' . (int) $quantity;

		return $sql;
	}

	/**
	 * Construit la requête de récupération des particuliers pour une cible
	 * @param Comptage_Model_Cible $cible
	 * @return string
	 */
	protected function _cibleBuildCmdPart(Comptage_Model_Cible $cible)
	{
		$query = new Db_QueryBuilder();

		$champFoyer = 'id_postal';
		$champGroupBy = 'id_postal';
		$champSource = 'default_source_pers';
		if (isset($cible[self::ID_CRITERE_TELOBLIG])) {
			$champFoyer = 'id_telmkt';
			$champGroupBy = 'id_telmkt';
			$champSource = 'default_source_tel';
		}
		if (isset($cible[self::ID_CRITERE_MULTICONTACT])) {
			$champGroupBy = '';
		}

		$query->from($this->_table);
		$this->_cibleBuildWhere($cible, $query);

		if (empty($champGroupBy)) {
			$query->select(array($this->_op->getId(), $champFoyer, 'id_pers', $champSource));
			$sql = $query->getQuery();
		}
		else {

			$query->select('MIN(id_pers)');
			$query->groupBy($champGroupBy);

			$query2 = new Db_QueryBuilder();
			$query2->select(array($this->_op->getId(), $champFoyer, 'id_pers', $champSource));
			$query2->from($this->_table);
			$query2->where('id_pers IN (' . $query->getQuery() . ')');
			$sql = $query2->getQuery();
		}

		return $sql;
	}

	/**
	 * Construit la requête de récupération des professionnels pour une cible
	 * @param Comptage_Model_Cible $cible
	 * @return string
	 */
	protected function _cibleBuildCmdPro(Comptage_Model_Cible $cible)
	{
		$query = new Db_QueryBuilder();

		$query->select(array($this->_op->getId(), 'id_pers'));
		$query->from($this->_table);
		$this->_cibleBuildWhere($cible, $query);

		return $query->getQuery();
	}

	/**
	 * Retourne la requête associée au comptage
	 * @param Comptage_Model_Cible $cible
	 * @return string
	 */
	protected function _cibleBuildCount(Comptage_Model_Cible $cible)
	{
		$query = new Db_QueryBuilder();

		// Détermine le champ sur lequel on fait le comptage (dépend de la cible et de divers critères)
		if ($this->_op->getCibleType() == 'part') {
			if (isset($cible[self::ID_CRITERE_MULTICONTACT])) {
				$champCount = 'id_pers';
			}
			else if (isset($cible[self::ID_CRITERE_TELOBLIG])) {
				$champCount = 'id_telmkt';
			}
			else {
				$champCount = 'id_postal';
			}
		}
		else if ($this->_op->getCibleType() == 'pro') {
			$champCount = 'id_pers';
		}

		$query->select("COUNT(DISTINCT $champCount) AS count");
		$query->from($this->_table);
		$this->_cibleBuildWhere($cible, $query);

		return $query->getQuery();
	}

	/**
	 * Construit la clause where des requêtes suivant la cible
	 * @param Comptage_Model_Cible $cible
	 * @param Db_QueryBuilder $query
	 * @return bool
	 */
	protected function _cibleBuildWhere(Comptage_Model_Cible $cible, Db_QueryBuilder $query)
	{
		/* @var $cibleCrit Comptage_Model_Cible_Critere */
		foreach ($cible as $idCrit => $cibleCrit) {
			$valeurs = array_keys($cibleCrit->tabValeurs);
			$crit = $this->_daoCrit->get($idCrit);

			if (!empty($cibleCrit->tabValeurs)) {
				$filter = $this->_critereBuildFilter($cible, $crit, $cibleCrit->tabValeurs);

				if (!empty($filter)) {
					$critType = $crit->getType();
					if ($critType['code'] == 'geo') {
						$query->orWhere($filter);
					}
					else {
						$query->andWhere($filter);
					}
				}
			}
		}

		if (!$query->getClause('andWhere') && !$query->getClause('orWhere')) {
			throw new \RuntimeException('Requête sans clause where!');
		}

		return true;
	}

	/**
	 * Retourne le filtre pour un critère donné
	 * @param Comptage_Model_Cible $cible
	 * @param Comptage_Model_Critere $crit
	 * @param array $valeurs
	 * @throws RuntimeException si le type de valeur assocé au critère n'est pas géré
	 * @return string
	 */
	protected function _critereBuildFilter(Comptage_Model_Cible $cible, Comptage_Model_Critere $crit, array $valeurs)
	{
		$filter = '';
		$exp = new Db_QueryBuilder_Expression();

		switch ($crit->getTypeValeur()) {
			case 'unitaire':
				$valeur = key($valeurs);
				// Si la valeur utilisateur correspond à plusieurs valeurs critère (valeurs séparées par |)
				// gère le critère comme une liste
				if (strpos($valeur, self::SEPARATEUR_VALEURS_UNITAIRE) !== false) {
					$valeurs = explode(self::SEPARATEUR_VALEURS_UNITAIRE, $valeur);
					$valeurs = array_combine($valeurs, $valeurs);
					$filter = $this->_listeBuildFilter($cible, $crit, $valeurs);
				}
				// Sinon traitement normal, valeur unique
				else {
					$valeur = $crit->getTypeDonnee() == 'numeric' ? (int) $valeur :
																	$this->_db->escape_string_add_quotes($valeur);
					if ($crit->getCodeSql()) {
						$filter = str_replace('%value%', $valeur, $crit->getCodeSql());
					}
					else {
						$filter = $exp->eq($crit->getChampSql(), $valeur);
					}
				}
				break;

			case 'liste':
				$filter = $this->_listeBuildFilter($cible, $crit, $valeurs);
				break;

			case 'intervalle':
				if ($crit->getCode() == 'age') {
					$filter = $this->_ageBuildFilter($cible, $crit, $valeurs);
				}
				else {
					$filter = $this->_intervalleBuildFilter($cible, $crit, $valeurs);
				}
				break;

			case 'switch':
				if ($crit->getCodeSql()) {
					$filter = $crit->getCodeSql();
				}
				else {
					$filter = $exp->notNull($crit->getChampSql());
				}
				break;

			default:
				throw  new \RuntimeException('Type valeur non géré: ' . $crit->getTypeValeur());
				break;
		}

		$filter = str_replace(array('%table_adresse%', '%table_commande%'),
								array($this->_table, $this->_cmdTable), $filter);
		$filter .= ' /* ' . $crit->getLibelle() . ' */';

		return $filter;
	}

	/**
	 * Retourne le filtre pour un intervalle de valeurs
	 * @param Comptage_Model_Cible $cible
	 * @param Comptage_Model_Critere $crit
	 * @param array $valeurs
	 * @return string
	 */
	protected function _intervalleBuildFilter(Comptage_Model_Cible $cible, Comptage_Model_Critere $crit, array $valeurs)
	{
		$filter = '';

		$codeSql = $crit->getCodeSql();
		$champSql = $crit->getChampSql();

		$min = '';
		if (isset($valeurs['min'])) {
			if ($crit->getTypeDonnee() == 'numeric') {
				$min = (int) key($valeurs['min']);
			}
			else {
				$min = $this->_db->escape_string_add_quotes(key($valeurs['min']));
			}
		}
		$max = '';
		if (isset($valeurs['max'])) {
			if ($crit->getTypeDonnee() == 'numeric') {
				$max = (int) key($valeurs['max']);
			}
			else {
				$max = $this->_db->escape_string_add_quotes(key($valeurs['max']));
			}
		}

		if (!empty($codeSql) && is_array($codeSql)) {
			if ($min && $max) {
				$filter = str_replace(array('%min%', '%max%'), array($min, $max), $codeSql['both']);
			}
			else if ($min) {
				$filter = str_replace('%min%', $min, $codeSql['min']);
			}
			else if ($max) {
				$filter = str_replace('%max%', $max, $codeSql['max']);
			}
		}
		else {
			if ($min && $max) {
				$filter = "($champSql BETWEEN $min AND $max)";
			}
			else if ($min) {
				$filter = "$champSql >= $min";
			}
			else if ($max) {
				$filter = "$champSql <= $max";
			}
		}

		return $filter;
	}

	/**
	 * Retourne le filtre pour une liste de valeurs
	 * Insère les codes dans une table temporaire si le nombre de valeurs dépasse la limite Application::getInstance()->DB_ORACLE_IN_LIMIT
	 * @param Comptage_Model_Cible $cible
	 * @param Comptage_Model_Critere $crit
	 * @param array $valeurs liste de valeurs
	 * @return string
	 */
	protected function _listeBuildFilter(Comptage_Model_Cible $cible, Comptage_Model_Critere $crit, array $valeurs)
	{
		$filter = '';

		$valeurs = array_keys($valeurs);
		// Si le nombre d'éléments dans la liste dépasse la limite max pour un IN
		if (count($valeurs) > Application::getInstance()->DB_ORACLE_IN_LIMIT) {
			// Insertion des éléments dans la table temporaire
			$this->_insertTempCodes($cible->getId(), $crit->getId(), $valeurs);
			// Filtre sur le contenu de la table temporaire
			$sql= 'SELECT code FROM tmp_in WHERE id_critere = ' . (int) $crit->getId() . ' AND id_cible = ' . (int) $cible->getId();
			if ($crit->getCodeSql()) {
				$filter = str_replace('%value%', $sql, $crit->getCodeSql());
			}
			else {
				$filter = $crit->getChampSql() . ' IN (' . $sql . ')';
			}
		}
		else {
			if ($crit->getTypeDonnee() == 'numeric') {
				foreach ($valeurs as $i => $valeur) {
					$valeurs[$i] = (int) $valeur;
				}
			}
			else {
				foreach ($valeurs as $i => $valeur) {
					$valeurs[$i] = $this->_db->escape_string_add_quotes($valeur);
				}
			}
			if ($crit->getCodeSql()) {
				$filter = str_replace('%value%', implode(', ', $valeurs), $crit->getCodeSql());
			}
			else {
				$filter = $crit->getChampSql() . ' IN (' . implode(', ', $valeurs) . ')';
			}
		}

		return $filter;
	}

	/**
	 * Retourne le filtre sur l'âge
	 * @param Comptage_Model_Cible $cible
	 * @param Comptage_Model_Critere $crit
	 * @param array $valeurs
	 * @return string
	 */
	protected function _ageBuildFilter(Comptage_Model_Cible $cible, Comptage_Model_Critere $crit, array $valeurs)
	{
		// Fait correspondre une puissance de 2 à chaque tranche
		static $tranches = array(	0  => 1 /*tranche_age_00_20*/,
									20 => 2 /*tranche_age_21_30*/,
									30 => 4 /*tranche_age_31_40*/,
									40 => 8 /*tranche_age_41_50*/,
									50 => 16 /*tranche_age_51_60*/,
									60 => 32 /*tranche_age_61_70*/,
									70 => 64 /*tranche_age_71_110*/);

		$min = isset($valeurs['min']) ? (int) key($valeurs['min']) : null;
		$max = isset($valeurs['max']) ? (int) key($valeurs['max']) : null;
		$myTranches = getSlices($tranches, $min, $max);

		$byte = 0;
		foreach ($myTranches as $bit) {
			$byte = $byte | $bit;
		}

		$filter = 'BITAND(' . $crit->getChampSql() . ', ' . $byte . ') > 0';
		return $filter;
	}

	/**
	 * Insère des codes dans la table temporaire
	 * Appelle la procédure stockée ora_req_pkg.insert_code
	 * @param int $idCible
	 * @param int $idCritere
	 * @param array $list liste de codes
	 */
	protected function _insertTempCodes($idCible, $idCritere, $list)
	{
		$idOp = $this->_op->getId();

		$res = $this->_db->parse('ora_req_pkg.insert_code(:id_critere,:code_in,:id_cible,:id_operation)');
		$this->_db->bind($res, ':id_critere', $idCritere, -1, SQLT_INT);
		$this->_db->bind_array($res, ':code_in', $list, count($list), 10, SQLT_CHR);
		$this->_db->bind($res, ':id_cible', $idCible, -1, SQLT_INT);
		$this->_db->bind($res, ':id_operation', $idOp, -1, SQLT_INT);
		$res = $this->_db->execute($res);

		return true;
	}

	/**
	 * Vide la table temporaire des codes
	 */
	protected function _emptyTempCodes()
	{
		$sql = 'DELETE FROM tmp_in WHERE id_operation = ' . (int) $this->_op->getId();
		$this->_db->query($sql);
		$this->_db->commit();

		return true;
	}

	/**
	 * Supprime les commandes déjà associées à l'opération en cours
	 */
	protected function _emptyCmd()
	{
		$sql = 'DELETE FROM ' . $this->_cmdTable .' WHERE id_operation = ' . (int) $this->_op->getId();
		$this->_db->query($sql);
		$this->_db->commit();

		return true;
	}

}