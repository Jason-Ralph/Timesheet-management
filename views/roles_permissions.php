<?php
$csrf_token = $_SESSION[ 'csrf_token' ] = bin2hex( random_bytes( 32 ) );
include( ASSET_PATH . '/includes/header.php' );
?>
<div class="content">
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class="container mt-5"> 
        <!-- Success and Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?> </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?> </div>
        <?php endif; ?>
        <h1>Roles and Permissions</h1>
        <!-- Add Permission Form -->
        
        <?php if ($permissionHelperController->hasPermission('Add permission')): ?>
        <form method="POST" id="addPermissionForm">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          <div class="row mb-3">
            <div class="col-md-6">
              <input type="text" name="permission_name" class="form-control" placeholder="Permission Name" required>
            </div>
            <div class="col-md-6">
              <button type="submit" class="btn btn-success">Add Permission</button>
            </div>
          </div>
        </form>
        <?php endif; ?>
        <div id="permissions-container">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Permission</th>
                <?php foreach ($roles as $role): ?>
                <th><?php echo htmlspecialchars($role); ?></th>
                <?php endforeach; ?>
                <?php if ($permissionHelperController->hasPermission('Delete permission')): ?>
                <th>Actions</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($permissions as $permission): ?>
              <tr>
                <td><?php if ($permissionHelperController->hasPermission('Edit permission')): ?>
                  <input type="text" class="form-control edit-permission-name" data-permission-id="<?php echo $permission['id']; ?>" value="<?php echo htmlspecialchars($permission['permission_name']); ?>">
                  <?php else: ?>
                  <?php echo htmlspecialchars($permission['permission_name']); ?>
                  <?php endif; ?></td>
                <?php foreach ($roles as $role): ?>
                <?php if ($permissionHelperController->hasPermission('Edit permission')): ?>
                <td><input type="checkbox"
            class="permission-checkbox"
            data-permission-id="<?php echo $permission['id']; ?>"
            data-role="<?php echo htmlspecialchars($role); ?>"
            <?php echo $permission[$role] == 1 ? 'checked' : ''; ?>></td>
                <?php else: ?>
                <td><?php echo $permission[$role] == 1 ? '✔️' : '❌'; ?></td>
                <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($permissionHelperController->hasPermission('Delete permission')): ?>
                <td><button type="button" class="btn btn-danger btn-sm delete-permission" data-permission-id="<?php echo $permission['id']; ?>">Delete</button></td>
                <?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </article>
  </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Add Permission
    const addPermissionForm = document.getElementById('addPermissionForm');
    addPermissionForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(addPermissionForm);

        const response = await fetch('<?php echo BASE_URL; ?>/add-permission', {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();
        if (result.success) {
            alert('Permission added successfully!');
            fetchPermissions(); // Fetch and reload permissions dynamically
        } else {
            alert('Failed to add permission.');
        }
    });

    // Update Role Permission
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', async function () {
            const permissionId = this.dataset.permissionId;
            const role = this.dataset.role;
            const value = this.checked ? 1 : 0;

            const response = await fetch('<?php echo BASE_URL; ?>/update-role-permission', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: permissionId, role: role, value: value }),
            });

            const result = await response.json();
            if (!result.success) {
                alert('Failed to update permission.');
                this.checked = !this.checked; // Revert checkbox state on failure
            }
        });
    });

    // Fetch Permissions Dynamically
    async function fetchPermissions() {
        const response = await fetch('<?php echo BASE_URL; ?>/fetch-permissions', {
            method: 'GET',
        });
        const html = await response.text();

        // Update the permissions table or container with new content
        document.getElementById('permissions-container').innerHTML = html;

        // Re-bind events to the dynamically loaded content
        bindDynamicEvents();
    }

    // Re-bind events for dynamically loaded elements
    function bindDynamicEvents() {
        document.querySelectorAll('.edit-permission-name').forEach(input => {
            input.addEventListener('blur', async function () {
                const permissionId = this.dataset.permissionId;
                const permissionName = this.value.trim();

                const response = await fetch('<?php echo BASE_URL; ?>/edit-permission', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: permissionId, permission_name: permissionName }),
                });

                const result = await response.json();
                if (result.success) {
                    alert('Permission updated successfully!');
                } else {
                    alert('Failed to update permission.');
                }
            });
        });

        document.querySelectorAll('.delete-permission').forEach(button => {
            button.addEventListener('click', async function () {
                if (!confirm('Are you sure you want to delete this permission?')) return;

                const permissionId = this.dataset.permissionId;
                const response = await fetch('<?php echo BASE_URL; ?>/delete-permission', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: permissionId }),
                });

                const result = await response.json();
                if (result.success) {
                    alert('Permission deleted successfully!');
                    fetchPermissions(); // Fetch and reload permissions dynamically
                } else {
                    alert('Failed to delete permission.');
                }
            });

        });
    }

    // Initialize dynamic events
    bindDynamicEvents();
});

</script>
<?php include(ASSET_PATH . '/includes/footer.php'); ?>
