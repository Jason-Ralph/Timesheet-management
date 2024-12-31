<?php
$csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
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
                <h1>Clients</h1>
				
				<?php if ($permissionHelperController->hasPermission('Add client')): ?>
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addClientModal">Add Client</button>
				<?php endif; ?>
				
				<?php if ($permissionHelperController->hasPermission('Access client recycle bin')): ?>
                <a class="btn btn-primary mb-3" href="client-recycle-bin">Recycle bin</a>
				<?php endif; ?>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Client logo</th>
                        <th>Account Executive</th>
                        <th>Contact Email</th>
                        <th>Contact Phone</th>
                        <th>Client Year End</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($client['client_name']); ?></td>
							<td><img src="<?php echo htmlspecialchars($client['clientLogo']); ?>" style="max-width: 120px;"  alt=""/></td>
                            <td><?php echo htmlspecialchars($client['accountExecutive']); ?></td>
                            <td><?php echo htmlspecialchars($client['contactName']); ?></td>
                            <td><?php echo htmlspecialchars($client['contactEmail']); ?></td>
                            <td><?php echo htmlspecialchars($client['ClientYearEnd']); ?></td>
                            <td>
								<?php if ($permissionHelperController->hasPermission('View client')): ?>
								<a href="<?php echo BASE_URL; ?>/view-client?id=<?php echo $client['id']; ?>" class="btn btn-info btn-sm">View</a>
								<?php endif; ?>
								<?php if ($_SESSION['user']['name'] == $client['accountExecutive']): ?> 
								<?php if ($permissionHelperController->hasPermission('Edit client')): ?>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editClientModal<?php echo $client['id']; ?>">Edit</button>
								<?php endif; ?>
								<?php endif; ?>
								<?php if ($_SESSION['user']['name'] == $client['accountExecutive']): ?> 
								<?php if ($permissionHelperController->hasPermission('Delete client')): ?>
                              <form method="POST" action="<?php echo BASE_URL; ?>/clients" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
					<?php endif; ?>			
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

<!-- Add Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>/clients">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="client_name" class="form-control mb-3" placeholder="Client Name" required>
                    <input type="text" name="accountExecutive" class="form-control mb-3" placeholder="Account Executive" required>
                    <input type="email" name="contactEmail" class="form-control mb-3" placeholder="Contact Email" required>
                    <input type="text" name="contactPhone" class="form-control mb-3" placeholder="Contact Phone" required>
                    <input type="text" name="contactName" class="form-control mb-3" placeholder="Contact Name">
                    <input type="text" name="contactTitle" class="form-control mb-3" placeholder="Contact Title">
                    <input type="email" name="secondaryContactEmail" class="form-control mb-3" placeholder="Secondary Contact Email">
                    <input type="text" name="secondaryContactName" class="form-control mb-3" placeholder="Secondary Contact Name">
                    <input type="text" name="secondaryContactPhone" class="form-control mb-3" placeholder="Secondary Contact Phone">
                    <input type="text" name="secondaryContactTitle" class="form-control mb-3" placeholder="Secondary Contact Title">
                    <input type="text" name="clientLogo" class="form-control mb-3" placeholder="Client Logo">
                    <input type="text" name="clientAddress" class="form-control mb-3" placeholder="Client Address">
                    <input type="date" name="clientJoinDate" class="form-control mb-3" placeholder="Client Join Date">
                    <input type="date" name="ClientYearEnd" class="form-control mb-3" placeholder="Client Year End">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
<?php foreach ($clients as $client): ?>
    <div class="modal fade" id="editClientModal<?php echo $client['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?php echo BASE_URL; ?>/clients">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="client_name" class="form-control mb-3" value="<?php echo htmlspecialchars($client['client_name']); ?>" required>
                        <input type="text" name="accountExecutive" class="form-control mb-3" value="<?php echo htmlspecialchars($client['accountExecutive']); ?>" required>
                        <input type="email" name="contactEmail" class="form-control mb-3" value="<?php echo htmlspecialchars($client['contactEmail']); ?>" required>
                        <input type="text" name="contactPhone" class="form-control mb-3" value="<?php echo htmlspecialchars($client['contactPhone']); ?>" required>
                        <input type="text" name="contactName" class="form-control mb-3" value="<?php echo htmlspecialchars($client['contactName']); ?>">
                        <input type="text" name="contactTitle" class="form-control mb-3" value="<?php echo htmlspecialchars($client['contactTitle']); ?>">
                        <input type="email" name="secondaryContactEmail" class="form-control mb-3" value="<?php echo htmlspecialchars($client['secondaryContactEmail']); ?>">
                        <input type="text" name="secondaryContactName" class="form-control mb-3" value="<?php echo htmlspecialchars($client['secondaryContactName']); ?>">
                        <input type="text" name="secondaryContactPhone" class="form-control mb-3" value="<?php echo htmlspecialchars($client['secondaryContactPhone']); ?>">
                        <input type="text" name="secondaryContactTitle" class="form-control mb-3" value="<?php echo htmlspecialchars($client['secondaryContactTitle']); ?>">
                        <input type="text" name="clientLogo" class="form-control mb-3" value="<?php echo htmlspecialchars($client['clientLogo']); ?>">
                        <input type="text" name="clientAddress" class="form-control mb-3" value="<?php echo htmlspecialchars($client['clientAddress']); ?>">
                        <input type="date" name="clientJoinDate" class="form-control mb-3" value="<?php echo htmlspecialchars($client['clientJoinDate']); ?>">
                        <input type="date" name="ClientYearEnd" class="form-control mb-3" value="<?php echo htmlspecialchars($client['ClientYearEnd']); ?>">
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
