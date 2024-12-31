<?php
$csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
include(ASSET_PATH . '/includes/header.php');
?>
<div class="content">
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class="container mt-5">

        <?php if(isset($_SESSION['success_message'])): ?>
          <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
          </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error_message'])): ?>
          <div class="alert alert-danger">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
          </div>
        <?php endif; ?>

        <h1>Task Descriptions</h1>
		  
		  
		<?php if ($permissionHelperController->hasPermission('Add task description')): ?>   
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTaskDescriptionModal">Add Task Description</button>
		<?php endif; ?>  
		  
		<?php if ($permissionHelperController->hasPermission('Access task description recycle bin')): ?>  
        <a class="btn btn-primary mb-3" href="task-description-recycle-bin">Recycle Bin</a>
		<?php endif; ?> 
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Task Name</th>
              <th>Description</th>
              <th>Roles</th>
              <th>Cost</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($taskDescriptions as $td): ?>
            <tr>
              <td><?php echo htmlspecialchars($td['taskName']); ?></td>
              <td><?php echo htmlspecialchars($td['taskDescription']); ?></td>
              <td><?php echo htmlspecialchars($td['taskRoles']); ?></td>
              <td><?php echo htmlspecialchars($td['taskCost']); ?></td>
              <td>
				  
				  
				  
				<?php if ($permissionHelperController->hasPermission('Edit task description')): ?>  
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editTaskDescModal<?php echo $td['id']; ?>">Edit</button>
				<?php endif; ?> 
				  
				<?php if ($permissionHelperController->hasPermission('Delete task description')): ?>  
                <form method="POST" action="task-descriptions" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $td['id']; ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
				<?php endif; ?>   
				  
				  
              </td>
            </tr>

           

          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </article>
  </section>
</div>

<!-- Place modals outside the content at the end -->
<?php foreach($taskDescriptions as $td): ?>
<!-- Edit Modal -->
<div class="modal fade" id="editTaskDescModal<?php echo $td['id']; ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="task-descriptions">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="id" value="<?php echo $td['id']; ?>">
        <div class="modal-header">
          <h5 class="modal-title">Edit Task Description</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label>Task Name</label>
          <input type="text" name="taskName" class="form-control mb-3" value="<?php echo htmlspecialchars($td['taskName']); ?>" required>
          <label>Description</label>
          <input type="text" name="taskDescription" class="form-control mb-3" value="<?php echo htmlspecialchars($td['taskDescription']); ?>">
          <label>Roles</label>
          <input type="text" name="taskRoles" class="form-control mb-3" value="<?php echo htmlspecialchars($td['taskRoles']); ?>">
          <label>Cost</label>
          <input type="number" name="taskCost" class="form-control mb-3" value="<?php echo htmlspecialchars($td['taskCost']); ?>">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- /Edit Modal -->
<?php endforeach; ?>
            <!-- /Edit Modal -->
<!-- Add Modal -->
<div class="modal fade" id="addTaskDescriptionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="task-descriptions">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <div class="modal-header">
          <h5 class="modal-title">Add Task Description</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label>Task Name</label>
          <input type="text" name="taskName" class="form-control mb-3" required>
          <label>Description</label>
          <input type="text" name="taskDescription" class="form-control mb-3">
          <label>Roles</label>
          <input type="text" name="taskRoles" class="form-control mb-3">
          <label>Cost</label>
          <input type="number" name="taskCost" class="form-control mb-3" value="0">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add Task Description</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include(ASSET_PATH . '/includes/footer.php'); ?>
