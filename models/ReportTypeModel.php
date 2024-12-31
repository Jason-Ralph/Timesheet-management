<?php
require_once __DIR__ . '/../config/Database.php';

class ReportTypeModel {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // List active
    public function getAllReportTypes() {
        $sql = "SELECT * FROM reportType WHERE isDeleted = 0 ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // List deleted (for recycle bin)
    public function getDeletedReportTypes() {
        $sql = "SELECT * FROM reportType WHERE isDeleted = 1 ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add new
    public function addReportType($data) {
        $sql = "INSERT INTO reportType (reportType, reportDescription, isDeleted)
                VALUES (:reportType, :reportDescription, 0)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
    }

    // Edit
    public function updateReportType($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        if (empty($fields)) {
            throw new Exception('No fields to update.');
        }

        $sql = "UPDATE reportType SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    // Soft delete
    public function softDeleteReportType($id) {
        $sql = "UPDATE reportType SET isDeleted = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    // Restore
    public function restoreReportType($id) {
        $sql = "UPDATE reportType SET isDeleted = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    // Permanent delete
    public function permanentDeleteReportType($id) {
        $sql = "DELETE FROM reportType WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
	
		
public function getReportTypeById($id) {
    $sql = "SELECT * FROM reportType WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
	
	
}
