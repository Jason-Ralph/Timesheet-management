<?php include(ASSET_PATH . '/includes/header.php'); ?>
<div class="content">
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class="container mt-5">
        <h1>Task Recycle Bin</h1>
        <a class="btn btn-primary mb-3" href="tasks">Back to Tasks</a>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Task Name</th>
              <th>Client</th>
              <th>Report Type</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Time so far</th>
              <th>Total Time</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($tasks)): ?>  
              <?php foreach ($tasks as $task): ?>
			  <?php $taskStartTime =  htmlspecialchars((new DateTime($task['taskStartTime']))->format('Y-m-d H:i:s'));
echo "<div id='live-timer' data-start-time='$taskStartTime'></div>"; ?>
              <tr>
                <td><?php echo htmlspecialchars($task['taskType']); ?></td>
                <td><?php echo htmlspecialchars($task['client']); ?></td>
                <td><?php echo htmlspecialchars($task['reportType']); ?></td>
                <td><?php echo htmlspecialchars((new DateTime($task['taskStartTime']))->format('Y-m-d H:i:s')); ?></td>
                <td><?php
        if (!empty($task['taskEndTime'])) {
            echo htmlspecialchars((new DateTime($task['taskEndTime']))->format('Y-m-d H:i:s'));
        } else {
            echo 'TBD';
        }
        ?></td>
                <td>
        <div class="live-timer" 
             data-start-time="<?php echo htmlspecialchars($task['taskStartTime']); ?>" 
             data-end-time="<?php echo !empty($task['taskEndTime']) ? htmlspecialchars($task['taskEndTime']) : ''; ?>">
        </div>
        <div class="timer-display">00:00:00</div>
    </td>
    <td><?php echo htmlspecialchars($task['taskTotalTime'] ?? 'TBD'); ?></td>
                <td>
                  <form method="POST" action="<?php echo BASE_URL; ?>/restore-task" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                    <button type="submit" class="btn btn-success btn-sm">Restore</button>
                  </form>
                  <form method="POST" action="<?php echo BASE_URL; ?>/permanent-delete-task" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7">No tasks in the recycle bin.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>
  </section>
</div>
<?php include(ASSET_PATH . '/includes/footer.php'); ?>
