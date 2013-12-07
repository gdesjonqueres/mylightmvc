<?php
/**
 * Script réalisant la création d'une commande et la génération du fichier d'extraction
 * prend en paramètre:
 * $argv[1]: id de la campagne
 * $argv[2]: id de l'opération
 */

if (empty($argc) || $argc != 3) {
	throw new \InvalidArgumentException('Arguments incorrects');
}
$idCpg = $argv[1];
$idOp = $argv[2];

// utilise la version cli du bootstrap
require_once realpath(dirname(__FILE__) . '/../inc') . '/bootstrap-exec.inc.php';

$app = Application::getInstance();

// Définit un gestionnaire d'exception
$oldExceptionHandler = set_exception_handler(function(Exception $e) {
	// écrit dans le fichier de log des extractions
	if (isset($GLOBALS['logFp']) && !empty($GLOBALS['logFp'])) {
		$fp = $GLOBALS['logFp'];
		fwrite($fp, "\n" . date('d/m/Y H:i:s') . ': Arret de l\'extraction suite a l\'exception suivante: ' . $e->getMessage() . "\r\n");
		fwrite($fp, "\n" . 'cf ' . ini_get('error_log') . ' pour plus de details');
		fclose($fp);
	}
	// log l'exception dans le log d'erreur et envoie un mail
	logException($e);
});

// Définit un gestionnaire d'erreur
$oldErrorHandler = set_error_handler(function($errno, $errstr, $errfile, $errline) {
	// écrit dans le fichier de log des extractions
	if (isset($GLOBALS['logFp']) && !empty($GLOBALS['logFp'])) {
		$fp = $GLOBALS['logFp'];
		fwrite($fp, "\n" . date('d/m/Y H:i:s') . ': Arret de l\'extraction suite a l\'ereur suivante: ' . $errstr . "\r\n");
		fwrite($fp, "\n" . 'cf ' . ini_get('error_log') . ' pour plus de details');
		fclose($fp);
	}
	return false; // exécute le gestionnaire d'erreur par défaut
}, ini_get('error_reporting'));


// Récupère l'opération
$etape = 1;
$logFp = fopen($app->PATH_FILES . '/files.log', 'ab');
myLog($logFp, "--------------------------------------------------------------");
myLog($logFp, "Charge l'opération $idOp", $etape++);
$operation = Comptage_Model_Operation_Dao::getInstance()->getFullOperation($idOp);

// Récupère l'extraction
myLog($logFp, "Récupère l'extraction associée", $etape++);
$extraction = $operation->getExtraction();
$fileSetup = $extraction->getFichier();
// Liste des champs à extraire
$tabChamps = Comptage_Model_Extraction_Dao::getInstance()->getListChampsSql($extraction);
// Ajoute l'id dataneo
$tabChamps['ID_PERS'] = 'Id Dataneo';

// Génère la commande
$requeteur = new Comptage_Model_Operation_Requeteur($operation);

myLog($logFp, "Commence à créer la commande", $etape++);
$requeteur->createCommande($extraction->getQuantite());
myLog($logFp, "La commande est créée");

myLog($logFp, "Récupère les lignes d'adresses", $etape++);
$resultset = $requeteur->getCommandeResultset(array_keys($tabChamps));

// Génère le fichier
myLog($logFp, "Commence à générer le fichier " . $fileSetup->type, $etape++);
if ($fileSetup->type == 'excel') {
	if ($fileSetup->options['format'] == 'excel2007') {
		$ext = 'xlsx';
	}
	else {
		$ext = 'xls';
	}
	$fileName = sprintf($app->FILES_PATTERN, $operation->getId(), $ext);
	createExcel($app->PATH_FILES . '/' . $fileName, $tabChamps, $resultset, $fileSetup->options['format']);
}
else if ($fileSetup->type == 'txt') {
	$fileName = sprintf($app->FILES_PATTERN, $operation->getId(), 'txt');
	createTxt($app->PATH_FILES . '/' . $fileName, $tabChamps, $resultset, $fileSetup->options['separator']);
}
myLog($logFp, "Le fichier $fileName est généré");

