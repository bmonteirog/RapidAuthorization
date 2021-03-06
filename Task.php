<?php

/**
 * Task
 * @author Maykonn Welington Candido<maykonn@outlook.com>
 */

namespace RapidAuthorization;

use \PDO;
use \Exception;
use RapidAuthorization\Database\MySQL;

class Task extends Entity
{
    public function delete($id)
    {
        if($this->findById($id)) {
            $this->id = $id;

            try {
                $sql = "DELETE FROM rpd_task WHERE id = :id";

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
                return $stmt->execute();
            } catch(\PDOException $e) {
                MySQL::instance()->showException($e);
            }
        }

        return false;
    }

    public function attachOperation($operationId, $taskId)
    {
        if($this->isPossibleToAttachTheOperation($operationId, $taskId)) {
            try {
                $sql = "INSERT INTO rpd_task_has_operation(id_task, id_operation) VALUES (:idTask, :idOperation)";

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':idTask', $taskId, PDO::PARAM_INT);
                $stmt->bindParam(':idOperation', $operationId, PDO::PARAM_INT);
                return $stmt->execute();
            } catch(\PDOException $e) {
                MySQL::instance()->showException($e);
            }
        }

        return false;
    }

    private function isPossibleToAttachTheOperation($operationId, $taskId)
    {
        return (
            Operation::instance($this->preferences, $this->db)->findById($operationId) &&
            !Task::instance($this->preferences, $this->db)->hasOperation($operationId, $taskId)
            );
    }

    public function findById($taskId)
    {
        try {
            $sql = "SELECT id, name, business_name, description FROM rpd_task WHERE id = :taskId";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $task = $stmt->fetch();

            if($task) {
                return $task;
            } else {
                throw new Exception('Record #' . $taskId . ' not found on `task` table');
            }
        } catch(\PDOException $e) {
            MySQL::instance()->showException($e);
        } catch(Exception $e) {
            MySQL::instance()->showException($e);
        }

        return false;
    }

    public function findByName($name)
    {
        try {
            $sql = "SELECT id, name, business_name, description FROM rpd_task WHERE name = :name";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $task = $stmt->fetch();

            if($task) {
                return $task;
            } else {
                throw new Exception('Record with name: ' . $name . ' not found on `task` table');
            }
        } catch(\PDOException $e) {
            MySQL::instance()->showException($e);
        } catch(Exception $e) {
            MySQL::instance()->showException($e);
        }

        return false;
    }

    public function findAll()
    {
        try {
            $sql = "SELECT id, name, business_name, description FROM rpd_task";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            MySQL::instance()->showException($e);
        } catch(Exception $e) {
            MySQL::instance()->showException($e);
        }

        return Array();
    }

    public function save()
    {
        try {
            $sql = "
                INSERT INTO rpd_task(
                    id, name, business_name, description
                ) VALUES (
                    :id, :name, :businessName, :description
                ) ON DUPLICATE KEY UPDATE name = :name, business_name = :businessName, description = :description";

            return $this->saveFromSQL($sql);
        } catch(\PDOException $e) {
            MySQL::instance()->showException($e);
        }
    }

    public function getRolesThatHasAccess($taskId)
    {
        if(Task::instance($this->preferences, $this->db)->findById($taskId)) {
            try {
                $sql = "SELECT id_role FROM rpd_role_has_task WHERE id_task = :idTask";
                $stmt = $this->db->prepare($sql);
                $this->id = (int) $taskId;
                $stmt->bindParam(':idTask', $this->id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                return $stmt->fetchAll();
            } catch(\PDOException $e) {
                MySQL::instance()->showException($e);
            }
        }

        return false;
    }

    public function getOperations($taskId)
    {
        if(Task::instance($this->preferences, $this->db)->findById($taskId)) {
            try {
                $sql = "
                SELECT o.id, o.name, o.business_name, o.description
                FROM rpd_operation o INNER JOIN rpd_task_has_operation tho ON o.id = tho.id_operation
                WHERE tho.id_task = :idTask";

                $stmt = $this->db->prepare($sql);
                $this->id = (int) $taskId;
                $stmt->bindParam(':idTask', $this->id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(\PDOException $e) {
                MySQL::instance()->showException($e);
            } catch(Exception $e) {
                MySQL::instance()->showException($e);
            }
        }

        return Array();
    }

    public function hasOperation($operationId, $taskId)
    {
        if(
            Operation::instance($this->preferences, $this->db)->findById($operationId) &&
            Task::instance($this->preferences, $this->db)->findById($taskId)
        ) {
            $operation = Operation::instance($this->preferences, $this->db);
            $tasksThatCanExecuteTheOperation = $operation->getTasksThatCanExecute($operationId);
            foreach($tasksThatCanExecuteTheOperation as $task) {
                if($task['id_task'] == $taskId) {
                    return true;
                }
            }
        }

        return false;
    }

    public function removeTaskFromRole($taskId, $roleId)
    {
        if(
            Role::instance($this->preferences, $this->db)->findById($roleId) &&
            Task::instance($this->preferences, $this->db)->findById($taskId)
        ) {
            try {
                $sql = "DELETE FROM rpd_role_has_task WHERE id_role = :roleId AND id_task = :taskId";

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
                $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
                return $stmt->execute();
            } catch(\PDOException $e) {
                MySQL::instance()->showException($e);
            }
        }

        return false;
    }

}
