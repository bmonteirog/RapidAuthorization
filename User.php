<?php

/**
 * User
 * É composta pela classe "User" do domínio da aplicação cliente.
 * @author Maykonn Welington Candido<maykonn@outlook.com>
 */

namespace Rapid\Authorization;

use \PDO;
use \Exception;
use Rapid\Authorization\Database\MySQL;

class User extends Entity
{

    public $id;

    /**
     * @var User
     */
    private static $instance;

    /**
     * @return User
     */
    public static function instance(PDO $pdo)
    {
        return self::$instance = new self($pdo);
    }

    public function getRoles($id, $pdoFetchMode = PDO::FETCH_ASSOC)
    {
        try {
            $this->id = (int) $id;

            $sql = "
            SELECT rol.id, rol.`name`
            FROM role rol
            RIGHT JOIN user_has_role usr ON rol.id = usr.id_role
            WHERE usr.id_user = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode($pdoFetchMode);

            return $stmt->fetchAll();
        } catch(PDOException $e) {
            MySQL::instance()->showException($e);
        }
    }

    public function attachRole($roleId, $userId)
    {
        if($this->isPossibleToAttachTheRole($roleId, $userId)) {
            try {
                $sql = "INSERT INTO user_has_role(id_user, id_role) VALUES (:idUser, :idRole)";

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':idUser', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':idRole', $roleId, PDO::PARAM_INT);

                return $stmt->execute();
            } catch(PDOException $e) {
                MySQL::instance()->showException($e);
            }
        }

        return false;
    }

    private function isPossibleToAttachTheRole($roleId, $userId)
    {
        return (
            Role::instance($this->db)->findById($roleId) and
            User::instance($this->db)->findById($userId)
            );
    }

    public function findById($userId)
    {
        try {
            // use * here because we don't know the fields from "User" table
            $sql = "SELECT * FROM user WHERE id = :userId";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $user = $stmt->fetch();

            if($user) {
                return $user;
            } else {
                throw new Exception('Record #' . $userId . ' not found on `user` table');
            }
        } catch(PDOException $e) {
            MySQL::instance()->showException($e);
        } catch(Exception $e) {
            MySQL::instance()->showException($e);
        }

        return false;
    }

    public function findAll()
    {
        try {
            $sql = "SELECT id FROM user";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch(PDOException $e) {
            MySQL::instance()->showException($e);
        } catch(Exception $e) {
            MySQL::instance()->showException($e);
        }

        return Array();
    }

    public function hasPermissionsOfTheRole($roleId, $userId)
    {
        if(
            Role::instance($this->db)->findById($roleId) and
            User::instance($this->db)->findById($userId)
        ) {
            try {
                $sql = "SELECT id FROM user_has_role WHERE id_user = :idUser AND id_role = :idRole";

                $stmt = $this->db->prepare($sql);
                $this->id = (int) $userId;
                $stmt->bindParam(':idUser', $this->id, PDO::PARAM_INT);
                $stmt->bindParam(':idRole', $roleId, PDO::PARAM_INT);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                return ($stmt->fetch() ? true : false);
            } catch(PDOException $e) {
                MySQL::instance()->showException($e);
            } catch(Exception $e) {
                MySQL::instance()->showException($e);
            }
        }

        return false;
    }

    public function hasAccessToTask($taskId, $userId)
    {
        if(
            Task::instance($this->db)->findById($taskId) and
            User::instance($this->db)->findById($userId)
        ) {
            $rolesThatHasAccessToTask = Task::instance($this->db)->getRolesThatHasAccess($taskId);
            foreach($rolesThatHasAccessToTask as $role) {
                if($this->hasPermissionsOfTheRole($role['id_role'], $userId)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasAccessToOperation($operationId, $userId)
    {
        if(
            Operation::instance($this->db)->findById($operationId) and
            User::instance($this->db)->findById($userId)
        ) {
            $tasksThatCanExecuteTheOperation = Operation::instance($this->db)->getTasksThatCanExecute($operationId);
            foreach($tasksThatCanExecuteTheOperation as $task) {
                if($this->hasAccessToTask($task['id_task'], $userId)) {
                    return true;
                }
            }
        }

        return false;
    }

}

?>
