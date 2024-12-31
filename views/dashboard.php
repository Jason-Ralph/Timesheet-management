<?php include(ASSET_PATH . '/includes/header.php');?>
<div class="content">

<lottie-player src="https://assets5.lottiefiles.com/packages/lf20_edpg3c3s.json"  background="transparent"  speed="0.3"  style="width: 200px; height: 200px; position:absolute; bottom:30px; right:30px; z-index:0; opacity:0.3;"  autoplay></lottie-player>	
	
<div class="menu-trigger"></div>	
<section class="profile">
<article>

	<h1 align="center">Welcome to your profile</h1>
	<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($_SESSION['error_message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="row gap-30">
<div class="profile-card-left">	
<div class="profile-banner"></div>
<div class="profile-content-row">
<div class="profile-image">
<?php
$userImg = isset($_SESSION['user']['userImg']) && $_SESSION['user']['userImg'] ? $_SESSION['user']['userImg'] : 'Placeholder.jpg';
?>
<img src="../assets/images/<?php echo htmlspecialchars($userImg, ENT_QUOTES, 'UTF-8'); ?>" alt="User Image"/>

</div>
<div class="profile-content">
<div class="basic">
<p class="name"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></p>    
<p class="title"><strong>Title:</strong><?php echo htmlspecialchars($_SESSION['user']['userTitle']); ?></p>    
<p class="department"><strong>Department:</strong><?php echo htmlspecialchars($_SESSION['user']['department']); ?></p>    
<p class="manager"><strong>Manager:</strong><?php echo htmlspecialchars($_SESSION['user']['manager']); ?></p>    
<p class="roleId"><strong>User type:</strong><?php echo htmlspecialchars($_SESSION['user']['role_name']); ?></p>		
</div>
<div class="socials">
<ul class="social-list">
	
<a href="mailto:<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" target="_blank"><li class="email"> </li></a>    
<a href="tel:<?php echo htmlspecialchars($_SESSION['user']['phone']); ?>" target="_blank"><li class="phone"> </li></a>
<a href="https://<?php echo htmlspecialchars($_SESSION['user']['linkedin']); ?>" target="_blank"><li class="linkedin"> </li></a>    
<a href="<?php echo htmlspecialchars($_SESSION['user']['facebook']); ?>" target="_blank"><li class="facebook"> </li></a>    
<a href="https://api.whatsapp.com/send?phone=<?php echo htmlspecialchars($_SESSION['user']['phone']); ?>" target="_blank"><li class="whatsapp"></li></a>	
	
		
</ul>		
</div>
</div><div class="card-logo">

<img src="../assets/images/Logo.svg" alt=""/> 

</div>	
</div>		
</div>
<div class="profile-card-right">	
<h4>Additional details</h4>	


<p class="languages">Languages<br>
<span><?php echo htmlspecialchars($_SESSION['user']['languages']); ?></span></p>	

<p class="join">Join date<br>
<span><?php echo htmlspecialchars($_SESSION['user']['joinDate']); ?></span></p>

<p class="birthday">Birthday<br>
<span><?php echo htmlspecialchars($_SESSION['user']['birthday']); ?></span></p>

<p class="experience">Experience<br>
<span><?php echo htmlspecialchars($_SESSION['user']['experience']); ?></span></p>	

<p class="address">Address<br>
<span><?php echo htmlspecialchars($_SESSION['user']['address']); ?></span></p>	

</div>	

	</div>
</article>
</section>
	
</div>	

<?php include(ASSET_PATH . '/includes/footer.php');?>
