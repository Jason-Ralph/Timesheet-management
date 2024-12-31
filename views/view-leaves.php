<!-- views/view-leaves.php -->
<?php include(__DIR__ . '/../includes/header.php'); ?>

<div class="container mt-5">
    <h2>Your Leave History</h2>

    <!-- Display Success or Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($leaves)): ?>
        <p>You have no leave applications.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Reason</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Days</th>
                    <th>Status</th>
                    <th>Applied At</th>
                    <th>Approved By</th>
                    <th>Approved At</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaves as $leave): ?>
                    <tr>
                        <td><?= ucfirst(htmlspecialchars($leave['leave_type'])); ?></td>
                        <td><?= htmlspecialchars($leave['reason'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($leave['start_date']); ?></td>
                        <td><?= htmlspecialchars($leave['end_date']); ?></td>
                        <td><?= htmlspecialchars($leave['total_days']); ?></td>
                        <td>
                            <?php
                                switch ($leave['status']) {
                                    case 'pending':
                                        echo '<span class="badge bg-warning text-dark">Pending</span>';
                                        break;
                                    case 'approved':
                                        echo '<span class="badge bg-success">Approved</span>';
                                        break;
                                    case 'rejected':
                                        echo '<span class="badge bg-danger">Rejected</span>';
                                        break;
                                    default:
                                        echo '-';
                                }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($leave['applied_at']); ?></td>
                        <td><?= htmlspecialchars($leave['approved_by'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($leave['approved_at'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($leave['comments'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include(__DIR__ . '/../includes/footer.php'); ?>
