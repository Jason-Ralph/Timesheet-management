<?php include(ASSET_PATH . '/includes/header.php'); ?>

<div class="content">
    <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_edpg3c3s.json" background="transparent" speed="0.3" style="width: 200px; height: 200px; position:absolute; bottom:30px; right:30px; z-index:0; opacity:0.3;" autoplay></lottie-player>    
    
    <div class="menu-trigger"></div>    
    <section class="profile">
        <article>
            <div class="container mt-5">
				
		
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
				
				
	<h1>Client Recycle Bin</h1>
<a class="btn btn-primary mb-3" href="clients">Back to client list</a>
<table class="table table-striped">
    <thead>
    <tr>
        <th>Client Name</th>
        <th>Account Executive</th>
        <th>Contact Email</th>
        <th>Contact Phone</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($clients as $client): ?>
        <tr>
            <td><?php echo htmlspecialchars($client['client_name']); ?></td>
            <td><?php echo htmlspecialchars($client['accountExecutive']); ?></td>
            <td><?php echo htmlspecialchars($client['contactEmail']); ?></td>
            <td><?php echo htmlspecialchars($client['contactPhone']); ?></td>
            <td>
                <form method="POST" action="<?php echo BASE_URL; ?>/restore-client" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                    <button type="submit" class="btn btn-success btn-sm">Restore</button>
                </form>
				 <form method="POST" action="<?php echo BASE_URL; ?>/permanent-delete-client" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
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