<?php
$csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<?php include(ASSET_PATH . '/includes/header.php'); ?>
<div class="content">
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class="">
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

        <h1>Admin Timesheet</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTaskModal">
          Add timesheet
        </button>
        <a class="btn btn-primary mb-3" href="task-recycle-bin">Recycle Bin</a>
         <!-- Export to Excel Button -->
        <a href="<?php echo BASE_URL; ?>/export-task-excel" class="btn btn-success mb-3">
          Export to Excel
        </a
        <!-- Filter Form -->
        <form id="filtersForm">
          <div class="row">
            <div class="col-2">
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
        <style>
		 form#filtersForm {
    margin: 30px 0px;
} 
		  
		  
		 </style>
        <!-- DataTable -->
        <table id="adminTasksTable" class="table table-striped display">
          <thead>
            <tr>
              <th>User&nbsp;Name</th>
              <th>Task&nbsp;Name</th>
              <th>Client</th>
              <th>Report&nbsp;Type</th>
              <th>Start&nbsp;Time</th>
              <th>End&nbsp;Time</th>
              <th>Time&nbsp;so&nbsp;far</th>
              <th>Total&nbsp;Time</th>
              <th>Quoted&nbsp;Cost</th>
              <th>Actual&nbsp;Cost</th>
              <th>Late&nbsp;Work</th>
              <th>Overtime</th>
              <th>Comments</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tasks as $task): ?>
              <?php 
                $taskStartTime = htmlspecialchars(
                  (new DateTime($task['taskStartTime']))->format('Y-m-d H:i:s')
                );
                // hidden timer data
                echo "<div id='live-timer' data-start-time='$taskStartTime'></div>";
              ?>
              <tr>
                <td><?php echo htmlspecialchars($task['userName']); ?></td>
                <td><?php echo htmlspecialchars($task['taskType']); ?></td>
                <td><?php echo htmlspecialchars($task['client']); ?></td>
                <td><?php echo htmlspecialchars($task['reportType']); ?></td>
                <td>
                  <?php 
                    echo htmlspecialchars(
                      (new DateTime($task['taskStartTime']))->format('Y-m-d H:i:s')
                    );
                  ?>
                </td>
                <td>
                  <?php
                    if (!empty($task['taskEndTime'])) {
                      echo htmlspecialchars(
                        (new DateTime($task['taskEndTime']))->format('Y-m-d H:i:s')
                      );
                    } else {
                      echo 'TBD';
                    }
                  ?>
                </td>
                <td>
                  <div 
                    class="live-timer"
                    data-start-time="<?php echo htmlspecialchars($task['taskStartTime']); ?>"
                    data-end-time="<?php echo !empty($task['taskEndTime']) 
                      ? htmlspecialchars($task['taskEndTime']) 
                      : ''; ?>"
                  ></div>
                  <div class="timer-display">00:00:00</div>
                </td>
                <td><?php echo htmlspecialchars($task['taskTotalTime'] ?? 'TBD'); ?></td>
                <td 
                  contenteditable="true"
                  data-id="<?php echo $task['id']; ?>"
                  data-column="taskQuotedCost"
                  class="editable"
                >
                  <?php echo htmlspecialchars('R' . $task['taskQuotedCost'] ?? 'TBD'); ?>
                </td>
                <td><?php echo htmlspecialchars(('R' . $task['taskActualCost']) ?? 'TBD'); ?></td>
                <td><?php echo $task['taskLateWork'] ? '✔️' : '❌'; ?></td>
                <td>
                  <input 
                    type="checkbox"
                    class="overtime-checkbox"
                    data-id="<?php echo $task['id']; ?>"
                    data-column="taskOvertime"
                    <?php echo $task['taskOvertime'] ? 'checked' : ''; ?>
                  >
                </td>
                <td>
                  <?php
                    $maxLength = 50; 
                    echo htmlspecialchars(mb_strimwidth($task['comments'], 0, $maxLength, '...'));
                  ?>
                </td>
                <td>
                  <a 
                    href="admin-task-profile?id=<?php echo $task['id']; ?>" 
                    class="btn btn-info btn-sm"
                  >
                    View
                  </a>
                  <form 
                    method="POST" 
                    id="submit" 
                    action="delete-task.php" 
                    style="display:inline;"
                  >
                    <input 
                      type="hidden" 
                      id="task_id" 
                      value="<?php echo $task['id']; ?>"
                    >
                    <button 
                      type="submit" 
                      class="btn btn-danger btn-sm"
                    >
                      Delete
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

     
        

        <!-- Edit Task Modal -->
        <?php foreach ($tasks as $task): ?>
          <div class="modal fade" id="editTaskModal<?php echo $task['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST" id="scrf-2" action="<?php echo BASE_URL; ?>/tasks">
                  <input 
                    type="hidden" 
                    id="csrf_token" 
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                  >
                  <input 
                    type="hidden" 
                    id="id" 
                    value="<?php echo $task['id']; ?>"
                  >
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Task</h5>
                    <button 
                      type="button" 
                      class="btn-close" 
                      data-bs-dismiss="modal"
                    ></button>
                  </div>
                  <div class="modal-body">
                    <label for="taskDesc">Task Description</label>
                    <select id="taskDesc" class="form-control mb-3" required>
                      <option value="">Select Task</option>
                      <?php foreach ($taskDescriptions as $taskDesc): ?>
                        <option value="<?php echo $taskDesc['taskName']; ?>"
                          <?php if ($taskDesc['taskName'] == $task['taskType']) echo 'selected'; ?>
                        >
                          <?php echo htmlspecialchars($taskDesc['taskName']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>

                    <label for="client_select">Client</label>
                    <select id="client_select" class="form-control mb-3" required>
                      <option value="">Select Client</option>
                      <?php foreach ($clients as $client): ?>
                        <option value="<?php echo htmlspecialchars($client['client_name']); ?>"
                          <?php echo ($client['client_name'] === $task['client']) ? 'selected' : ''; ?>
                        >
                          <?php echo htmlspecialchars($client['client_name']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>

                    <label for="report_select">Report Type</label>
                    <select id="report_select" class="form-control mb-3" required>
                      <option value="">Select Report</option>
                      <?php foreach ($reports as $report): ?>
                        <option value="<?php echo $report['reportType']; ?>"
                          <?php if ($report['reportType'] == $task['reportType']) echo 'selected'; ?>
                        >
                          <?php echo htmlspecialchars($report['reportType']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>

                    <label for="taskStartTime">Start Time</label>
                    <input 
                      type="datetime" 
                      id="taskStartTime" 
                      class="form-control mb-3" 
                      value="<?php echo htmlspecialchars($task['taskStartTime']); ?>"
                    >

                    <label for="taskEndTime">End Time</label>
                    <input 
                      type="datetime-local" 
                      id="taskEndTime" 
                      class="form-control mb-3"
                      value="<?php echo htmlspecialchars(isset($task['taskEndTime']) && $task['taskEndTime']
                        ? (new DateTime($task['taskEndTime']))->format('Y-m-d\TH:i')
                        : ''
                      ); ?>"
                    >

                    <label for="comments">Comments</label>
                    <input 
                      type="text" 
                      id="comments" 
                      class="form-control mb-3" 
                      value="<?php echo htmlspecialchars($task['comments']); ?>"
                    >
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- Timer & Update Logic Scripts -->
        <script>
          // Update timer for each task dynamically
          document.querySelectorAll('tr').forEach(row => {
            const timerDisplay = row.querySelector('.timer-display');
            const liveTimer    = row.querySelector('.live-timer');
            if (!timerDisplay || !liveTimer) return;

            const taskStartTimeString = liveTimer.getAttribute('data-start-time');
            const taskEndTimeString   = liveTimer.getAttribute('data-end-time');
            const startTime = new Date(taskStartTimeString).getTime();
            const endTime   = taskEndTimeString ? new Date(taskEndTimeString).getTime() : null;

            function updateTimer() {
              const currentTime = new Date().getTime();
              let elapsed;

              if (endTime) {
                elapsed = endTime - startTime;
              } else {
                elapsed = currentTime - startTime;
              }

              if (elapsed < 0) {
                timerDisplay.textContent = 'Invalid';
                return;
              }

              const hours   = Math.floor(elapsed / (1000 * 60 * 60));
              const minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
              const seconds = Math.floor((elapsed % (1000 * 60)) / 1000);
              const formattedTime = 
                String(hours).padStart(2, '0') + ':' + 
                String(minutes).padStart(2, '0') + ':' + 
                String(seconds).padStart(2, '0');

              timerDisplay.textContent = formattedTime;
              if (endTime) clearInterval(interval);
            }

            const interval = setInterval(updateTimer, 1000);
            updateTimer();
          });

          // Inline editing for Quoted Cost
          document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.editable').forEach(cell => {
              cell.addEventListener('blur', () => {
                const id     = cell.dataset.id;
                const column = cell.dataset.column;
                const value  = cell.textContent.trim();

                fetch('/TOTG-admin-center/public/update-task', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id, column, value })
                })
                .then(response => response.json())
                .then(data => {
                  if (data.success) {
                    cell.style.backgroundColor = 'lightgreen';
                    setTimeout(() => cell.style.backgroundColor = '', 2000);
                  } else {
                    alert(data.message || 'Failed to update task');
                    cell.style.backgroundColor = 'lightcoral';
                  }
                })
                .catch(error => {
                  console.error('Error:', error);
                  cell.style.backgroundColor = 'lightcoral';
                });
              });
            });

            // Overtime checkbox
            document.querySelectorAll('.overtime-checkbox').forEach(checkbox => {
              checkbox.addEventListener('change', () => {
                const id     = checkbox.dataset.id;
                const column = checkbox.dataset.column;
                const value  = checkbox.checked ? 1 : 0;

                fetch('/TOTG-admin-center/public/update-task', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id, column, value })
                })
                .then(response => response.json())
                .then(data => {
                  if (data.success) {
                    checkbox.closest('td').style.backgroundColor = 'lightgreen';
                    setTimeout(() => checkbox.closest('td').style.backgroundColor = '', 2000);
                  } else {
                    alert(data.message || 'Failed to update task');
                    checkbox.closest('td').style.backgroundColor = 'lightcoral';
                  }
                })
                .catch(error => {
                  console.error('Error:', error);
                  checkbox.closest('td').style.backgroundColor = 'lightcoral';
                });
              });
            });
          });
        </script>

        <!-- DataTables + DateRangePicker Filter Script -->
        <script>
          let table;
          let dateRangeStart = null;
          let dateRangeEnd   = null;

          function applyFilters() {
            // If #dateRangeFilter is empty, clear the date range
            const val = document.getElementById('dateRangeFilter').value;
            if (!val) {
              dateRangeStart = null;
              dateRangeEnd   = null;
            } else {
              // val e.g. "13/06/2023 - 15/06/2023" (DD/MM/YYYY - DD/MM/YYYY)
              const parts = val.split(' - ');
              if (parts.length === 2) {
                // parse with moment
                dateRangeStart = moment(parts[0], 'DD/MM/YYYY').startOf('day');
                dateRangeEnd   = moment(parts[1], 'DD/MM/YYYY').endOf('day');
              }
            }
            table.draw();
          }

          // DataTables custom filter
          $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
              // data[4] is the "Start Time" column (0=User Name,1=Task Name,2=Client,3=Report Type,4=Start Time,...)
              const startTimeString = data[4]; 
              if (!startTimeString) return true; // no date -> include

              const rowDate = moment(startTimeString, 'YYYY-MM-DD HH:mm:ss');
              if (!rowDate.isValid()) return true; // if can't parse date, include row

              if (dateRangeStart && rowDate.isBefore(dateRangeStart)) {
                return false; 
              }
              if (dateRangeEnd && rowDate.isAfter(dateRangeEnd)) {
                return false;
              }
              return true;
            }
          );

          document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            table = $('#adminTasksTable').DataTable({
              paging: true,
              searching: true,
              ordering: true,
              columnDefs: [
                { orderable: false, targets: [7] }
              ]
            });

            // Initialize date range picker
            $('#dateRangeFilter').daterangepicker({
              opens: 'left',
              autoUpdateInput: false,
              locale: { cancelLabel: 'Clear' }
            }, function(start, end) {
              // on apply
              $('#dateRangeFilter').val(
                start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY')
              );
              applyFilters();
            });

            // if user clicks "clear" in the picker
            $('#dateRangeFilter').on('cancel.daterangepicker', function(ev, picker) {
              $(this).val('');
              applyFilters();
            });

            // Buttons
            document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
            document.getElementById('clearFiltersBtn').addEventListener('click', function() {
              document.getElementById('filtersForm').reset();
              $('#dateRangeFilter').val('');
              applyFilters();
            });
          });
        </script>

        <?php include(ASSET_PATH . '/includes/footer.php'); ?>
      </div>
    </article>
  </section>
</div>
