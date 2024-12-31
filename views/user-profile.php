<?php
// views/user-profile.php

$csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<?php include(ASSET_PATH . '/includes/header.php'); ?>
<div class="content">
  <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_edpg3c3s.json" background="transparent" speed="0.3" style="width: 200px; height: 200px; position:absolute; bottom:30px; right:30px; z-index:0; opacity:0.3;" autoplay></lottie-player>
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class="mt-5">
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?> </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?> </div>
        <?php endif; ?>
        <div class="row gap-30">
          <div class="profile-card-left">
            <div class="profile-banner"></div>
            <div class="profile-content-row">
              <div class="profile-image">
                <?php
                $userImg = isset($user['userImg']) && $user['userImg'] ? $user['userImg'] : 'Placeholder.jpg';
                ?>
                <img src="../assets/images/<?php echo htmlspecialchars($userImg, ENT_QUOTES, 'UTF-8'); ?>" alt="User Image"/> </div>
              <div class="profile-content">
                <div class="basic">
                  <p class="name"><?php echo htmlspecialchars($user['name']); ?></p>
                  <p class="title"><strong>Title:</strong> <?php echo htmlspecialchars($user['userTitle']); ?></p>
                  <p class="department"><strong>Department:</strong> <?php echo htmlspecialchars($user['department']); ?></p>
                  <p class="manager"><strong>Manager:</strong> <?php echo htmlspecialchars($user['manager']); ?></p>
                  <p class="roleId"><strong>User type:</strong> <?php echo htmlspecialchars($user['role_name']); ?></p>
                </div>
                <div class="socials">
                  <ul class="social-list">
                    <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" title="<?php echo htmlspecialchars($user['email']); ?>">
                    <li class="email"> </li>
                    </a> <a href="tel:<?php echo htmlspecialchars($user['phone']); ?>" title="<?php echo htmlspecialchars($user['phone']); ?>">
                    <li class="phone"> </li>
                    </a> <a href="<?php echo htmlspecialchars($user['linkedin']); ?>" title="<?php echo htmlspecialchars($user['linkedin']); ?>">
                    <li class="linkedin"> </li>
                    </a> <a href="<?php echo htmlspecialchars($user['facebook']); ?>" title="<?php echo htmlspecialchars($user['facebook']); ?>">
                    <li class="facebook"> </li>
                    </a> <a href="https://api.whatsapp.com/send?phone=<?php echo htmlspecialchars($user['phone']); ?>">
                    <li class="whatsapp"></li>
                    </a>
                  </ul>
                </div>
              </div>
              <div class="card-logo"> <img src="../assets/images/Logo.svg" alt=""/> </div>
            </div>
          </div>
          <div class="profile-card-right">
            <h4>Additional details</h4>
            <p class="languages">Languages<br>
              <span><?php echo htmlspecialchars($user['languages']); ?></span> </p>
            <p class="join">Join date<br>
              <span><?php echo htmlspecialchars($user['joinDate']); ?></span> </p>
            <p class="birthday">Birthday<br>
              <span><?php echo htmlspecialchars($user['birthday']); ?></span> </p>
            <p class="experience">Experience<br>
              <span><?php echo htmlspecialchars($user['experience']); ?></span> </p>
            <p class="address">Address<br>
              <span><?php echo htmlspecialchars($user['address']); ?></span> </p>
          </div>
        </div>
        <br>
        <br>
        <a href="<?php echo BASE_URL; ?>/users" class="btn btn-primary margin-30T">Back to users</a>
      </div>
      
     <!-- ============================= -->
  <!-- NEW SECTION: User Activity Statistics -->
  <!-- ============================= -->
  <div class="container mt-5">
    <h2><?php echo htmlspecialchars($user['name']); ?>'s Activity Statistics</h2>
    
    <!-- Action Types Chart -->
    <div class="row mt-4">
      <div class="col-md-6">
        <h4>Actions Performed</h4>
	
        <canvas id="userActionsChart"></canvas>
      </div>
      
      <div class="col-md-6">
        <h4>Tasks Completed vs. Active</h4>
        <canvas id="userTasksStatusChart"></canvas>
      </div>
    </div>
    
    <!-- Task Duration Chart -->
    <div class="row mt-4">
      <div class="col-md-3">
        <h5>Average Task Duration (Hours)</h5>
        <canvas id="userAvgTaskDurationChart"></canvas>
      </div>
      
      <div class="col-md-3">
        <h5>Late Work Ratio (%)</h5>
        <canvas id="userLateRatioChart"></canvas>
      </div>
    
      <div class="col-md-3">
        <h5>Cost Variance (Actual - Quoted)</h5>
        <canvas id="userCostVarianceChart"></canvas>
      </div>
      
      <div class="col-md-3">
        <h5>Overtime Ratio (%)</h5>
        <canvas id="userOvertimeRatioChart"></canvas>
      </div>
    </div>
    
  </div>
  
 
    </article>
   </section>
