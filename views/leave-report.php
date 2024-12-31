<!-- views/leave-report.php -->
<?php include(__DIR__ . '/../includes/header.php'); ?>

<div class="container mt-5">
    <h2>Leave Report</h2>

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

    <form method="GET" action="<?= BASE_URL; ?>/leave-report" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" required value="<?= htmlspecialchars($start_date ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" required value="<?= htmlspecialchars($end_date ?? ''); ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </div>
        </div>
    </form>

    <?php if (isset($report_leaves)): ?>
        <h4>Report from <?= htmlspecialchars($start_date); ?> to <?= htmlspecialchars($end_date); ?></h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
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
                <?php foreach ($report_leaves as $leave): ?>
                    <tr>
                        <td><?= htmlspecialchars($leave['username']); ?></td>
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
