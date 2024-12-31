<?php
$csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
include(ASSET_PATH . '/includes/header.php');
?>
<div class="content">
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class="container mt-4">
        <h1>Task Descriptions (Recycle Bin)</h1>
        <a class="btn btn-primary mb-3" href="task-descriptions">Back to Active</a>

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
                <!-- restore -->
                <form method="POST" action="restore-task-description" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="id" value="<?php echo $td['id']; ?>">
                  <button type="submit" class="btn btn-success btn-sm">Restore</button>
                </form>
                <!-- permanent delete -->
                <form method="POST" action="permanent-delete-task-description" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="id" value="<?php echo $td['id']; ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Delete Forever</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </article>
  </section>
</div>
<?php include(ASSET_PATH . '/includes/footer.php'); ?>