</div>

<!-- ============================= -->
<!-- NEW SCRIPT: Initialize Charts -->
<!-- ============================= -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==== 1) User Actions Performed Chart ====
    (function() {
        const actionsData = <?php echo json_encode($logActionsCount); ?>;
        const actionsLabels = Object.keys(actionsData);
        const actionsCounts = Object.values(actionsData);
        console.log("Actions Data:", actionsData); // Debugging
        new Chart(document.getElementById('userActionsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: actionsLabels,
                datasets: [{
                    label: 'Actions Performed',
                    data: actionsCounts,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: false,
                        text: 'Actions Performed'
                    }
                }
            }
        });
    })();

    // ==== 2) User Tasks Completed vs. Active Chart ====
    (function() {
        const completed = <?php echo json_encode($data['completed_user'] ?? []); ?>;
        const active = <?php echo json_encode($data['active_user'] ?? []); ?>;
        const labels = Object.keys(completed);
        const completedCounts = Object.values(completed);
        const activeCounts = Object.values(active);
        console.log("Completed Tasks:", completed);
        console.log("Active Tasks:", active);

        new Chart(document.getElementById('userTasksStatusChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Completed Tasks',
                        data: completedCounts,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)'
                    },
                    {
                        label: 'Active Tasks',
                        data: activeCounts,
                        backgroundColor: 'rgba(255, 159, 64, 0.6)'
                    }
                ]
            },
            options: {
                responsive: true,
				plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: false,
                        text: 'Tasks Completed vs. Active'
                    }
                },
                scales: {
                    x: { stacked: false },
                    y: { beginAtZero: true }
                }
            }
        });
    })();

    // ==== 3) User Average Task Duration Chart ====
    (function() {
        const avgDuration = <?php echo json_encode($data['avg_user'] ?? []); ?>;
        const labels = Object.keys(avgDuration);
        const durations = Object.values(avgDuration).map(sec => (sec / 3600).toFixed(2)); // Convert seconds to hours
        console.log("Average Duration:", durations);

        new Chart(document.getElementById('userAvgTaskDurationChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Average Duration (Hours)',
                    data: durations,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)'
                }]
            },
            options: {
                responsive: true,
            }
        });
    })();

    // ==== 4) User Late Work Ratio Chart ====
    (function() {
        const lateRatio = <?php echo json_encode($data['ratio_late_user'] ?? []); ?>;
        const labels = Object.keys(lateRatio);
        const ratios = Object.values(lateRatio).map(ratio => (ratio * 100).toFixed(2)); // Convert to percentage
        console.log("Late Ratio:", ratios);

        new Chart(document.getElementById('userLateRatioChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Late Work Ratio (%)',
                    data: ratios,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ]
                }]
            },
            options: {
                responsive: true,  
            }
        });
    })();

    // ==== 5) User Cost Variance Chart ====
    (function() {
        const costVariance = <?php echo json_encode($data['cost_variance_user'] ?? []); ?>;
        const labels = Object.keys(costVariance);
        const variances = Object.values(costVariance).map(val => parseFloat(val).toFixed(2));
        console.log("Cost Variance:", variances);

        new Chart(document.getElementById('userCostVarianceChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cost Variance (R)',
                    data: variances,
                    backgroundColor: 'rgba(255, 206, 86, 0.6)'
                }]
            },
            options: {
                responsive: true,
                
            }
        });
    })();

    // ==== 6) User Overtime Ratio Chart ====
    (function() {
        const overtimeRatio = <?php echo json_encode($data['ratio_overtime_user'] ?? []); ?>;
        const labels = Object.keys(overtimeRatio);
        const ratios = Object.values(overtimeRatio).map(ratio => (ratio * 100).toFixed(2)); // Convert to percentage
        console.log("Overtime Ratio:", ratios);

        new Chart(document.getElementById('userOvertimeRatioChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Overtime Ratio (%)',
                    data: ratios,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 206, 86, 0.6)'
                    ]
                }]
            },
            options: {
                responsive: true,
            }
        });
    })();
});
</script>
<!-- ============================= -->
<!-- END OF NEW SCRIPT -->
<!-- ============================= -->

<?php include(ASSET_PATH . '/includes/footer.php'); ?>
