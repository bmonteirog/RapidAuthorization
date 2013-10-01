<?php

/**
 * MySQLSchemaHandler
 * @author Maykonn Welington Candido<maykonn@outlook.com>
 */

namespace Rapid\Authorization\Database;

use \PDO;

class MySQLSchemaHandler
{

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
    public static function instance(PDO $pdo)
    {
        if(self::$instance instanceof MySQLSchemaHandler) {
            return self::$instance;
        } else {
            return self::$instance = new self($pdo);
        }
    }

    private function __construct(PDO $pdo)
    {
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
        return file_get_contents($dir . 'schema.sql');
    }

}

?>