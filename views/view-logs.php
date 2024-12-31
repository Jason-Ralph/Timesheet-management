<?php
$csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<?php include(ASSET_PATH . '/includes/header.php'); ?>

<div class="content">
    <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_edpg3c3s.json" background="transparent" speed="0.3" style="width: 200px; height: 200px; position:absolute; bottom:30px; right:30px; z-index:0; opacity:0.3;" autoplay></lottie-player>    
    
	<div class="menu-trigger"></div>	
	
	 <section class="profile">
        <article>
	
	
	
    <h1>System Logs</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Department</th>
                <th>Page/Action</th>
                <th>Action Type</th>
                <th>Level</th>
                <th>Date & Time</th>
                <th>Additional Info</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['id']); ?></td>
                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                        <td><?php echo htmlspecialchars($log['department']); ?></td>
                        <td><?php echo htmlspecialchars($log['page']); ?></td>
                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php echo htmlspecialchars($log['level']); ?></td>
                        <td><?php echo htmlspecialchars($log['date_time']); ?></td>
                        <td><?php echo htmlspecialchars($log['additional_info']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11">No logs available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
        </article>
    </section>
</div>

<?php include(ASSET_PATH . '/includes/footer.php'); ?>
