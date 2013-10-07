<?php

/**
 * MySQLSchemaHandler
 * @author Maykonn Welington Candido<maykonn@outlook.com>
 */

namespace Rapid\Authorization\Database;

use \PDO;
use Rapid\Authorization\ClientPreferences;

class MySQLSchemaHandler
{

    private $userTable = '';
    private $userTablePK = '';
    private $dbCharset = '';

    /**
     * @var PDO
     */
    private $db;

    /**
     * @var MySQLSchemaHandler
     */
    private static $instance;

    /**
     * @return MySQLSchemaHandler
     */
    public static function instance(ClientPreferences $preferences, PDO $pdo)
    {
        if(self::$instance instanceof MySQLSchemaHandler) {
            return self::$instance;
        } else {
            return self::$instance = new self($preferences, $pdo);
        }
    }

    private function __construct(ClientPreferences $preferences, PDO $pdo)
    {
        $preferencesList = $preferences->getPreferencesList();
        $this->userTable = $preferencesList->userTable;
        $this->userTablePK = $preferencesList->userTablePK;
        $this->dbCharset = $preferencesList->dbCharset;
        $this->db = $pdo;
    }

    public function createDefaultSchema()
    {
        try {
            $stmt = $this->db->prepare($this->getAuthorizationTablesStmt());
            $stmt->execute();
        } catch(PDOException $e) {
            echo '<pre>';
            echo '<b>' . $e->getMessage() . '</b><br/><br/>';
            echo $e->getTraceAsString();
            echo '</pre>';
        }
    }

    private function getAuthorizationTablesStmt()
    {
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $contentDefault = file_get_contents($dir . 'schema.sql');

        // replace user table and PK
        $userTableDefault = 'CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)';
        $userTable = 'CREATE TABLE IF NOT EXISTS `' . $this->userTable . '` (
  `' . $this->userTablePK . '` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`' . $this->userTablePK . '`)';
        $contentUserTable = str_replace($userTableDefault, $userTable, $contentDefault);

        // replace user_has_role foreign key
        $userHasRoleFKDefault = 'REFERENCES `user` (`id`)';
        $userHasRoleFK = 'REFERENCES `' . $this->userTable . '` (`' . $this->userTablePK . '`)';

        $contentUser = str_replace($userHasRoleFKDefault, $userHasRoleFK, $contentUserTable);

        // tables collation
        $collationDefault = 'utf8';
        $collation = $this->dbCharset;
        $content = str_replace($collationDefault, $collation, $contentUser);

        return $content;
    }

}

?>
