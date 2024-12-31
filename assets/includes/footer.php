<script>
$(document).ready( function () {	   
(function() {
	var $body = document.body
	, $menu_trigger = $body.getElementsByClassName('menu-trigger')[0];

	if ( typeof $menu_trigger !== 'undefined' ) {
		$menu_trigger.addEventListener('click', function() {
			$body.className = ( $body.className == 'menu-active' )? '' : 'menu-active';
		});
	}

}).call(this);	

	
// Immediately hide all 'ul' elements within '.sublist' on page load
$(".sublist ul").slideUp();

$(".sublist").click(function(){
  // Toggle visibility when '.sublist' is clicked
  $(this).find("ul").slideToggle();
});
		
});
</script>

    </body>
</html>