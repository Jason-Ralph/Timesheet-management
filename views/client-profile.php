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

    <h1>Client Profile: <?php echo htmlspecialchars($client['client_name']); ?></h1>

	<img src="<?php echo htmlspecialchars($client['clientLogo']); ?>" style="max-width: 100%; display: block; margin: 0 auto 30px auto;"  alt=""/>
				
				
    <table class="table table-bordered">
        <tr>
            <th>Client Name</th>
            <td><?php echo htmlspecialchars($client['client_name']); ?></td>
        </tr>
        <tr>
            <th>Account Executive</th>
            <td><?php echo htmlspecialchars($client['accountExecutive']); ?></td>
        </tr>
        <tr>
            <th>Contact Email</th>
			<td><a href="mailto:<?php echo htmlspecialchars($client['contactEmail']); ?>"><i class="email"></i><?php echo htmlspecialchars($client['contactEmail']); ?></a></td>
        </tr>
        <tr>
            <th>Contact Phone</th>
            <td><a href="tel:<?php echo htmlspecialchars($client['contactPhone']); ?>"><i class="phone"></i><?php echo htmlspecialchars($client['contactPhone']); ?></a></td>
        </tr>
        <tr>
            <th>Contact Name</th>
            <td><?php echo htmlspecialchars($client['contactName']); ?></td>
        </tr>
        <tr>
            <th>Contact Title</th>
            <td><?php echo htmlspecialchars($client['contactTitle']); ?></td>
        </tr>
        <tr>
            <th>Secondary Contact Email</th>
            <td><?php echo htmlspecialchars($client['secondaryContactEmail']); ?></td>
        </tr>
        <tr>
            <th>Secondary Contact Name</th>
            <td><?php echo htmlspecialchars($client['secondaryContactName']); ?></td>
        </tr>
        <tr>
            <th>Secondary Contact Phone</th>
            <td><?php echo htmlspecialchars($client['secondaryContactPhone']); ?></td>
        </tr>
        <tr>
            <th>Secondary Contact Title</th>
            <td><?php echo htmlspecialchars($client['secondaryContactTitle']); ?></td>
        </tr>
        <tr>
            <th>Client Address</th>
            <td><?php echo htmlspecialchars($client['clientAddress']); ?></td>
        </tr>
        <tr>
            <th>Client Join Date</th>
            <td><?php echo htmlspecialchars($client['clientJoinDate']); ?></td>
        </tr>
        <tr>
            <th>Client Year End</th>
            <td><?php echo htmlspecialchars($client['ClientYearEnd']); ?></td>
        </tr>
    </table>

    <a href="<?php echo BASE_URL; ?>/clients" class="btn btn-primary">Back to Clients</a>
</div>
        </article>
    </section>
</div>



<?php include(ASSET_PATH . '/includes/footer.php'); ?>