// Met à jour le nom du fichier
myLog($logFp, "Met à jour l'extraction", $etape++);
$fileSetup->nom = $fileName;
Comptage_Model_Extraction_Dao::getInstance()->save($extraction);

// Termine l'opération
myLog($logFp, "Met à jour l'opération et la campagne", $etape++);
$operation->setStatut('STA05');
Comptage_Model_Operation_Dao::getInstance()->save($operation);
// Termine la campagne
Comptage_Model_Campagne_Dao::getInstance()->save(Comptage_Model_Campagne_Dao::getInstance()->get($idCpg)->setStatut('STA05'));

myLog($logFp, "Log:\n" . print_r(Logger::getLogEntries(), true));

fclose($logFp);

restore_error_handler($oldErrorHandler);
restore_exception_handler($oldExceptionHandler);


/**
 * Ecrit dans le fichier de log des extractions
 * @param unknown $fp
 * @param unknown $value
 * @param string $step
 */
function myLog($fp, $value, $step = NULL)
{
	static $on = '07';
	if ($step) {
		$step = $step < 10 ? "0$step" : $step;
		$step = " Etape $step/$on ";
	}

	fwrite($fp, "\n- $step" . date('d/m/Y H:i:s') . ": $value");
}

/**
 * Créé le fichier Excel des adresses
 * @param string $filePath
 * @param array $cols
 * @param array $resultset
 * @param string $format
 */
function createExcel($filePath, $cols, $resultset, $format)
{
	$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	require_once 'PHPExcel/PHPExcel.php';
	$workbook = new PHPExcel();
	$sheet = $workbook->getActiveSheet();
	$sheet->setTitle('Adresses DataneoDirect');

	$styleArrayTitle = array(
		'font' => array(
			'bold' => true,
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		),
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
			),
		),
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array(
				'rgb' => 'A8D98C',
			)
		)
	);
	$lastIndex = count($cols) - 1;
	$style = $sheet->getStyle('A1:' . $letters[$lastIndex] . '1');
	$style->applyFromArray($styleArrayTitle);

	// En-têtes
	$j = 0;
	foreach ($cols as $key => $val) {
		$sheet->setCellValueByColumnAndRow($j, 1, $val);
		$sheet->getColumnDimension($letters[$j])->setAutoSize(true);
		$j++;
	}

	// Lignes
	$i = 2;
	list($db, $res) = $resultset;
	while ($row = $db->fetchRow($res)) {
		$j = 0;
		foreach ($cols as $key => $val) {
			$sheet->setCellValueExplicit($letters[$j] . $i, $row[$key], PHPExcel_Cell_DataType::TYPE_STRING);
			$j++;
		}
		$i++;
	}

	if ($format == 'excel2007') {
		require_once 'PHPExcel/PHPExcel/Writer/Excel2007.php';
		$writer = new PHPExcel_Writer_Excel2007($workbook);
		$writer->save($filePath);
	}
	else {
		require_once 'PHPExcel/PHPExcel/Writer/Excel5.php';
		$writer = new PHPExcel_Writer_Excel5($workbook);
		$writer->save($filePath);
	}

	return true;
}

/**
 * Créé le fichier texte des adresses
 * @param string $filePath
 * @param array $cols
 * @param array $resultset
 * @param string|int $separator valeur chaîne pour le séparateur ou code ASCII
 */
function createTxt($filePath, $cols, $resultset, $separator)
{
	$nl = "\r\n";
	// Vérifie si le séparateur est un entier
	if (is_numeric($separator)) {
		$separator = chr((int) $separator);
	}

	$fp = fopen($filePath, 'wb');
	fwrite($fp, implode($separator, $cols) . $nl);
	list($db, $res) = $resultset;
	while ($row = $db->fetchRow($res)) {
		$data = array();
		foreach ($cols as $key => $val) {
			$data[] = utf8_decode($row[$key]);
		}
		fwrite($fp, implode($separator, $data) . $nl);
	}
	fclose($fp);

	return true;
}
