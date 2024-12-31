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

        <h1>Report Types</h1>
		  
		<?php if ($permissionHelperController->hasPermission('Add report type')): ?>  
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addReportTypeModal">Add Report Type</button>
		<?php endif; ?>
		  
		  
		<?php if ($permissionHelperController->hasPermission('Access report types recycle bin')): ?>  
        <a class="btn btn-primary mb-3" href="report-type-recycle-bin">Recycle Bin</a>
		<?php endif; ?>
		  
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Report Type</th>
              <th>Description</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($reportTypes as $rt): ?>
            <tr>
              <td><?php echo htmlspecialchars($rt['reportType']); ?></td>
              <td><?php echo htmlspecialchars($rt['reportDescription']); ?></td>
              <td>
				<?php if ($permissionHelperController->hasPermission('Edit report type')): ?>   
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editReportTypeModal<?php echo $rt['id']; ?>">Edit</button>
				<?php endif; ?>
				<?php if ($permissionHelperController->hasPermission('Delete report type')): ?>   
                <form method="POST" action="report-types" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $rt['id']; ?>">
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
<?php foreach($reportTypes as $rt): ?>
            
            <!-- Edit Modal -->
            <div class="modal fade" id="editReportTypeModal<?php echo $rt['id']; ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="POST" action="report-types">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" value="<?php echo $rt['id']; ?>">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Report Type</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <label>Report Type</label>
                      <input type="text" name="reportType" class="form-control mb-3" value="<?php echo htmlspecialchars($rt['reportType']); ?>" required>
                      <label>Description</label>
                      <input type="text" name="reportDescription" class="form-control mb-3" value="<?php echo htmlspecialchars($rt['reportDescription']); ?>">
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
<!-- Add Modal -->
<div class="modal fade" id="addReportTypeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="report-types">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <div class="modal-header">
          <h5 class="modal-title">Add Report Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label>Report Type</label>
          <input type="text" name="reportType" class="form-control mb-3" placeholder="E.g. Annual Financial Statements" required>
          <label>Description</label>
          <input type="text" name="reportDescription" class="form-control mb-3" placeholder="(Optional) short desc">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include(ASSET_PATH . '/includes/footer.php'); ?>
