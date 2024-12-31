<?php
$csrf_token = $_SESSION[ 'csrf_token' ] = bin2hex( random_bytes( 32 ) );
?>
<?php include(ASSET_PATH . '/includes/header.php'); ?>
<div class="content">
  <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_edpg3c3s.json" background="transparent" speed="0.3" style="width: 200px; height: 200px; position:absolute; bottom:30px; right:30px; z-index:0; opacity:0.3;" autoplay></lottie-player>
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class="container mt-5">
		<!-- users.php -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>  
		  
		  
		  
        <h1>Users</h1>
		<?php if ($permissionHelperController->hasPermission('Add user')): ?>  
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
		<?php endif; ?>   
		  
		<?php if ($permissionHelperController->hasPermission('Access user recycle bin')): ?>  
        <a class="btn btn-primary mb-3" href="user-recycle-bin">Recycle Bin</a>
		<?php endif; ?> 
		  
		  
        <table class="table table-striped">
          <thead>
            <tr>
              <th>User Photo</th>
              <th>User Name</th>
              <th>User Title</th>
              <th>Department</th>
              <th>User Phone</th>
              <th>User Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
              <td><div class="profile-image-sml">
                  <?php
                  $userImg = isset( $user[ 'userImg' ] ) && $user[ 'userImg' ] ? $user[ 'userImg' ] : 'Placeholder.jpg';
                  ?>
                  <img src="../assets/images/<?php echo htmlspecialchars($userImg, ENT_QUOTES, 'UTF-8'); ?>" alt="User Image"/> </div></td>
              <td><?php echo htmlspecialchars($user['name']); ?></td>
              <td><?php echo htmlspecialchars($user['userTitle']); ?></td>
              <td><?php echo htmlspecialchars($user['department']); ?></td>
              <td><?php echo htmlspecialchars($user['phone']); ?></td>
              <td><?php echo htmlspecialchars($user['email']); ?></td>
              <td>
				<?php if ($permissionHelperController->hasPermission('View user')): ?>  
				<a href="<?php echo BASE_URL; ?>/user-profile?id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm">View</a>
				<?php endif; ?>  
				  
				<?php if ($permissionHelperController->hasPermission('Edit user')): ?>  
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">Edit</button>
				<?php endif; ?>  
				  
				<?php if ($permissionHelperController->hasPermission('Delete user')): ?>  
                <form method="POST" action="<?php echo BASE_URL; ?>/users" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
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
<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?php echo BASE_URL; ?>/users">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="modal-header">
          <h5 class="modal-title">Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="name" class="form-control mb-3" placeholder="Name" required>
          <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
          <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
          <input type="text" name="phone" class="form-control mb-3" placeholder="Phone number" required>
          <input type="text" name="userTitle" class="form-control mb-3" placeholder="User Title">
          <select name="role_id" class="form-control mb-3" required>
        <option value="">Select Role</option>
        <?php foreach ($roles as $role): ?>
            <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
        <?php endforeach; ?>
    </select>
          <input type="text" name="department" class="form-control mb-3" placeholder="Department">
          <input type="text" name="manager" class="form-control mb-3" placeholder="Manager">
          <input type="date" name="joinDate" class="form-control mb-3" placeholder="Join Date">
          <input type="date" name="birthday" class="form-control mb-3" placeholder="Birthday">
          <input type="text" name="experience" class="form-control mb-3" placeholder="Experience">
          <input type="text" name="address" class="form-control mb-3" placeholder="Address">
          <input type="text" name="userImg" class="form-control mb-3" placeholder="User Image">
          <input type="text" name="linkedin" class="form-control mb-3" placeholder="LinkedIn">
          <input type="text" name="facebook" class="form-control mb-3" placeholder="Facebook">
          <input type="text" name="languages" class="form-control mb-3" placeholder="Languages">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<?php foreach ($users as $user): ?>
<div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?php echo BASE_URL; ?>/users">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
        <div class="modal-header">
          <h5 class="modal-title">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
		  <label for="name">Name</label>
          <input type="text" name="name" class="form-control mb-3" value="<?php echo htmlspecialchars($user['name']); ?>" required>
		  <label for="email">Email</label>
          <input type="email" name="email" class="form-control mb-3" value="<?php echo htmlspecialchars($user['email']); ?>" required>
		  <label for="phone">Phone</label>
          <input type="text" name="phone" class="form-control mb-3" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
		  <label for="userTitle">Title</label>
          <input type="text" name="userTitle" class="form-control mb-3" value="<?php echo htmlspecialchars($user['userTitle']); ?>">
		  <label for="role_id">Role</label>
          <select name="role_id" class="form-control mb-3" >
            <?php foreach ($roles as $role): ?>
            <option value="<?php echo $role['id']; ?>" <?php if ($role['id'] == $user['role_id']) echo 'selected'; ?>><?php echo htmlspecialchars($role['name']); ?></option>
            <?php endforeach; ?>
          </select>
		  <label for="department">Department</label>	
          <input type="text" name="department" class="form-control mb-3" value="<?php echo htmlspecialchars($user['department']); ?>">
		  <label for="manager">Manager</label>
          <input type="text" name="manager" class="form-control mb-3" value="<?php echo htmlspecialchars($user['manager']); ?>">
		  <label for="joinDate">Join date</label>	
          <input type="date" name="joinDate" class="form-control mb-3" value="<?php echo htmlspecialchars($user['joinDate']); ?>">
			<label for="birthday">Birthday</label>
          <input type="date" name="birthday" class="form-control mb-3" value="<?php echo htmlspecialchars($user['birthday']); ?>">
			<label for="experience">Experience</label>
          <input type="text" name="experience" class="form-control mb-3" value="<?php echo htmlspecialchars($user['experience']); ?>">
			<label for="address">Address</label>
          <input type="text" name="address" class="form-control mb-3" value="<?php echo htmlspecialchars($user['address']); ?>">
			<label for="userImg">Image name<small>usually (Name + .jpg)</small></label>
          <input type="text" name="userImg" class="form-control mb-3" value="<?php echo htmlspecialchars($user['userImg']); ?>">
			<label for="linkedin">Linkedin</label>
          <input type="text" name="linkedin" class="form-control mb-3" value="<?php echo htmlspecialchars($user['linkedin']); ?>">
			<label for="facebook">Facebook</label>
          <input type="text" name="facebook" class="form-control mb-3" value="<?php echo htmlspecialchars($user['facebook']); ?>">
			<label for="languages">Languages</label>
          <input type="text" name="languages" class="form-control mb-3" value="<?php echo htmlspecialchars($user['languages']); ?>">
			
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php include(ASSET_PATH . '/includes/footer.php'); ?>
