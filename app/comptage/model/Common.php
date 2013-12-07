<?php
/**
 * Classe contenant des méthodes du modèle qui ne sont pas directement liées à une entité
 *
 * @package Requeteur\Model
 */
class Comptage_Model_Common
{
	/**
	 * Retourne le libellé d'un statut
	 * @param string $idStatut id statut
	 * @return string libellé
	 */
	public static function getStatutLibelle($idStatut)
	{
		static $tabLibelles = array();

		if (!isset($tabLibelles[$idStatut])) {
			/* @var $db Db_Postgre */
			$db = Db::getInstance('Requeteur');
			$sql = 'SELECT id_statut, libelle FROM statut WHERE id_statut = ' . $db->escape_string_add_quotes($idStatut);
			$res = $db->query($sql);
			$row = $db->fetchRow($res);
			if (!empty($row)) {
				$tabLibelles[$row['id_statut']] = $row['libelle'];
			}
		}
		if (isset($tabLibelles[$idStatut])) {
			return $tabLibelles[$idStatut];
		}
		return false;
	}

	/**
	 * Construit un arbre de données,
	 * Retourne des tableaux imbriqués:
	 * Seules les feuilles ont un "id", les noeuds ont des "children" et chaque élément a un "label"
	 * @param string $critere code du critère
	 * @param int $level niveau d'imbrications (ex: 3 pour les codes rubriques)
	 * @return array arbre
	 */
	public static function getTree($critere, $level)
	{
		$db = Db::getInstance('Megabase');
		$tableName = 't' . strtolower($critere);
		return self::_getTreeRecursive($db, $tableName, $level-1);
	}

	private static function _getTreeRecursive($db, $tableName, $level, $id = null)
	{
		// niveau le plus imbriqué (feuilles)
		if ($level == 0) {
			$tab = array();
			$sql = "SELECT code, libelle FROM $tableName WHERE code IS NOT NULL";
			if ($id !== null) {
				$sql .= ' AND code_' . ($level + 1) . ' = \'' . $db->escape_string($id) . '\'';
			}
			$sql .= ' ORDER BY code';
			$res = $db->query($sql);
			while ($row = $db->fetchRow($res)) {
				$tab[] = array('id' => $row['CODE'],
								'label' => $row['CODE'] . ' - ' . $row['LIBELLE']);
			}
			return $tab;
		}
		// noeuds
		else {
			$tab = array();
			$code = "code_$level";
			$label = "libelle_$level";
			$sql = "SELECT $code AS CODE, $label AS LIBELLE FROM $tableName WHERE $code IS NOT NULL";
			if ($id !== null) {
				$sql .= " AND code_" . ($level + 1) . " = '" . $db->escape_string($id) . "'";
			}
			$sql .= " GROUP BY $code, $label ORDER BY $code";
			$res = $db->query($sql);
			// construit le noeud en cours, ajoute les noeuds enfants
			while ($row = $db->fetchRow($res)) {
				$tab[] = array('label' => $row['CODE'] . ' - ' . $row['LIBELLE'],
								'children' => self::_getTreeRecursive($db, $tableName, $level-1, $row['CODE']));
			}
			return $tab;
		}
	}

	/**
	 * Retourne un "arbre" contenant toutes les campagnes terminées.
	 * Les campagnes sont classées sous la société du contact associé à l'opération
	 * @see Comptage_Model_Common::getTree
	 * @return array
	 */
	public static function getTreeRepoussoir()
	{
		$db = Db::getInstance('Requeteur');
		$tab = array();

		$sql =
"SELECT
ct.id_contact,
ct.societe,
id_operation,
to_char(c.date_creation, 'DD/MM/YYYY') || ' - ' || substr(c.libelle, 0, 30) || ' - ' || u.prenom || ' ' || u.nom AS label
FROM cpg_operation o
INNER JOIN cpg_campagne c ON c.id_campagne = o.id_campagne
INNER JOIN contact ct ON ct.id_contact = o.id_contact
INNER JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
WHERE o.id_statut = 'STA05'
ORDER BY ct.societe, c.date_creation, c.libelle, o.id_operation";
		$res = $db->query($sql);
		while ($row = $db->fetchRow($res)) {
			if (!isset($tab[$row['societe']])) {
				$tab[$row['societe']] = array(	'label' => $row['societe'],
												'children' => array());
			}
			$tab[$row['societe']]['children'][] = array('id' => $row['id_operation'],
														'label' => $row['label']);
		}

		return array_values($tab);
	}

	/**
	 * Effectue une recherche dans la Megabase et retourne la liste d'éléments correspondant
	 * @param string $critere code du critère
	 * @param string $label libellé à rechercher
	 * @return array tableau d'éléments [{id => code, label => libellé}]
	 */
	public static function lookup($critere, $label)
	{
		$ora = Db::getInstance('Megabase');
		$tableName = 't' . strtolower($critere);

		$list = array();

		$label = $ora->escape_string(Misc::removeAccent($label));
		$req = "SELECT code, libelle
				FROM $tableName
				WHERE UPPER(CONVERT(libelle, 'us7ascii')) LIKE
						CONVERT('%" . strtoupper($label) . "%', 'us7ascii')
				ORDER BY INSTR(libelle, CONVERT('" . strtoupper($label) . "', 'us7ascii')), libelle";
		$res = $ora->query($req);
		while ($row = $ora->fetchRow($res)) {
			$list[] = array('id' => $row['CODE'],
							'label' => $row['LIBELLE']);
		}

		return $list;
	}

	/**
	 * Filtre une liste de codes utilisateurs avec le contenu de la Megabase (appelée pour un import de codes)
	 * @param string $critere nom du critere
	 * @param array $list liste de codes
	 * @return array tableau de libellés indexés sur le code
	 */
	public static function filter($critere, $list)
	{
		if (!empty($list)) {
			/* @var $ora Db_Oracle */
			$ora = Db::getInstance('Megabase');

			$cnt = count($list);
			// Filtre les données avec une requête SELECT WHERE code IN ()
			if ($cnt <= Application::getInstance()->DB_ORACLE_IN_LIMIT) {
				$newList = array();

				foreach ($list as $i => $val) {
					$list[$i] = $ora->escape_string_add_quotes($val);
				}
				$tableName = 't' . strtolower($critere);
				$sql = 'SELECT code, libelle FROM ' . $tableName . ' WHERE code IN (' . implode(', ', $list) . ')';
				$res = $ora->query($sql);
				while ($row = $ora->fetchRow($res)) {
					$newList[$row['CODE']] = $row['LIBELLE'];
				}

				return $newList;
			}
			// Appelle la procédure stockée de filtrage des données, si le nombre de codes dépasse la limite
			else {
				$res = $ora->parse('ora_req_pkg.filter_code(:type_code,:code_in,:code_out,:lib_out)');
				$ora->bind($res, ':type_code', $critere, -1, SQLT_CHR);
				$ora->bind_array($res, ':code_in', $list, $cnt, 6, SQLT_CHR);
				$ora->bind_array($res, ':code_out', $listCode, $cnt, 10, SQLT_CHR);
				$ora->bind_array($res, ':lib_out', $listLabel, $cnt, 255, SQLT_CHR);
				$res = $ora->execute($res);

				return array_combine($listCode, $listLabel);
			}
		}
	}

}