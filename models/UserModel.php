<?php
// UserModel.php
require_once __DIR__ . '/../config/Database.php';

class UserModel {
    private $db;

    public function __construct() {
        // Initialize the database connection
        $this->db = (new Database())->connect();
    }

	
	  // Retrieve a user by email (for login or validation)
    public function getUserByEmail($email) {
    $sql = "SELECT u.id, u.name, u.email, u.password, u.role_id, 
                   r.name as role_name,
                   u.userTitle, u.department, u.manager, u.languages, 
                   u.joinDate, u.birthday, u.experience, u.userImg, u.address, 
                   u.phone, u.linkedin, u.facebook, u.created_at
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = :email";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
	
	
	
    // Get all users with their roles
    public function getAllUsersWithRoles() {
        $sql = "SELECT users.*, roles.name AS role_name FROM users 
                LEFT JOIN roles ON users.role_id = roles.id 
                WHERE users.isDeleted IS NULL OR users.isDeleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all deleted users
    public function getDeletedUsers() {
    $sql = "SELECT users.*, roles.name AS role_name FROM users 
            LEFT JOIN roles ON users.role_id = roles.id 
            WHERE users.isDeleted = 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Add a new user
    public function addUser($data) {
        $sql = "INSERT INTO users (name, email, password, phone, userTitle, department, manager, joinDate, birthday, experience, address, userImg, linkedin, facebook, languages, role_id)
                VALUES (:name, :email, :password, :phone, :userTitle, :department, :manager, :joinDate, :birthday, :experience, :address, :userImg, :linkedin, :facebook, :languages, :role_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
    }

    // Update an existing user
    public function updateUser($id, $data) {
        if (empty($data)) {
            throw new Exception("No data provided for update.");
        }

        // Define allowed fields to prevent mass assignment vulnerabilities
        $allowedFields = [
            'name',
            'email',
            'phone',
            'userTitle',
            'department',
            'manager',
            'joinDate',
            'birthday',
            'experience',
            'address',
            'userImg',
            'linkedin',
            'facebook',
            'languages',
            'role_id'
        ];

        // Filter $data to include only allowed fields
        $data = array_intersect_key($data, array_flip($allowedFields));

        if (empty($data)) {
            throw new Exception("No valid data provided for update.");
        }

        // Prepare dynamic SET clause
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "`$key` = :$key";
        }
        $setClause = implode(', ', $fields);

        // Complete SQL statement
        $sql = "UPDATE `users` SET $setClause WHERE `id` = :id";
        $stmt = $this->db->prepare($sql);

        // Bind parameters dynamically
        foreach ($data as $key => $value) {
            // Determine the PDO parameter type
            $paramType = PDO::PARAM_STR;
            if (is_int($value)) {
                $paramType = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $paramType = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $paramType = PDO::PARAM_NULL;
            }

            $stmt->bindValue(":$key", $value, $paramType);
        }

        // Bind the ID
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            // Log the error or handle accordingly
            throw new Exception("Failed to update user: " . $e->getMessage());
        }
    }

    // Soft delete a user
    public function softDeleteUser($id) {
        $sql = "UPDATE users SET isDeleted = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    // Restore a soft-deleted user
    public function restoreUser($id) {
        $sql = "UPDATE users SET isDeleted = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    // Permanently delete a user
    public function permanentDeleteUser($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    // Get a user by ID
    public function getUserById($id) {
        $sql = "SELECT users.*, roles.name AS role_name FROM users 
                LEFT JOIN roles ON users.role_id = roles.id 
                WHERE users.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
	
	
// Inside models/UserModel.php
public function getUserDetailsWithPermissions($id) {
    $sql = "SELECT users.*, roles.name AS role_name, GROUP_CONCAT(permissions.permission_name) AS permissions FROM users
            LEFT JOIN roles ON users.role_id = roles.id
            LEFT JOIN role_permissions ON roles.id = role_permissions.role_id
            LEFT JOIN permissions ON role_permissions.permission_id = permissions.id
            WHERE users.id = :id
            GROUP BY users.id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
	
	
}
?>
