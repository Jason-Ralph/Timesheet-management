<?php
// TaskModel.php
require_once __DIR__ . '/../config/Database.php';

class TaskModel {
    private $db;

    public function __construct() {
        // Initialize the database connection
        $this->db = (new Database())->connect();
    }

    //-----------------------------------------------------------------------GET TASKS BY USER---
    public function getTasksByUserName($name) {
        $sql = "SELECT tasks.* 
                FROM tasks 
                WHERE tasks.userName = :name 
                  AND (tasks.isDeleted = '' OR tasks.isDeleted = 0)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $name]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //-----------------------------------------------------------------------GET DELETED TASKS---
    public function getDeletedTasks() {
        $sql = "SELECT tasks.* FROM tasks WHERE tasks.isDeleted = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //-----------------------------------------------------------------------UPDATE TASK VALUE---
    public function updateTaskValue($id, $column, $value) {
        // Validate column to prevent SQL injection
        $allowedColumns = ['taskQuotedCost', 'taskOvertime'];
        if (!in_array($column, $allowedColumns)) {
            throw new Exception('Invalid column for update.');
        }

        $query = "UPDATE tasks SET $column = :value WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':value', $value, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    //-----------------------------------------------------------------------GET TASK COST BY TYPE---
    public function getTaskCostByType($taskType) {
        $query = "SELECT taskCost FROM taskDescriptions WHERE taskName = :taskType LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':taskType', $taskType, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    //-----------------------------------------------------------------------ADD TASK---
    public function addTask($data) {
        try {
            $sql = "INSERT INTO tasks (client, reportType, taskType, taskStartTime, taskEndTime, taskTotalTime, 
                                       taskQuotedCost, taskActualCost, taskLateWork, taskOvertime, isDeleted, 
                                       userName, userRole, timeCreated, timeUpdated, comments)
                    VALUES (:client, :reportType, :taskType, :taskStartTime, :taskEndTime, :taskTotalTime, 
                            :taskQuotedCost, :taskActualCost, :taskLateWork, :taskOvertime, :isDeleted, 
                            :userName, :userRole, :timeCreated, :timeUpdated, :comments)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->db->lastInsertId(); // Return the new task ID
        } catch (PDOException $e) {
            throw new Exception('Failed to add task: ' . $e->getMessage());
        }
    }

    //-----------------------------------------------------------------------UPDATE EXISTING TASK---
    public function updateTask($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        if (empty($fields)) {
            // No fields to update
            throw new Exception('No fields to update.');
        }

        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    //-----------------------------------------------------------------------FINISH TASK---
    public function finishTask($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        if (empty($fields)) {
            // No fields to update
            throw new Exception('No fields to update.');
        }

        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    //-----------------------------------------------------------------------SENT TASK TO RECYCLE BIN---
    public function softDeleteTask($id) {
        $sql = "UPDATE tasks SET isDeleted = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    //-----------------------------------------------------------------------RESTORE TASK FROM RECYCLE BIN---
    public function restoreTask($id) {
        $sql = "UPDATE tasks SET isDeleted = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    //-----------------------------------------------------------------------PERMANENTLY DELETE TASK---
    public function permanentDeleteTask($id) {
        $sql = "DELETE FROM tasks WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    //-----------------------------------------------------------------------GET ALL TASKS (ANY STATUS)---
    public function getAllTasks() {
        $sql = "SELECT * FROM tasks ORDER BY timeCreated DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //-----------------------------------------------------------------------GET ALL ACTIVE TASKS---
    public function getAllActiveTasks() {
        $sql = "SELECT * FROM tasks WHERE isDeleted = 0 ORDER BY timeCreated DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //-----------------------------------------------------------------------GET ALL ACTIVE TASKS + DEPT---
    public function getAllActiveTasksWithDept() {
        $sql = "
            SELECT 
                t.*,
                td.taskRoles AS department
            FROM tasks t
            LEFT JOIN taskDescriptions td 
                   ON t.taskType = td.taskName
            WHERE t.isDeleted = 0
            ORDER BY t.timeCreated DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //-----------------------------------------------------------------------FILTERED ACTIVE TASKS (NO DEPT JOIN)---
    public function getFilteredActiveTasks($startDate, $endDate, $userName) {
        $whereClauses = ["isDeleted = 0"]; // Only active tasks
        $params = [];

        if (!empty($startDate)) {
            $whereClauses[] = "taskStartTime >= :startDate";
            $params[':startDate'] = $startDate . ' 00:00:00';
        }
        if (!empty($endDate)) {
            $whereClauses[] = "taskStartTime <= :endDate";
            $params[':endDate'] = $endDate . ' 23:59:59';
        }
        if (!empty($userName)) {
            $whereClauses[] = "userName = :userName";
            $params[':userName'] = $userName;
        }

        $query = "SELECT * FROM tasks";
        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }
        $query .= " ORDER BY timeCreated DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //-----------------------------------------------------------------------(NEW) FILTERED ACTIVE TASKS WITH DEPT JOIN---
    public function getFilteredActiveTasksWithDept($startDate, $endDate, $userName) {
        $whereClauses = ["t.isDeleted = 0"];
        $params = [];

        if (!empty($startDate)) {
            $whereClauses[] = "t.taskStartTime >= :startDate";
            $params[':startDate'] = $startDate . ' 00:00:00';
        }
        if (!empty($endDate)) {
            $whereClauses[] = "t.taskStartTime <= :endDate";
            $params[':endDate'] = $endDate . ' 23:59:59';
        }
        if (!empty($userName)) {
            $whereClauses[] = "t.userName = :userName";
            $params[':userName'] = $userName;
        }

        $query = "
            SELECT 
                t.*,
                r.name AS department
            FROM tasks t
            LEFT JOIN roles r ON t.userRole = r.id
        ";

        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $query .= " ORDER BY t.timeCreated DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //-----------------------------------------------------------------------GET TASK BY ID---
    public function getTaskById($id) {
        $sql = "SELECT tasks.* FROM tasks WHERE tasks.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //-----------------------------------------------------------------------RECORD HISTORY---
    public function recordHistory($taskId, $action, $userId = null, $changes = null) {
        try {
            $sql = "INSERT INTO taskHistory (taskId, userId, action, dateTime, changes)
                    VALUES (:taskId, :userId, :action, NOW(), :changes)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':taskId'  => $taskId,
                ':userId'  => $userId,
                ':action'  => strtolower($action), // Ensure consistency
                ':changes' => $changes,
            ]);
        } catch (PDOException $e) {
            throw new Exception('Failed to record task history: ' . $e->getMessage());
        }
    }
	
	public function getTaskHistoryByTaskId($taskId) {
    $sql = "SELECT th.*, u.name AS userName 
            FROM taskHistory th
            LEFT JOIN users u ON th.userId = u.id
            WHERE th.taskId = :taskId
            ORDER BY th.taskId DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
	
	public function getAllTaskHistories() {
        $stmt = $this->db->prepare("SELECT th.*, u.name AS userName 
									FROM taskHistory th
                                     LEFT JOIN users u ON th.userId = u.id
                                     ORDER BY th.taskId DESC"); // Adjust table and join as needed
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
	
	
	
	
}
?>
