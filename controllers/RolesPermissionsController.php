<?php
require_once __DIR__ . '/../models/RolesPermissionsModel.php';

class RolesPermissionsController {
    private $model;

    public function __construct() {
        $this->model = new RolesPermissionsModel();
    }

    public function listRolesPermissions() {
        $roles = $this->model->getRoles(); // Get roles from DB
        $permissions = $this->model->getPermissions(); // Get permissions and current settings
        include __DIR__ . '/../views/roles_permissions.php'; // Load view
    }

	public function addPermission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $permission_name = filter_var($_POST['permission_name'], FILTER_SANITIZE_STRING);
        $newPermissionId = $this->model->addPermission($permission_name);

        echo json_encode(['success' => true, 'permission' => ['id' => $newPermissionId, 'name' => $permission_name]]);
    }
}
    public function editPermission() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $permission_name = filter_var($data['permission_name'], FILTER_SANITIZE_STRING);
        $this->model->editPermission($id, $permission_name);
        echo json_encode(['success' => true]);
    }

    public function deletePermission() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $this->model->deletePermission($id);
        echo json_encode(['success' => true]);
    }

    public function updateRolePermission() {
        $data = json_decode(file_get_contents('php://input'), true);
		$id = $data['id'];
        $this->model->updateRolePermission($id, $data['role'], $data['value']);
        echo json_encode(['success' => true]);
    }

public function fetchPermissions() {
    $roles = $this->permissionsModel->getRoles();
    $permissions = $this->permissionsModel->getPermissions();
    include __DIR__ . '/../views/roles_permissions.php';
}	
}	
?>
