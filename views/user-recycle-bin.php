<?php include(ASSET_PATH . '/includes/header.php'); ?>
<div class="content">
  <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_edpg3c3s.json" background="transparent" speed="0.3" style="width: 200px; height: 200px; position:absolute; bottom:30px; right:30px; z-index:0; opacity:0.3;" autoplay></lottie-player>
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class="container mt-5">
        <h1>User Recycle Bin</h1>
        <a class="btn btn-primary mb-3" href="users">Back to Users</a>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>User Name</th>
              <th>User Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
			<?php if (!empty($users)): ?>  
            <?php foreach ($users as $user): ?>
            <tr>
              <td><?php echo htmlspecialchars($user['name']); ?></td>
              <td><?php echo htmlspecialchars($user['email']); ?></td>
              <td><form method="POST" action="<?php echo BASE_URL; ?>/restore-user" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                  <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                  <button type="submit" class="btn btn-success btn-sm">Restore</button>
                </form>
                <form method="POST" action="<?php echo BASE_URL; ?>/permanent-delete-user" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                  <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                </form></td>
            </tr>
            <?php endforeach; ?>
			<?php else: ?>
        <tr>
            <td colspan="3">No users in the recycle bin.</td>
        </tr>
    <?php endif; ?>
</tbody>
        </table>
      </div>
    </article>
  </section>
</div>
<?php include(ASSET_PATH . '/includes/footer.php'); ?>