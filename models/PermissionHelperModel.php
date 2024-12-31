<?php

require_once __DIR__ . '/../config/Database.php';

class PermissionHelperModel {
    private $db;

    public function __construct() {
        // Initialize the database connection
        $this->db = (new Database())->connect();
    }

    // Check if the user has a specific permission
    public function checkUserPermission($userId, $requiredPermission) {
        // Get the user's role
        $userRole = $this->getUserRole($userId);
        if (!$userRole) {
            return false; // No role assigned
        }

        // Check if the permission is granted for the role
        return $this->hasPermission($userRole, $requiredPermission);
    }

    // Get the user's role name using their user ID
    private function getUserRole($userId) {
        $query = "SELECT roles.name 
                  FROM users 
                  INNER JOIN roles ON users.role_id = roles.id 
                  WHERE users.id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        return $role ? $role['name'] : null; // Return the role name
    }

    // Check if the role has the required permission
    private function hasPermission($roleName, $permissionName) {
        // Dynamically construct the query to check the role-specific column
        $query = "SELECT `$roleName` 
                  FROM permissions 
                  WHERE permission_name = :permission_name";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':permission_name', $permissionName, PDO::PARAM_STR);
        $stmt->execute();

        $permission = $stmt->fetch(PDO::FETCH_ASSOC);
        return $permission && $permission[$roleName] == 1;
    }
}
