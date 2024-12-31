<?php
require_once __DIR__ . '/../config/Database.php';

class RoleModel {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAllRoles() {
        $sql = "SELECT id, name, description FROM roles";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
	
	
	
}





?>
