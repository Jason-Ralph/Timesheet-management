<?php
// controllers/ReportsController.php

// Include necessary models and libraries
require_once __DIR__ . '/../models/TaskModel.php';
require_once __DIR__ . '/../models/UserModel.php';
// If you have a Logger, adjust as needed:
require_once __DIR__ . '/../libraries/Logger.php';

/**
 * ReportsController
 * Handles the generation and management of reports.
 */
class ReportsController {
    private $taskModel;
    private $userModel;
    private $logger;

    public function __construct() {
        // Ensure the session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize models
        $this->taskModel = new TaskModel();
        $this->userModel = new UserModel();

        // Initialize the Logger (if used)
        $this->logger = Logger::getInstance();
    }

    /**
     * The main "reports" page action
     */
    public function index() {
        try {
            // 1) Grab the GET parameters for start_date and end_date, or use defaults
            $startDate = isset($_GET['start_date']) && $_GET['start_date']
                ? $_GET['start_date']
                : date('Y-m-d', strtotime('-31 days')); // default last 7 days

            $endDate = isset($_GET['end_date']) && $_GET['end_date']
                ? $_GET['end_date']
                : date('Y-m-d');

            // If you filter by user:
            $selectedUser = isset($_GET['selected_user']) ? $_GET['selected_user'] : null;

            // 2) Fetch tasks from the model, applying date-range (and user) filters
            //    Adjust this to your actual method signature & filtering logic
            $tasks = $this->taskModel->getFilteredActiveTasksWithDept($startDate, $endDate, $selectedUser);

            // 3) Aggregate the data into an array $data
            $data = $this->aggregateData($tasks);

            // 4) (Optional) Log the report generation
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],
                    $_SESSION['user']['userTitle'],
                    $_SESSION['user']['department'],
                    'ReportsController::index',
                    'generate_report',
                    "Generated report with Start: $startDate, End: $endDate, UserID: $selectedUser",
                    'INFO'
                );
            }

            // 5) Include the view, passing $data and the chosen $startDate/$endDate
            //    (They become available as local vars in reports.php)
            include __DIR__ . '/../views/reports.php';

        } catch (Exception $e) {
            // Handle any error
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],
                    $_SESSION['user']['userTitle'],
                    $_SESSION['user']['department'],
                    'ReportsController::index',
                    'error',
                    "Failed to load reports: " . $e->getMessage(),
                    'ERROR'
                );
            }
            $_SESSION['error_message'] = "Failed to load reports: " . $e->getMessage();
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * Aggregate data from tasks for reporting purposes.
     * (Example aggregator from your previous code, truncated or adapted as needed.)
     *
     * @param array $tasks
     * @return array
     */
    private function aggregateData($tasks) {
        // Prepare all counters/arrays for user, dept, client, etc.
        $countsUser      = []; 
        $timeUser        = []; 
        $actualCostUser  = []; 
        $quotedCostUser  = []; 
        $overtimeUser    = []; 
        $lateUser        = []; 
        $completedUser   = []; 
        $activeUser      = []; 

        $countsDept      = []; 
        $timeDept        = []; 
        $actualCostDept  = []; 
        $quotedCostDept  = []; 
        $overtimeDept    = []; 
        $lateDept        = []; 
        $completedDept   = []; 
        $activeDept      = []; 

        $countsClient    = []; 
        $timeClient      = []; 
        $actualCostClient= []; 
        $quotedCostClient= []; 
        $overtimeClient  = []; 
        $lateClient      = []; 
        $completedClient = []; 
        $activeClient    = []; 

        // Global counters
        $totalCompleted = 0;
        $totalActive    = 0;

        // Additional stats
        $tasks_by_month  = [];
        $start_hour_dist = array_fill(0, 24, 0);

        // Extra
        $day_of_week_count   = [0,0,0,0,0,0,0];
        $costVarianceUser    = [];
        $costVarianceDept    = [];
        $costVarianceClient  = [];
        $durationBuckets     = ['0-2'=>0, '2-4'=>0, '4-8'=>0, '8+'=>0];

        // Loop each task
        foreach ($tasks as $task) {
            $userName  = $task['userName']   ?? 'Unknown';
            $dept      = $task['department'] ?? 'Unknown';
            $client    = $task['client']     ?? 'Unknown';
            $quoted    = (float)($task['taskQuotedCost'] ?? 0);
            $actual    = (float)($task['taskActualCost'] ?? 0);
            $isOvertime= !empty($task['taskOvertime']) && $task['taskOvertime'] != '0';
            $isLate    = !empty($task['taskLateWork']) && $task['taskLateWork'] != '0';
            $isDone    = !empty($task['taskEndTime']);

            if ($isDone) $totalCompleted++; else $totalActive++;

            // timeSpent
            $startTime = strtotime($task['taskStartTime'] ?? '');
            $endTime   = $isDone ? strtotime($task['taskEndTime']) : time();
            $timeSpent = 0;
            if ($startTime && $endTime && $endTime > $startTime) {
                $timeSpent = $endTime - $startTime;
            }

            // tasks by month
            if ($startTime) {
                $monthKey = date('Y-m', $startTime);
                if (!isset($tasks_by_month[$monthKey])) {
                    $tasks_by_month[$monthKey] = 0;
                }
                $tasks_by_month[$monthKey]++;
            }

            // distribution of start hour
            if ($startTime) {
                $hr = (int)date('G', $startTime);
                $start_hour_dist[$hr]++;
            }

            // day_of_week_count
            if ($startTime) {
                $dw = (int)date('w', $startTime); // 0=Sunday..6=Saturday
                $day_of_week_count[$dw]++;
            }

            // cost variance
            $costDiff = $actual - $quoted;
            if (!isset($costVarianceUser[$userName])) $costVarianceUser[$userName] = 0;
            $costVarianceUser[$userName] += $costDiff;

            if (!isset($costVarianceDept[$dept])) $costVarianceDept[$dept] = 0;
            $costVarianceDept[$dept] += $costDiff;

            if (!isset($costVarianceClient[$client])) $costVarianceClient[$client] = 0;
            $costVarianceClient[$client] += $costDiff;

            // Duration Buckets
            $hoursSpent = $timeSpent / 3600;
            if ($hoursSpent <= 2)      $durationBuckets['0-2']++;
            else if ($hoursSpent <= 4) $durationBuckets['2-4']++;
            else if ($hoursSpent <= 8) $durationBuckets['4-8']++;
            else                       $durationBuckets['8+']++;

            // Stats by user
            if (!isset($countsUser[$userName])) {
                $countsUser[$userName]     = 0;
                $timeUser[$userName]       = 0;
                $actualCostUser[$userName] = 0;
                $quotedCostUser[$userName] = 0;
                $overtimeUser[$userName]   = 0;
                $lateUser[$userName]       = 0;
                $completedUser[$userName]  = 0;
                $activeUser[$userName]     = 0;
            }
            $countsUser[$userName]++;
            $timeUser[$userName]       += $timeSpent;
            $actualCostUser[$userName] += $actual;
            $quotedCostUser[$userName] += $quoted;
            if ($isOvertime) $overtimeUser[$userName]++;
            if ($isLate)     $lateUser[$userName]++;
            if ($isDone)     $completedUser[$userName]++; else $activeUser[$userName]++;

            // Stats by dept
            if (!isset($countsDept[$dept])) {
                $countsDept[$dept]       = 0;
                $timeDept[$dept]         = 0;
                $actualCostDept[$dept]   = 0;
                $quotedCostDept[$dept]   = 0;
                $overtimeDept[$dept]     = 0;
                $lateDept[$dept]         = 0;
                $completedDept[$dept]    = 0;
                $activeDept[$dept]       = 0;
            }
            $countsDept[$dept]++;
            $timeDept[$dept]       += $timeSpent;
            $actualCostDept[$dept] += $actual;
            $quotedCostDept[$dept] += $quoted;
            if ($isOvertime) $overtimeDept[$dept]++;
            if ($isLate)     $lateDept[$dept]++;
            if ($isDone)     $completedDept[$dept]++; else $activeDept[$dept]++;

            // Stats by client
            if (!isset($countsClient[$client])) {
                $countsClient[$client]     = 0;
                $timeClient[$client]       = 0;
                $actualCostClient[$client] = 0;
                $quotedCostClient[$client] = 0;
                $overtimeClient[$client]   = 0;
                $lateClient[$client]       = 0;
                $completedClient[$client]  = 0;
                $activeClient[$client]     = 0;
            }
            $countsClient[$client]++;
            $timeClient[$client]       += $timeSpent;
            $actualCostClient[$client] += $actual;
            $quotedCostClient[$client] += $quoted;
            if ($isOvertime) $overtimeClient[$client]++;
            if ($isLate)     $lateClient[$client]++;
            if ($isDone)     $completedClient[$client]++; else $activeClient[$client]++;
        }

        // Compute average and ratio stats for user/dept/client
        $avg_user = []; $ratioOvertimeUser = []; $ratioLateUser = [];
        foreach ($countsUser as $u => $cnt) {
            $avg_user[$u] = $cnt ? ($timeUser[$u]/$cnt) : 0; // in seconds
            $ratioOvertimeUser[$u] = $cnt ? ($overtimeUser[$u]/$cnt) : 0;
            $ratioLateUser[$u]     = $cnt ? ($lateUser[$u]/$cnt)     : 0;
        }

        $avg_dept = []; $ratioOvertimeDept = []; $ratioLateDept = [];
        foreach ($countsDept as $d => $cnt) {
            $avg_dept[$d]          = $cnt ? ($timeDept[$d]/$cnt) : 0;
            $ratioOvertimeDept[$d] = $cnt ? ($overtimeDept[$d]/$cnt) : 0;
            $ratioLateDept[$d]     = $cnt ? ($lateDept[$d]/$cnt)     : 0;
        }

        $avg_client = []; $ratioOvertimeClient = []; $ratioLateClient = [];
        foreach ($countsClient as $c => $cnt) {
            $avg_client[$c]          = $cnt ? ($timeClient[$c]/$cnt) : 0;
            $ratioOvertimeClient[$c] = $cnt ? ($overtimeClient[$c]/$cnt) : 0;
            $ratioLateClient[$c]     = $cnt ? ($lateClient[$c]/$cnt)     : 0;
        }

        // Return the final data structure
        return [
            // *** User stats ***
            'count_user'     => $countsUser,
            'time_user'      => $timeUser,
            'actual_user'    => $actualCostUser,
            'quoted_user'    => $quotedCostUser,
            'overtime_user'  => $overtimeUser,
            'late_user'      => $lateUser,
            'completed_user' => $completedUser,
            'active_user'    => $activeUser,
            'avg_user'            => $avg_user,
            'ratio_overtime_user' => $ratioOvertimeUser,
            'ratio_late_user'     => $ratioLateUser,

            // *** Dept stats ***
            'count_dept'     => $countsDept,
            'time_dept'      => $timeDept,
            'actual_dept'    => $actualCostDept,
            'quoted_dept'    => $quotedCostDept,
            'overtime_dept'  => $overtimeDept,
            'late_dept'      => $lateDept,
            'completed_dept' => $completedDept,
            'active_dept'    => $activeDept,
            'avg_dept'            => $avg_dept,
            'ratio_overtime_dept' => $ratioOvertimeDept,
            'ratio_late_dept'     => $ratioLateDept,

            // *** Client stats ***
            'count_client'     => $countsClient,
            'time_client'      => $timeClient,
            'actual_client'    => $actualCostClient,
            'quoted_client'    => $quotedCostClient,
            'overtime_client'  => $overtimeClient,
            'late_client'      => $lateClient,
            'completed_client' => $completedClient,
            'active_client'    => $activeClient,
            'avg_client'             => $avg_client,
            'ratio_overtime_client'  => $ratioOvertimeClient,
            'ratio_late_client'      => $ratioLateClient,

            // *** Global stats ***
            'totalCompleted' => $totalCompleted,
            'totalActive'    => $totalActive,
            'tasks_by_month' => $tasks_by_month,
            'start_hour_dist'=> $start_hour_dist,

            // *** Additional ***
            'day_of_week_count'    => $day_of_week_count,
            'cost_variance_user'   => $costVarianceUser,
            'cost_variance_dept'   => $costVarianceDept,
            'cost_variance_client' => $costVarianceClient,
            'duration_buckets'     => $durationBuckets
        ];
    }
}
