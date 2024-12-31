<?php
// views/reports.php

// We assume the variables $data, $startDate, $endDate, etc. are available here
// because we included this file from ReportsController::index()

include(ASSET_PATH . '/includes/header.php');
?>

<div class="content">
  <div class="menu-trigger"></div>
  <section class="profile">
    <article>
      <div class="container mt-4">
        <h1>Reports &amp; Analytics</h1>

        <!-- ============================= -->
        <!-- NEW DATE RANGE FILTER (HIDDEN FORM) -->
        <!-- ============================= -->
        <?php if ($permissionHelperController->hasPermission('Edit task')): ?>
          <form method="GET" action="<?php echo BASE_URL; ?>/reports" id="filtersForm" class="mb-4">
            <!-- Hidden fields for actual start/end used in the query -->
            <input type="hidden" name="start_date" id="start_date"
                   value="<?php echo htmlspecialchars($startDate); ?>">
            <input type="hidden" name="end_date" id="end_date"
                   value="<?php echo htmlspecialchars($endDate); ?>">

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

        <!-- ============================= -->
        <!-- STATS SELECTOR -->
        <!-- ============================= -->
        <div class="mb-4">
          <label for="statsSelect" class="form-label">Select Stats Category:</label>
          <select id="statsSelect" class="form-select" style="max-width: 300px;">
            <option value="user">User stats</option>
            <option value="client">Client stats</option>
            <option value="department">Department stats</option>
            <option value="task">Task stats</option>
            <option value="cost">Cost stats</option>
          </select>
        </div>

        <?php
          // EXTRACT ARRAYS FROM $data
          // USER
          $countUser       = $data['count_user']       ?? [];
          $timeUser        = $data['time_user']        ?? [];
          $actualUser      = $data['actual_user']      ?? [];
          $quotedUser      = $data['quoted_user']      ?? [];
          $overtimeUser    = $data['overtime_user']    ?? [];
          $lateUser        = $data['late_user']        ?? [];
          $doneUser        = $data['completed_user']   ?? [];
          $activeUser      = $data['active_user']      ?? [];
          $avgUser         = $data['avg_user']         ?? [];
          $ratioOvertimeU  = $data['ratio_overtime_user'] ?? [];
          $ratioLateU      = $data['ratio_late_user']  ?? [];

          // DEPT
          $countDept       = $data['count_dept']       ?? [];
          $timeDept        = $data['time_dept']        ?? [];
          $actualDept      = $data['actual_dept']      ?? [];
          $quotedDept      = $data['quoted_dept']      ?? [];
          $overtimeDept    = $data['overtime_dept']    ?? [];
          $lateDept        = $data['late_dept']        ?? [];
          $doneDept        = $data['completed_dept']   ?? [];
          $activeDept      = $data['active_dept']      ?? [];
          $avgDept         = $data['avg_dept']         ?? [];
          $ratioOvertimeD  = $data['ratio_overtime_dept'] ?? [];
          $ratioLateD      = $data['ratio_late_dept']  ?? [];

          // CLIENT
          $countClient     = $data['count_client']     ?? [];
          $timeClient      = $data['time_client']      ?? [];
          $actualClient    = $data['actual_client']    ?? [];
          $quotedClient    = $data['quoted_client']    ?? [];
          $overtimeClient  = $data['overtime_client']  ?? [];
          $lateClient      = $data['late_client']      ?? [];
          $doneClient      = $data['completed_client'] ?? [];
          $activeClient    = $data['active_client']    ?? [];
          $avgClient       = $data['avg_client']       ?? [];
          $ratioOvertimeC  = $data['ratio_overtime_client'] ?? [];
          $ratioLateC      = $data['ratio_late_client']  ?? [];

          // TASK (GLOBAL)
          $totalCompleted  = $data['totalCompleted']  ?? 0;
          $totalActive     = $data['totalActive']     ?? 0;
          $tasksByMonth    = $data['tasks_by_month']  ?? [];
          $startHourDist   = $data['start_hour_dist'] ?? []; // 0..23
		  
		  // NEW FIELDS for Extra Stats
          $dayOfWeekCount  = $data['day_of_week_count']    ?? [0,0,0,0,0,0,0];
          $costVarUser     = $data['cost_variance_user']   ?? [];
          $costVarDept     = $data['cost_variance_dept']   ?? [];
          $costVarClient   = $data['cost_variance_client'] ?? [];
          $durationBuckets = $data['duration_buckets']     ?? ['0-2'=>0,'2-4'=>0,'4-8'=>0,'8+'=>0];
		  
        ?>

        <!-- ============================= -->
        <!-- 1) USER STATS (DIV) -->
        <!-- ============================= -->
        <div id="user-stats" style="display: none;">
          <h2>User Stats</h2>

          <div class="row">
			  
			  
		<div class="col col-6"><h4>1. Tasks total count per user</h4>
          <canvas id="userCountChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">2. Tasks total time per user (Hours)</h4>
          <canvas id="userTimeChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">3. Tasks total cost per user (Actual)</h4>
          <canvas id="userCostChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">4. Average Task Duration (Hours) per user</h4>
          <canvas id="avgUserChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">5. Late Work Ratio (Late/Total) per user</h4>
          <canvas id="userLateRatioChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">6. Overtime Ratio (Overtime/Total) per user</h4>
          <canvas id="userOvertimeRatioChart"></canvas></div>
			
			
		</div>
        </div>

        <!-- ============================= -->
        <!-- 2) CLIENT STATS (DIV) -->
        <!-- ============================= -->
        <div id="client-stats" style="display: none;">
          <h2>Client Stats</h2>
