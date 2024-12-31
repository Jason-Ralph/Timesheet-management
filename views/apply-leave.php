<!-- views/apply-leave.php -->
<?php include(__DIR__ . '/../includes/header.php'); ?>

<div class="container mt-5">
    <h2>Apply for Leave</h2>

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

    <form method="POST" action="<?= BASE_URL; ?>/apply-leave">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">

        <div class="mb-3">
            <label for="leave_type" class="form-label">Leave Type</label>
            <select name="leave_type" id="leave_type" class="form-select" required>
                <option value="">Select Leave Type</option>
                <option value="sick">Sick Leave</option>
                <option value="annual">Annual Leave</option>
                <option value="study">Study Leave</option>
                <option value="family_responsibility">Family Responsibility Leave</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="mb-3" id="reason_field" style="display: none;">
            <label for="reason" class="form-label">Reason for Other Leave</label>
            <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Please specify the reason for your leave."></textarea>
        </div>

        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Apply for Leave</button>
    </form>
</div>

<script>
    // JavaScript to toggle the reason field based on leave type selection
    document.getElementById('leave_type').addEventListener('change', function() {
        var reasonField = document.getElementById('reason_field');
        if (this.value === 'other') {
            reasonField.style.display = 'block';
        } else {
            reasonField.style.display = 'none';
        }
    });
</script>

<?php include(__DIR__ . '/../includes/footer.php'); ?>
