<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TOTG Admin Center</title>
<!-- Bootstrap CSS -->
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">	
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script><br>
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>	
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="../assets/css/icofont.css">
<link rel="stylesheet" href="../assets/css/main.css">	


</head>
<body style="padding-top: 58px;">
<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
session_start();
}
$permissionHelperController = new PermissionsHelperController();


// Retrieve the user's role from the session
$user_role = isset($_SESSION['user']['role_name']) ? $_SESSION['user']['role_name'] : null;

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user']);
?>	
<nav id="slide-menu">
<img class="menu-logo" src="../assets/images/Logo.svg" alt="" />

<ul>
<?php if ($permissionHelperController->hasPermission('Access dashboard page')): ?>	
<li class="dashboard sep"><a href="dashboard">My dashboard</a></li>
<?php endif; ?>	
	
<?php if ($permissionHelperController->hasPermission('Access tasks page')): ?>	
<li class="project-time"><a href="tasks">Project timesheets</a></li>
<?php endif; ?>
	
<?php if ($permissionHelperController->hasPermission('Access tasks page')): ?>	
<li class="project-time"><a href="view-leaves">My Leave</a></li>
<?php endif; ?>	
	
<?php if ($permissionHelperController->hasPermission('Access tasks page')): ?>	
<li class="project-time"><a href="leaves">Apply Leave</a></li>
<?php endif; ?>

<li class="sublist sep">Admin management
<ul>
<?php if ($permissionHelperController->hasPermission('Access clients page')): ?>	
<li class="client sep"><a href="clients">Clients</a></li>
<?php endif; ?>
<?php if ($permissionHelperController->hasPermission('Access users page')): ?>	
<li class="users"><a href="users">Users</a></li>
<?php endif; ?>	
<?php if ($permissionHelperController->hasPermission('Access task description page')): ?>	
<li class="users"><a href="task-descriptions">Task descriptions</a></li>
<?php endif; ?>	
<?php if ($permissionHelperController->hasPermission('Access report types page')): ?>	
<li class="users"><a href="report-types">Report types</a></li>
<?php endif; ?>	
<?php if ($permissionHelperController->hasPermission('Access permissions page')): ?>	
<li class="users"><a href="roles-permissions">Roles</a></li>
<?php endif; ?>	
<?php if ($permissionHelperController->hasPermission('Access reports page')): ?>	
<li class="users"><a href="reports">Reporting</a></li>
<?php endif; ?>	
<?php if ($permissionHelperController->hasPermission('Access admin tasks page')): ?>	
<li class="users"><a href="admin-tasks">Project timesheet admin</a></li>
<?php endif; ?>	
<?php if ($permissionHelperController->hasPermission('Access admin tasks page')): ?>	
<li class="users"><a href="manage-leaves">Manage Leave</a></li>
<?php endif; ?>	
<?php if ($permissionHelperController->hasPermission('Access logs page')): ?>	
<li class="log"><a href="view-logs">Log</a></li>
<?php endif; ?>	
	
</ul>
</li>


<?php if ($is_logged_in): ?>
<li class="logout sep">
    <a href="#" onclick="document.getElementById('logout-form').submit(); return false;">Logout</a>
    <form id="logout-form" action="<?php echo BASE_URL; ?>/logout" method="POST" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    </form>
</li>
<?php else: ?>
<li class="login"><a href="<?php echo BASE_URL; ?>/login">Login</a></li>
<?php endif; ?>
</ul>
</nav>
            
 
