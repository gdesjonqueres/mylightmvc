<?php
/**
 * Classe singleton contenant toutes les variables de configuration de l'application
 *
 * @property string APP_NAME        Nom de l'application (utilisé notamment pour construire l'url de base)
 * @property string APP_BUILD       N° de build de l'application
 *                                      utilisé pour versionner les fichiers de scripts (js et css) et donc pour forcer le raffraîchissement du cache du navigateur
 * @property string APP_ENV         Environnement Web (=dev|int|prod|local)
 *
 * @property string PATH            Répertoire racine du requeteur (chemin absolu)
 * @property string PATH_APP        Chemin vers le répertoire des modules (contenant controllers, models, views...)
 * @property string PATH_INC        Contient toutes les classes techniques, notamment pour gérer le MVC
 * @property string PATH_IMG        Fichiers image
 * @property string PATH_JS         Fichiers javascript
 * @property string PATH_CSS        Fichiers css
 * @property string PATH_PLGN       Contient les "plugins", widgets du domaine public
 * @property string PATH_FILES      Répertoire où sont créés les fichiers d'extraction d'adresses
 * @property string PATH_EXEC       Répertoire contenant les scripts exécutés en ligne de commande
 * @property string PATH_TEMP       Répertoire pour les fichiers temporaires comme le fichier error.log du requeteur
 *
 * @property string URL_ROOT        Définit le web root (url sans host name) de l'application (ex: "/" pour prod, "/gdes/requeteur/" dans répertoire de dev de gdes)
 *                                      utilisé pour construire toutes les url internes de l'application
 * @property string URL_BASE        URL de base absolue (protocol + host name + web root)
 * @property string URL_IMG
 * @property string URL_CSS
 * @property string URL_JS
 * @property string URL_PLGN
 * @property string URL_SCRIPTS     URL vers les scripts appelés en direct (sans passer par le front controller)
 *
 * @property string SESSION_NAME            Nom de la session (permet d'éviter de mélanger des données de session avec ddv2)
 * @property string DEFAULT_MODULE          Indique quel est le module à charger si pas de module spécifié
 * @property string USE_LAYOUT              Indique si on utilise un layout
 *                                          (le rendu de chaque page est intégré dans un template de base contenant les en-têtes, pieds de page et l'inclusion des scripts de base)
 * @property string DEFAULT_LAYOUT_PATH     Chemin vers le fichier de vue du layout
 * @property string USE_SCRIPT_MINIMIZE     Indique si l'on doit charger une version minimisée si elle existe (ex: comptage.min.css pour comptage.css)
 * @property string USE_SCRIPT_COMBINE      Indique s'il faut combiner tous les js et tous les css dans un même fichier
 * @property string USE_SCRIPT_VERSION      Indique si on versionne les scripts pour mieux gérer la mise en cache (utilise APP_BUILD pour versionner)
 *
 * @property string DB_ORACLE_IN_LIMIT      Nombre d'éléments max pour la clause IN d'un SELECT, ex: SELECT WHERE x IN (y, z, ...)
 *
 * @property string DEFAULT_MAIL_FROM
 * @property string MAIL_ADMIN
 * @property string MAIL_DEMANDES
 * @property string MAILCALLBACK
 * @property string MAILFROM
 * @property string MAILADMIN
 * @property string EMAILVALIDATION
 * @property string MAILCONTACT
 *
 * @property string SALT
 * @property string TVA
 *
 * @package Fw
 */
class Application
{
    private static $_instance;

    private $_vars;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __set($k, $v)
    {
        $this->_vars[$k] = $v;
        return $this;
    }

    public function __get($k)
    {
        if (array_key_exists($k, $this->_vars)) {
            return $this->_vars[$k];
        }
        return null;
    }

    public function __isset($k)
    {
        return isset($this->_vars[$k]);
    }

    public function __unset($k)
    {
        unset($this->_vars[$k]);
        return $this;
    }
}