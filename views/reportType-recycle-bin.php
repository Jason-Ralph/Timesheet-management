<?php
$csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
include(ASSET_PATH . '/includes/header.php');
?>
<div class="content">
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class="container mt-4">
        <h1>Report Types (Recycle Bin)</h1>
        <a class="btn btn-primary mb-3" href="report-types">Back to Active</a>

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
                <!-- restore form -->
                <form method="POST" action="restore-report-type" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="id" value="<?php echo $rt['id']; ?>">
                  <button type="submit" class="btn btn-success btn-sm">Restore</button>
                </form>

                <!-- permanent delete form -->
                <form method="POST" action="permanent-delete-report-type" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="id" value="<?php echo $rt['id']; ?>">
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