<div class="row">
          <div class="col col-6"><h4>1. Tasks total count per client</h4>
          <canvas id="clientCountChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">2. Tasks total time per client (Hours)</h4>
          <canvas id="clientTimeChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">3. Tasks total cost per client (Actual)</h4>
          <canvas id="clientCostChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">4. Average Task Duration (Hours) per client</h4>
          <canvas id="avgClientChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">5. Late Work Ratio (Late/Total) per client</h4>
          <canvas id="clientLateRatioChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">6. Overtime Ratio (Overtime/Total) per client</h4>
          <canvas id="clientOvertimeRatioChart"></canvas></div>
			
			</div>
			
        </div>

        <!-- ============================= -->
        <!-- 3) DEPARTMENT STATS (DIV) -->
        <!-- ============================= -->
        <div id="department-stats" style="display: none;">
          <h2>Department Stats</h2>
<div class="row">
         <div class="col col-6"> <h4>1. Tasks total count per department</h4>
          <canvas id="deptCountChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">2. Tasks total time per department (Hours)</h4>
          <canvas id="deptTimeChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">3. Tasks total cost per department (Actual)</h4>
          <canvas id="deptCostChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">4. Average Task Duration (Hours) per department</h4>
          <canvas id="avgDeptChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">5. Late Work Ratio (Late/Total) per department</h4>
          <canvas id="deptLateRatioChart"></canvas></div>

          <div class="col col-6"><h4 class="mt-4">6. Overtime Ratio (Overtime/Total) per department</h4>
          <canvas id="deptOvertimeRatioChart"></canvas></div>
			
			
			
			</div>
        </div>

        <!-- ============================= -->
        <!-- 4) TASK STATS (DIV) -->
        <!-- ============================= -->
        <div id="task-stats" style="display: none;">
          <h2>Task Stats</h2>
          <p>Total Completed Tasks: <?php echo $totalCompleted; ?></p>
          <p>Total Active Tasks: <?php echo $totalActive; ?></p>



			<div class="row">
          <div class="col col-6">
			  
		<h4 class="mt-4">1. Tasks by Month (start date)</h4>
		<canvas id="tasksByMonthChart"></canvas>
		</div>
          <div class="col col-6">
		<h4 class="mb-4">2. Distribution of Start Times (Hour of Day)</h4>
		<canvas id="startHourDistChart"></canvas>
		</div>
				
		
			<div class="col col-6">
              <h4 class="mt-4">3. Day-of-Week Task Count</h4>
              <canvas id="dayOfWeekChart"></canvas>
            </div>
            <div class="col col-6">
              <h4 class="mt-4">4. Task Duration Buckets</h4>
              <canvas id="durationBucketChart"></canvas>
            </div>
					
				
				
			</div>
			
			
			
			
			
          <h4 class="mt-4">5. Completed vs. Active tasks per User</h4>
			<div class="row">
          <div class="col col-6"><canvas id="doneUserChart"></canvas></div>
          <div class="col col-6"><canvas id="activeUserChart"></canvas></div>
			</div>

          <hr>
          <h4 class="mt-4">6. Completed vs. Active tasks per Client</h4>
			<div class="row">
          <div class="col col-6"><canvas id="doneClientChart"></canvas></div>
          <div class="col col-6"><canvas id="activeClientChart"></canvas></div>
			</div>

          <hr>
          <h4 class="mt-4">7. Completed vs. Active tasks per Department</h4>
		<div class="row">
          <div class="col col-6"><canvas id="doneDeptChart"></canvas></div>
          <div class="col col-6"><canvas id="activeDeptChart"></canvas></div>
			</div>	
			
			
			
			
        </div>

        <!-- ============================= -->
        <!-- 5) COST STATS (DIV) -->
        <!-- ============================= -->
        <div id="cost-stats" style="display: none;">
          <h2>Cost Stats</h2>
          <p>Actual vs Quoted cost, plus Overtime counts, for user/department/client.</p>

          <h4>1. User Actual vs. Quoted Cost & Overtime Count</h4>
			
			
			<div class="row">
          <div class="col col-4"><canvas id="costUserActualChart"></canvas></div>
          <div class="col col-4"><canvas id="costUserQuotedChart"></canvas></div>
          <div class="col col-4"><canvas id="overtimeUserChart"></canvas></div>
			</div>

			
			
			
			
          <hr>
          <h4 class="mt-4">2. Department Actual vs. Quoted Cost & Overtime Count</h4>
			
		<div class="row">
          <div class="col col-4"><canvas id="costDeptActualChart"></canvas></div>
          <div class="col col-4"><canvas id="costDeptQuotedChart"></canvas></div>
          <div class="col col-4"><canvas id="overtimeDeptChart"></canvas></div>
			</div>	
			

          <hr>
          <h4 class="mt-4">3. Client Actual vs. Quoted Cost & Overtime Count</h4>
				<div class="row">
          <div class="col col-4"><canvas id="costClientActualChart"></canvas></div>
          <div class="col col-4"><canvas id="costClientQuotedChart"></canvas></div>
          <div class="col col-4"><canvas id="overtimeClientChart"></canvas></div>
			</div>

          <hr>
          
		<div class="row">
          <div class="col col-4"><h4 class="mt-4">4. Cost Variance (Actual - Quoted) by User</h4><canvas id="costVarianceUserChart"></canvas></div>
          <div class="col col-4"><h4 class="mt-4">5. Cost Variance (Actual - Quoted) by Department</h4><canvas id="costVarianceDeptChart"></canvas></div>
          <div class="col col-4"><h4 class="mt-4">6. Cost Variance (Actual - Quoted) by Client</h4><canvas id="costVarianceClientChart"></canvas></div>
			</div>

          <hr>
          
			
			
        </div>
  
      </div> <!-- /.container -->
    </article>
  </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // ========== 1) Initialize the Stats Selector Visibility ==========
  function updateStatsVisibility() {
    const selected = document.getElementById('statsSelect').value;
    const sections = ['user-stats', 'client-stats', 'department-stats', 'task-stats', 'cost-stats'];
    sections.forEach(id => (document.getElementById(id).style.display = 'none'));
    document.getElementById(`${selected}-stats`).style.display = 'block';
  }

  document.getElementById('statsSelect').addEventListener('change', updateStatsVisibility);
  // Default to user stats:
  document.getElementById('statsSelect').value = 'user';
  updateStatsVisibility();

  // ========== 2) Initialize the Date Range Picker (if user can edit tasks) ==========
  <?php if ($permissionHelperController->hasPermission('Access reports page')): ?>
  $('#dateRangeFilter').daterangepicker({
    locale: { format: 'YYYY-MM-DD' },
    startDate: '<?php echo htmlspecialchars($startDate); ?>',
    endDate:   '<?php echo htmlspecialchars($endDate); ?>',
    ranges: {
      'Today':        [moment(), moment()],
      'Yesterday':    [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Last 7 Days':  [moment().subtract(6, 'days'), moment()],
      'Last 30 Days': [moment().subtract(29, 'days'), moment()],
      'This Month':   [moment().startOf('month'), moment().endOf('month')],
      'Last Month':   [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    }
  }, function(start, end) {
    // When user applies a new range
    document.getElementById('start_date').value = start.format('YYYY-MM-DD');
    document.getElementById('end_date').value   = end.format('YYYY-MM-DD');
    // Submit the form => reload the page with new GET params
    document.getElementById('filtersForm').submit();
  });
  <?php endif; ?>

  // ========== 3) Helper Functions ==========

  // Convert "seconds" object values -> "hours" object values
  function secondsToHours(obj) {
    const result = {};
    for (let key in obj) {
      result[key] = obj[key] / 3600;
    }
    return result;
  }

  // Convert "ratio" object values (0..1) -> percentage (0..100)
  function ratioToPercent(obj) {
    const result = {};
    for (let key in obj) {
      result[key] = obj[key] * 100;
    }
    return result;
  }

  // ========== 4) Chart.js Inits ==========

  // ----- USER STATS -----
  (function() {
    // 1) userCountChart
    const userCountData = <?php echo json_encode($countUser); ?>;
    new Chart(document.getElementById('userCountChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(userCountData),
        datasets: [{
          label: 'Task Count (User)',
          data: Object.values(userCountData),
          backgroundColor: 'rgba(54, 162, 235, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 2) userTimeChart (Seconds -> Hours)
    const timeObj = secondsToHours(<?php echo json_encode($timeUser); ?>);
    new Chart(document.getElementById('userTimeChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(timeObj),
        datasets: [{
          label: 'Hours Spent (User)',
          data: Object.values(timeObj),
          backgroundColor: 'rgba(255, 159, 64, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 3) userCostChart (Actual)
    const userActual = <?php echo json_encode($actualUser); ?>;
    new Chart(document.getElementById('userCostChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(userActual),
        datasets: [{
          label: 'Actual Cost (User)',
          data: Object.values(userActual),
          backgroundColor: 'rgba(255, 99, 132, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 4) avgUserChart (Seconds -> Hours)
    const avgSec = <?php echo json_encode($avgUser); ?>;
    const avgHrs = {};
    for (let u in avgSec) {
      avgHrs[u] = avgSec[u] / 3600;
    }
    new Chart(document.getElementById('avgUserChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(avgHrs),
        datasets: [{
          label: 'Average Task Duration (Hours)',
          data: Object.values(avgHrs),
          backgroundColor: 'rgba(153, 102, 255, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 5) userLateRatioChart (0..1 => chart in same 0..1 range)
    const lateRatio = <?php echo json_encode($ratioLateU); ?>;
    new Chart(document.getElementById('userLateRatioChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(lateRatio),
        datasets: [{
          label: 'Late Work Ratio (User)',
          data: Object.values(lateRatio),
          backgroundColor: 'rgba(255, 205, 86, 0.3)'
        }]
      },
      options: {
        scales: {
          y: { min: 0, max: 1 }
        }
      }
    });
  })();

  (function() {
    // 6) userOvertimeRatioChart
    const otRatio = <?php echo json_encode($ratioOvertimeU); ?>;
    new Chart(document.getElementById('userOvertimeRatioChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(otRatio),
        datasets: [{
          label: 'Overtime Ratio (User)',
          data: Object.values(otRatio),
          backgroundColor: 'rgba(75, 192, 192, 0.3)'
        }]
      },
      options: {
        scales: {
          y: { min: 0, max: 1 }
        }
      }
    });
  })();

  // ----- CLIENT STATS -----
  (function() {
    // 1) clientCountChart
    const dataObj = <?php echo json_encode($countClient); ?>;
    new Chart(document.getElementById('clientCountChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Task Count (Client)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(54, 162, 235, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 2) clientTimeChart (Seconds -> Hours)
    const timeObj = secondsToHours(<?php echo json_encode($timeClient); ?>);
    new Chart(document.getElementById('clientTimeChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(timeObj),
        datasets: [{
          label: 'Hours Spent (Client)',
          data: Object.values(timeObj),
          backgroundColor: 'rgba(255, 159, 64, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 3) clientCostChart (Actual)
    const dataObj = <?php echo json_encode($actualClient); ?>;
    new Chart(document.getElementById('clientCostChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Actual Cost (Client)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 99, 132, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 4) avgClientChart (Seconds -> Hours)
    const avgSec = <?php echo json_encode($avgClient); ?>;
    const avgHrs = {};
    for (let c in avgSec) {
      avgHrs[c] = avgSec[c] / 3600;
    }
    new Chart(document.getElementById('avgClientChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(avgHrs),
        datasets: [{
          label: 'Average Duration (Hours)',
          data: Object.values(avgHrs),
          backgroundColor: 'rgba(153, 102, 255, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 5) clientLateRatioChart (0..1 => chart 0..1)
    const dataObj = <?php echo json_encode($ratioLateC); ?>;
    new Chart(document.getElementById('clientLateRatioChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Late Work Ratio (Client)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 205, 86, 0.3)'
        }]
      },
      options: {
        scales: {
          y: { min: 0, max: 1 }
        }
      }
    });
  })();

  (function() {
    // 6) clientOvertimeRatioChart (0..1 => chart 0..1)
    const dataObj = <?php echo json_encode($ratioOvertimeC); ?>;
    new Chart(document.getElementById('clientOvertimeRatioChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Overtime Ratio (Client)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(75, 192, 192, 0.3)'
        }]
      },
      options: {
        scales: {
          y: { min: 0, max: 1 }
        }
      }
    });
  })();

  // ----- DEPARTMENT STATS -----
  // (Follow the same pattern for deptCountChart, deptTimeChart, deptCostChart, etc.)

  (function() {
    // 1) deptCountChart
    const dataObj = <?php echo json_encode($countDept); ?>;
    new Chart(document.getElementById('deptCountChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Task Count (Dept)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(201, 203, 207, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 2) deptTimeChart (Seconds -> Hours)
    const timeObj = secondsToHours(<?php echo json_encode($timeDept); ?>);
    new Chart(document.getElementById('deptTimeChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(timeObj),
        datasets: [{
          label: 'Hours Spent (Dept)',
          data: Object.values(timeObj),
          backgroundColor: 'rgba(255, 159, 64, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 3) deptCostChart
    const dataObj = <?php echo json_encode($actualDept); ?>;
    new Chart(document.getElementById('deptCostChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Actual Cost (Dept)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 99, 132, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 4) avgDeptChart (Seconds -> Hours)
    const avgSec = <?php echo json_encode($avgDept); ?>;
    const avgHrs = {};
    for (let d in avgSec) {
      avgHrs[d] = avgSec[d] / 3600;
    }
    new Chart(document.getElementById('avgDeptChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(avgHrs),
        datasets: [{
          label: 'Average Duration (Hours)',
          data: Object.values(avgHrs),
          backgroundColor: 'rgba(153, 102, 255, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // 5) deptLateRatioChart (0..1)
    const dataObj = <?php echo json_encode($ratioLateD); ?>;
    new Chart(document.getElementById('deptLateRatioChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Late Work Ratio (Dept)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 205, 86, 0.3)'
        }]
      },
      options: {
        scales: {
          y: { min: 0, max: 1 }
        }
      }
    });
  })();

  (function() {
    // 6) deptOvertimeRatioChart (0..1)
    const dataObj = <?php echo json_encode($ratioOvertimeD); ?>;
    new Chart(document.getElementById('deptOvertimeRatioChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Overtime Ratio (Dept)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(75, 192, 192, 0.3)'
        }]
      },
      options: {
        scales: {
          y: { min: 0, max: 1 }
        }
      }
    });
  })();

  // ----- TASK STATS -----
  (function() {
    // tasksByMonthChart (line)
    const tasksByMonth = <?php echo json_encode($tasksByMonth); ?>;
    const sortedMonths = Object.keys(tasksByMonth).sort(); // e.g. ["2023-04","2023-05","2023-06"]
    const counts       = sortedMonths.map(m => tasksByMonth[m]);
    new Chart(document.getElementById('tasksByMonthChart').getContext('2d'), {
      type: 'line',
      data: {
        labels: sortedMonths,
        datasets: [{
          label: 'Tasks by Month',
          data: counts,
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)'
        }]
      }
    });
  })();

  (function() {
    // startHourDistChart (line)
    const distArr = <?php echo json_encode($startHourDist); ?>; // array of length 24
    const hours   = [...Array(24).keys()]; // [0,1,2,...,23]
    new Chart(document.getElementById('startHourDistChart').getContext('2d'), {
      type: 'line',
      data: {
        labels: hours.map(h => String(h)),
        datasets: [{
          label: 'Tasks Started This Hour',
          data: distArr,
          borderColor: 'rgba(255, 159, 64, 1)',
          backgroundColor: 'rgba(255, 159, 64, 0.2)'
        }]
      }
    });
  })();

  (function() {
    // Day-of-Week Task Count
    const dayOfWeekCount = <?php echo json_encode($dayOfWeekCount); ?>; 
    const dayLabels = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    new Chart(document.getElementById('dayOfWeekChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: dayLabels,
        datasets: [{
          label: 'Tasks Started',
          data: dayOfWeekCount,
          backgroundColor: 'rgba(54, 162, 235, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // Task Duration Buckets
    const durationObj = <?php echo json_encode($durationBuckets); ?>;
    new Chart(document.getElementById('durationBucketChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(durationObj),
        datasets: [{
          label: 'Number of Tasks',
          data: Object.values(durationObj),
          backgroundColor: 'rgba(255, 99, 132, 0.3)'
        }]
      }
    });
  })();

  // Completed vs Active tasks (User)
  (function() {
    const dataObj = <?php echo json_encode($doneUser); ?>;
    new Chart(document.getElementById('doneUserChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Completed Tasks (User)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(0, 102, 204, 0.3)'
        }]
      }
    });
  })();
  (function() {
    const dataObj = <?php echo json_encode($activeUser); ?>;
    new Chart(document.getElementById('activeUserChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Active Tasks (User)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 159, 64, 0.3)'
        }]
      }
    });
  })();

  // Completed vs Active (Client)
  (function() {
    const dataObj = <?php echo json_encode($doneClient); ?>;
    new Chart(document.getElementById('doneClientChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Completed Tasks (Client)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 51, 0, 0.3)'
        }]
      }
    });
  })();
  (function() {
    const dataObj = <?php echo json_encode($activeClient); ?>;
    new Chart(document.getElementById('activeClientChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Active Tasks (Client)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(75, 192, 192, 0.3)'
        }]
      }
    });
  })();

  // Completed vs Active (Dept)
  (function() {
    const dataObj = <?php echo json_encode($doneDept); ?>;
    new Chart(document.getElementById('doneDeptChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Completed Tasks (Dept)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 205, 86, 0.3)'
        }]
      }
    });
  })();
  (function() {
    const dataObj = <?php echo json_encode($activeDept); ?>;
    new Chart(document.getElementById('activeDeptChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Active Tasks (Dept)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(201, 203, 207, 0.3)'
        }]
      }
    });
  })();

  // ----- COST STATS -----
  (function() {
    // 1) costUserActualChart
    const dataObj = <?php echo json_encode($actualUser); ?>;
    new Chart(document.getElementById('costUserActualChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'User Actual Cost (R)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 99, 132, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // costUserQuotedChart
    const dataObj = <?php echo json_encode($quotedUser); ?>;
    new Chart(document.getElementById('costUserQuotedChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'User Quoted Cost (R)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(54, 162, 235, 0.3)'
        }]
      }
    });
  })();

  (function() {
    // overtimeUserChart
    const dataObj = <?php echo json_encode($overtimeUser); ?>;
    new Chart(document.getElementById('overtimeUserChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Overtime Tasks (User)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(75, 192, 192, 0.3)'
        }]
      }
    });
  })();

  // (2) Dept actual vs quoted cost + overtime
  (function() {
    // costDeptActualChart
    const dataObj = <?php echo json_encode($actualDept); ?>;
    new Chart(document.getElementById('costDeptActualChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Dept Actual Cost (R)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(153, 102, 255, 0.3)'
        }]
      }
    });
  })();
  (function() {
    // costDeptQuotedChart
    const dataObj = <?php echo json_encode($quotedDept); ?>;
    new Chart(document.getElementById('costDeptQuotedChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Dept Quoted Cost (R)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 159, 64, 0.3)'
        }]
      }
    });
  })();
  (function() {
    // overtimeDeptChart
    const dataObj = <?php echo json_encode($overtimeDept); ?>;
    new Chart(document.getElementById('overtimeDeptChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Overtime Tasks (Dept)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(75, 192, 192, 0.3)'
        }]
      }
    });
  })();

  // (3) Client actual vs quoted cost + overtime
  (function() {
    // costClientActualChart
    const dataObj = <?php echo json_encode($actualClient); ?>;
    new Chart(document.getElementById('costClientActualChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Client Actual Cost (R)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(201, 203, 207, 0.3)'
        }]
      }
    });
  })();
  (function() {
    // costClientQuotedChart
    const dataObj = <?php echo json_encode($quotedClient); ?>;
    new Chart(document.getElementById('costClientQuotedChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Client Quoted Cost (R)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(99, 255, 132, 0.3)'
        }]
      }
    });
  })();
  (function() {
    // overtimeClientChart
    const dataObj = <?php echo json_encode($overtimeClient); ?>;
    new Chart(document.getElementById('overtimeClientChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Overtime Tasks (Client)',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 205, 86, 0.3)'
        }]
      }
    });
  })();

  // 4) Cost Variance (Actual - Quoted) by User/Dept/Client
  (function() {
    // costVarianceUserChart
    const dataObj = <?php echo json_encode($costVarUser); ?>;
    new Chart(document.getElementById('costVarianceUserChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Cost Variance (R) [User]',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(255, 159, 64, 0.3)'
        }]
      },
      options: {
        scales: { y: { beginAtZero: false } }
      }
    });
  })();

  (function() {
    // costVarianceDeptChart
    const dataObj = <?php echo json_encode($costVarDept); ?>;
    new Chart(document.getElementById('costVarianceDeptChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Cost Variance (R) [Dept]',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(153, 102, 255, 0.3)'
        }]
      },
      options: {
        scales: { y: { beginAtZero: false } }
      }
    });
  })();

  (function() {
    // costVarianceClientChart
    const dataObj = <?php echo json_encode($costVarClient); ?>;
    new Chart(document.getElementById('costVarianceClientChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: Object.keys(dataObj),
        datasets: [{
          label: 'Cost Variance (R) [Client]',
          data: Object.values(dataObj),
          backgroundColor: 'rgba(75, 192, 192, 0.3)'
        }]
      },
      options: {
        scales: { y: { beginAtZero: false } }
      }
    });
  })();

});
</script>

<?php include(ASSET_PATH . '/includes/footer.php'); ?>
