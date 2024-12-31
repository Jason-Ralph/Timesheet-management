<?php
$csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<?php include(ASSET_PATH . '/includes/header.php'); ?>
<div class="content">
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class=" mt-5">
        <!-- Display success and error messages -->
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

		  
		  
		  
        <h1>Tasks Timesheet</h1>
		  <div class="row">
			  
		<div class="col-4">	  
		  <?php if ($permissionHelperController->hasPermission('Add task')): ?> 	  
    <?php if (!$hasUnfinishedTasks): ?>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTaskModal">Add Timesheet</button>
    <?php else: ?>
        <button class="btn btn-primary mb-3" disabled title="You have an unfinished task. Finish it before adding a new one.">
            Add Timesheet
        </button>
        <div class="alert alert-warning mt-2">
            You have an unfinished task. Please finish it before adding a new one.
        </div>
    <?php endif; ?>
<?php endif; ?>   			  
			  
		<?php if ($permissionHelperController->hasPermission('Access tasks recycle bin')): ?>  
        <a class="btn btn-primary mb-3" href="task-recycle-bin">Recycle Bin</a>
		<?php endif; ?> 
	</div>  
		
	<div class="col-6">	
		
		<?php if ($permissionHelperController->hasPermission('Edit task')): ?>  
		 <!-- Filter Form -->
        <form id="filtersForm">
          <div class="row">
            <div class="col-6">
              <label for="dateRangeFilter">Date Range</label>
              <input 
                type="text" 
                id="dateRangeFilter" 
                class="form-control" 
                placeholder="Select a date range" 
                readonly
              >
            </div>
            
          </div>
        </form>
		<?php endif; ?> 
		
		</div>
		
		  
		</div>  
		  
        <style>
		 form#filtersForm {
    margin: 30px 0px;
} 
		 form#filtersForm .row {
    justify-content: space-around;
    text-align: center;
} 
	form#filtersForm .form-control {
    text-align: center;
}  
		 </style> 
		  
        <table class="table table-striped display" <?php if ($permissionHelperController->hasPermission('Edit task')): ?>id="tasksTable"<?php endif; ?>>
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
            <?php foreach ($tasks as $task): ?>
			<?php 
			$taskStartTime =  htmlspecialchars((new DateTime($task['taskStartTime']))->format('Y-m-d H:i:s'));
            echo "<div id='live-timer' data-start-time='$taskStartTime'></div>";
            ?>  
			  
			  
			  
            <tr>
<td><?php echo htmlspecialchars($task['taskType']); ?></td>
<td><?php echo htmlspecialchars($task['client']); ?></td>
<td><?php echo htmlspecialchars($task['reportType']); ?></td>
<td><?php echo htmlspecialchars((new DateTime($task['taskStartTime']))->format('Y-m-d H:i:s')); ?></td>
<td>
    <?php
    if (!empty($task['taskEndTime'])) {
        echo htmlspecialchars((new DateTime($task['taskEndTime']))->format('Y-m-d H:i:s'));
    } else {
        echo 'TBD';
    }
    ?>
</td>
<td>
    <div class="live-timer" 
         data-start-time="<?php echo htmlspecialchars($task['taskStartTime']); ?>" 
         data-end-time="<?php echo !empty($task['taskEndTime']) ? htmlspecialchars($task['taskEndTime']) : ''; ?>">
    </div>
    <div class="timer-display">00:00:00</div>
</td>
<td><?php echo htmlspecialchars($task['taskTotalTime'] ?? 'TBD'); ?></td>
<td>
	
	
	
<?php if (
    $permissionHelperController->hasPermission('Edit task') &&
    empty($task['taskEndTime']) &&
    empty($task['taskTotalTime'])
): ?>
    <!-- Finish Task Form -->
    <form method="POST" action="<?php echo BASE_URL; ?>/finish-task" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($task['id']); ?>">
        <input type="hidden" name="column" value="taskEndTime">
        <input type="hidden" name="value" value="<?php echo date('Y-m-d H:i:s'); ?>"> <!-- Sets taskEndTime to current timestamp -->
        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to finish this task?');">Finish</button>

    </form>
<?php endif; ?>	
	
<?php if ($permissionHelperController->hasPermission('View task')): ?> 
    <a href="<?php echo BASE_URL; ?>/task-profile?id=<?php echo $task['id']; ?>" class="btn btn-info btn-sm">View</a>
 <?php endif; ?>	
<?php if ($permissionHelperController->hasPermission('Edit task')): ?>
    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editTaskModal<?php echo $task['id']; ?>">Edit</button>
<?php endif; ?>	
	
<?php if ($permissionHelperController->hasPermission('Delete task')): ?> 	
    <form method="POST" action="<?php echo BASE_URL; ?>/tasks" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this task?');">Delete</button>
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

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?php echo BASE_URL; ?>/tasks">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="modal-header">
          <h5 class="modal-title">Add Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label for="taskDesc">Task Description</label>
          <select name="taskDesc" class="form-control mb-3" required>
            <option value="">Select Task</option>
            <?php foreach ($taskDescriptions as $taskDesc): ?>
              <option value="<?php echo $taskDesc['taskName']; ?>"><?php echo htmlspecialchars($taskDesc['taskName']); ?></option>
            <?php endforeach; ?>
          </select>

          <label for="client_select">Client</label>
          <select name="client_select" class="form-control mb-3" required>
            <option value="">Select Client</option>
            <?php foreach ($clients as $client): ?>
              <option value="<?php echo $client['client_name']; ?>"><?php echo htmlspecialchars($client['client_name']); ?></option>
            <?php endforeach; ?>
          </select>

          <label for="report_select">Report Type</label>
          <select name="report_select" class="form-control mb-3" required>
            <option value="">Select Report</option>
            <?php foreach ($reports as $report): ?>
              <option value="<?php echo $report['reportType']; ?>"><?php echo htmlspecialchars($report['reportType']); ?></option>
            <?php endforeach; ?>
          </select>

          <label for="taskStartTime">Start Time</label>
          <input type="datetime-local" name="taskStartTime" class="form-control mb-3" required>

          <label for="comments">Comments</label>
          <input type="text" name="comments" class="form-control mb-3" placeholder="Comments">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add Task</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Task Modal -->
