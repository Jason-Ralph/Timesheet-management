<?php
require_once __DIR__ . '/../config/Database.php';

class RolesPermissionsModel {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getRoles() {
        $sql = "SHOW COLUMNS FROM permissions WHERE Field NOT IN ('id', 'permission_name')";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function getPermissions() {
        $sql = "SELECT * FROM permissions";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPermission($permission_name) {
        $sql = "INSERT INTO permissions (permission_name) VALUES (:permission_name)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['permission_name' => $permission_name]);

        return $this->db->lastInsertId();
    }

    public function editPermission($id, $permission_name) {
        $sql = "UPDATE permissions SET permission_name = :permission_name WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['permission_name' => $permission_name, 'id' => $id]);
    }

    public function deletePermission($id) {
        $sql = "DELETE FROM permissions WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    public function updateRolePermission($id, $role, $value) {
    $sql = "UPDATE permissions SET `$role` = :value WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['value' => $value, 'id' => $id]);
}

}
