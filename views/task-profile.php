<?php
$csrf_token = $_SESSION[ 'csrf_token' ] = bin2hex( random_bytes( 32 ) );
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
<div class="task-profile-card-left">	
<div class="task-profile-banner"></div>
<div class="task-profile-content-row">
<div class="task-profile-image">
    <img src="<?php echo htmlspecialchars($task['clientLogo'], ENT_QUOTES, 'UTF-8'); ?>" alt="User Image"/>
</div>
<div class="task-profile-content">
<div class="basic">
<p class="name"><?php echo htmlspecialchars($task['client']); ?></p>    
<p class="title"><strong>Task:</strong><?php echo htmlspecialchars($task['taskType']); ?></p>    
<p class="department"><strong>Report type:</strong><?php echo htmlspecialchars($task['reportType']); ?></p>    
<p class="manager"><strong>Start Task Time:</strong><?php echo htmlspecialchars($task['taskStartTime']); ?></p>    
<p class="roleId"><strong>End Task Time:</strong><?php echo htmlspecialchars($taskEndTime = isset( $task[ 'taskEndTime' ] ) && $task[ 'taskEndTime' ] ? $task[ 'taskEndTime' ] : 'TDB'); ?></p>		
<p class="roleId"><strong>Total Task Time:</strong><?php echo htmlspecialchars($taskTotalTime = isset( $task[ 'taskTotalTime' ] ) && $task[ 'taskTotalTime' ] ? $task[ 'taskTotalTime' ] : 'TBD'); ?></p>		
</div>
<div class="socials">
<p><strong>Comments:</strong><br>
<?php echo htmlspecialchars($comments = isset( $task[ 'comments' ] ) && $task[ 'comments' ] ? $task[ 'comments' ] : 'Sorry no comment'); ?>		
</p>	
</div>
</div><div class="task-card-logo">

<img src="../assets/images/Logo.svg" alt=""/> 

</div>	
</div>		
</div>
</div>
        </div>
        <a href="<?php echo BASE_URL; ?>/tasks" class="btn btn-primary margin-30T">Back to tasks</a> </div>
    </article>
  </section>
</div>
<?php include(ASSET_PATH . '/includes/footer.php'); ?>
