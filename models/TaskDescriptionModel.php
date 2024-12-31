<?php
// /models/TaskDescriptionModel.php
require_once __DIR__ . '/../config/Database.php';

class TaskDescriptionModel {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // List active
    public function getAllTaskDescriptions() {
        $sql = "SELECT * FROM taskDescriptions WHERE isDeleted = 0 ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // List deleted
    public function getDeletedTaskDescriptions() {
        $sql = "SELECT * FROM taskDescriptions WHERE isDeleted = 1 ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add
    public function addTaskDescription($data) {
        $sql = "INSERT INTO taskDescriptions (taskName, taskDescription, taskRoles, taskCost, isDeleted)
                VALUES (:taskName, :taskDescription, :taskRoles, :taskCost, 0)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
    }

    // Update
    public function updateTaskDescription($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        if (empty($fields)) {
            throw new Exception('No fields to update.');
        }

        $sql = "UPDATE taskDescriptions SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    // Soft delete
    public function softDeleteTaskDescription($id) {
        $sql = "UPDATE taskDescriptions SET isDeleted = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    // Restore
    public function restoreTaskDescription($id) {
        $sql = "UPDATE taskDescriptions SET isDeleted = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    // Permanent delete
    public function permanentDeleteTaskDescription($id) {
        $sql = "DELETE FROM taskDescriptions WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
	
	
		
public function getTaskDescriptionById($id) {
    $sql = "SELECT * FROM taskDescriptions WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
	
	
	
}