<?php foreach ($tasks as $task): ?>
<div class="modal fade" id="editTaskModal<?php echo $task['id']; ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?php echo BASE_URL; ?>/tasks">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
        <div class="modal-header">
          <h5 class="modal-title">Edit Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Only allow editing of 'client', 'taskType', and 'reportType' -->
          <label for="taskDesc">Task Description</label>
          <select name="taskDesc" class="form-control mb-3" required>
            <option value="">Select Task</option>
            <?php foreach ($taskDescriptions as $taskDesc): ?>
              <option value="<?php echo $taskDesc['taskName']; ?>" <?php if ($taskDesc['taskName'] == $task['taskType']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($taskDesc['taskName']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label for="client_select">Client</label>
          <select name="client_select" class="form-control mb-3" required>
              <option value="">Select Client</option>
              <?php foreach ($clients as $client): ?>
                  <option value="<?php echo htmlspecialchars($client['client_name']); ?>" 
                      <?php echo ($client['client_name'] === $task['client']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($client['client_name']); ?>
                  </option>
              <?php endforeach; ?>
          </select>

          <label for="report_select">Report Type</label>
          <select name="report_select" class="form-control mb-3" required>
            <option value="">Select Report</option>
            <?php foreach ($reports as $report): ?>
              <option value="<?php echo $report['reportType']; ?>" <?php if ($report['reportType'] == $task['reportType']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($report['reportType']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
    // Update timer for each task dynamically
    document.querySelectorAll('tr').forEach(row => {
        const timerDisplay = row.querySelector('.timer-display'); // Use class to avoid duplicate IDs
        const liveTimer = row.querySelector('.live-timer'); // Use class to avoid duplicate IDs
        if (!timerDisplay || !liveTimer) return;

        const taskStartTimeString = liveTimer.getAttribute('data-start-time');
        const taskEndTimeString = liveTimer.getAttribute('data-end-time');
        const startTime = new Date(taskStartTimeString).getTime();
        const endTime = taskEndTimeString ? new Date(taskEndTimeString).getTime() : null;

        function updateTimer() {
            const currentTime = new Date().getTime();
            let elapsed;

            // Calculate elapsed time
            if (endTime) {
                elapsed = endTime - startTime;
            } else {
                elapsed = currentTime - startTime;
            }

            // Stop if elapsed is invalid
            if (elapsed < 0) {
                timerDisplay.textContent = 'Invalid';
                return;
            }

            // Calculate hours, minutes, and seconds
            const hours = Math.floor(elapsed / (1000 * 60 * 60));
            const minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((elapsed % (1000 * 60)) / 1000);

            // Format time as HH:MM:SS
            const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

            // Update the display
            timerDisplay.textContent = formattedTime;

            // Stop the timer if end time exists
            if (endTime) {
                clearInterval(interval);
            }
        }

        // Start the timer
        const interval = setInterval(updateTimer, 1000);
        updateTimer(); // Run immediately to avoid delay
    });
</script>

<script>
  let table;
  let dateRangeStart = null;
  let dateRangeEnd   = null;

  // Custom filter for DataTables
  $.fn.dataTable.ext.search.push(function(settings, data) {
    // "Start Time" is column index 3 in your table
    const startTimeString = data[3]; 
    if (!startTimeString) return true; // no date -> include row

    const rowDate = moment(startTimeString, 'YYYY-MM-DD HH:mm:ss');
    if (!rowDate.isValid()) return true; // can't parse -> include

    // If a range is selected, filter out rows outside the range
    if (dateRangeStart && rowDate.isBefore(dateRangeStart)) {
      return false;
    }
    if (dateRangeEnd && rowDate.isAfter(dateRangeEnd)) {
      return false;
    }
    return true;
  });

  document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable once
    table = $('#tasksTable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      columnDefs: [
        { orderable: false, targets: [7] } // disable sorting on Actions column
      ]
    });

    // Initialize Date Range Picker
    $('#dateRangeFilter').daterangepicker({
      opens: 'left',
      autoUpdateInput: false,
      locale: { cancelLabel: 'Clear' }
    }, function(start, end) {
      // On "Apply"
      $('#dateRangeFilter').val(
        start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY')
      );
      dateRangeStart = start.startOf('day');
      dateRangeEnd   = end.endOf('day');
      table.draw();
    });

    // If user clicks "Clear"
    $('#dateRangeFilter').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
      dateRangeStart = null;
      dateRangeEnd   = null;
      table.draw();
    });
  });
</script>

<script>
$(document).ready(function(){
    // Handle Finish Task form submission
    $('form[action="<?php echo BASE_URL; ?>/finish-task"]').on('submit', function(e){
        e.preventDefault();
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response){
                if(response.success){
                    alert(response.message);
                    // Optionally, refresh the page or update the task's UI to reflect it's finished
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(){
                alert('An unexpected error occurred.');
            }
        });
    });

   
});
</script>

<?php include(ASSET_PATH . '/includes/footer.php'); ?>
