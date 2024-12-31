<?php
$csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<?php include(ASSET_PATH . '/includes/header.php'); ?>
<div class="content">
  <!-- Lottie Animation -->
  <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_edpg3c3s.json" background="transparent" speed="0.3" style="width: 200px; height: 200px; position:absolute; bottom:30px; right:30px; z-index:0; opacity:0.3;" autoplay></lottie-player>
  
  <div class="menu-trigger"></div>
  
  <section class="profile">
    <article>
      <div class="mt-5">
        <!-- Display Success and Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
          <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
          </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
          <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
          </div>
        <?php endif; ?>  

        <h1>Admin Task Profile</h1>

        <!-- Task Profile Card -->
        <div class="row gap-30">
          <div class="col-md-8 task-profile-card-left">
            <div class="card mb-4">
              <!-- Banner -->
              <div class="task-profile-banner"></div>
              
              <div class="task-profile-content-row">
                <!-- Client Logo -->
                <div class="task-profile-image">
                  <img src="<?php echo htmlspecialchars($task['clientLogo'], ENT_QUOTES, 'UTF-8'); ?>" alt="Client Logo" class="img-fluid"/>
                </div>
                
                <!-- Task Details -->
                <div class="task-profile-content">
                  <div class="basic">
                    <p class="name"><strong><?php echo htmlspecialchars($task['client']); ?></strong></p>    
                    <p class="title"><strong>Task:</strong> <?php echo htmlspecialchars($task['taskType']); ?></p>    
                    <p class="department"><strong>Report Type:</strong> <?php echo htmlspecialchars($task['reportType']); ?></p>    
                    <p class="manager"><strong>Start Task Time:</strong> <?php echo htmlspecialchars((new DateTime($task['taskStartTime']))->format('Y-m-d H:i:s')); ?></p>    
                    <p class="roleId"><strong>End Task Time:</strong> <?php echo htmlspecialchars(!empty($task['taskEndTime']) ? (new DateTime($task['taskEndTime']))->format('Y-m-d H:i:s') : 'TBD'); ?></p>		
                    <p class="roleId"><strong>Total Task Time:</strong> <?php echo htmlspecialchars(!empty($task['taskTotalTime']) ? $task['taskTotalTime'] : 'TBD'); ?></p>		
                  </div>
                  
                  <div class="socials mt-3">
                    <p><strong>Comments:</strong><br>
                      <?php echo htmlspecialchars(!empty($task['comments']) ? $task['comments'] : 'Sorry, no comments.'); ?>		
                    </p>	
                  </div>
                </div>
                
                <!-- Company Logo -->
                <div class="task-card-logo">
                  <img src="../assets/images/Logo.svg" alt="Company Logo" class="img-fluid"/>
                </div>	
              </div>
            </div>
          </div>
          
          <!-- Task History Card -->
          <div class="col-md-12">
            <div class="">
              <div class="">
                <span>Task History</span>
                <!-- Export History Button -->
                <?php if (!empty($taskHistory)): ?>
    <div class="mb-3">
        
            
            <a href="<?php echo BASE_URL; ?>/export-task-history-excel?id=<?php echo urlencode($task['id']); ?>">Export History excel</a>
            
     
    </div>
<?php endif; ?>
              </div>
              <div class="">
                <?php if (!empty($taskHistory)): ?>
                  <table class="table table-bordered" id="taskHistoryTable">
                    <thead>
                      <tr>
                        <th>Action</th>
                        <th>User</th>
                        <th>Date & Time</th>
                        <th>Changes</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($taskHistory as $history): ?>
                        <tr>
                          <td><?php echo htmlspecialchars(ucfirst($history['action'])); ?></td>
                          <td><?php echo htmlspecialchars($history['userName'] ?? 'System'); ?></td>
                          <td><?php echo htmlspecialchars((new DateTime($history['dateTime']))->format('Y-m-d H:i:s')); ?></td>
                          <td>
                <?php 
    // Check if 'changes' exist and handle based on the action type
    if (!empty($history['action']) && strtolower($history['action']) === 'added') {
        // If the action is 'add', display 'N/A' for changes
        echo "N/A";
    } elseif (!empty($history['changes'])) {
        // If there are changes, attempt to decode and display them
        $changes = json_decode($history['changes'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($changes)) {
            foreach ($changes as $field => $change) {
                // Display each change with proper sanitization
                echo "<strong>" . htmlspecialchars($field) . ":</strong> " . 
                     htmlspecialchars($change['old'] ?? 'N/A') . " &rarr; " . 
                     htmlspecialchars($change['new'] ?? 'N/A') . "<br>";
            }
        } else {
            // If JSON decoding fails, display an error message
            echo "Invalid changes data.";
        }
    } else {
        // If no changes and action is not 'add', display a default message
        echo "No additional details.";
    }
?>
            </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php else: ?>
                  <p>No history records available for this task.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Back to Admin Tasks Button -->
        <a href="<?php echo BASE_URL; ?>/admin-tasks" class="btn btn-secondary mt-4">Back to Admin Tasks</a>
      </div>
    </article>
  </section>
</div>

<!-- Initialize DataTables for Task History -->
<script>
  $(document).ready(function(){
    $('#taskHistoryTable').DataTable({
      paging: true,
      searching: false, // Disable search if not needed
      ordering: true,
      order: [[2, 'desc']], // Order by Date & Time descending
      lengthChange: false, // Disable changing page length
      pageLength: 5, // Set default page length
      language: {
        emptyTable: "No history records available for this task."
      }
    });
  });
</script>

<?php include(ASSET_PATH . '/includes/footer.php'); ?>
