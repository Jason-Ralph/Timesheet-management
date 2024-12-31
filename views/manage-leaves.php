<!-- views/manage-leaves.php -->
<?php include(__DIR__ . '/../includes/header.php'); ?>

<div class="container mt-5">
    <h2>Manage Leave Applications</h2>

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

    <?php if (empty($pending_leaves)): ?>
        <p>No pending leave applications.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Leave Type</th>
                    <th>Reason</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Days</th>
                    <th>Applied At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_leaves as $leave): ?>
                    <tr>
                        <td><?= htmlspecialchars($leave['username']); ?></td>
                        <td><?= ucfirst(htmlspecialchars($leave['leave_type'])); ?></td>
                        <td><?= htmlspecialchars($leave['reason'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($leave['start_date']); ?></td>
                        <td><?= htmlspecialchars($leave['end_date']); ?></td>
                        <td><?= htmlspecialchars($leave['total_days']); ?></td>
                        <td><?= htmlspecialchars($leave['applied_at']); ?></td>
                        <td>
                            <form method="POST" action="<?= BASE_URL; ?>/approve-leave" style="display:inline-block;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="leave_id" value="<?= htmlspecialchars($leave['id']); ?>">
                                
                                <div class="mb-2">
                                    <label for="comments" class="form-label">Comments (Optional)</label>
                                    <textarea name="comments" class="form-control" rows="2" placeholder="Add comments here..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" action="<?= BASE_URL; ?>/reject-leave" style="display:inline-block; margin-left:5px;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="leave_id" value="<?= htmlspecialchars($leave['id']); ?>">
                                
                                <div class="mb-2">
                                    <label for="comments" class="form-label">Comments (Optional)</label>
                                    <textarea name="comments" class="form-control" rows="2" placeholder="Add comments here..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include(__DIR__ . '/../includes/footer.php'); ?>
