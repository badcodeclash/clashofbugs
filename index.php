<html> 
<head> 
<?php 

require 'functions.php'; 

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

?>
<meta http-equiv="content-type" content="text/html; charset=utf-8"> 

<link rel="stylesheet" href="res/style.css">
<title>Clash of Bugs</title> 
</head> 

<body> 
<div class='wrap'>

	<div class='screen'>
	
		<div class='logo'></div>
		<?php page_controller(); ?>

		
		
			<?php 
			if (isset($_SESSION['log'])){
				
			echo '<div class="status">';
			echo '<div class="wrap" id="log">';
			echo $_SESSION['log']; 
			echo '</div>';
			echo '</div>';
			
			}
			?>
		
			
		<br>
		<?php
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $start), 4);

		echo 'page: '.$total_time.' ms | db: '.$_SESSION['sql_num'].' req '.$_SESSION['sql_time'].' ms';
		?>

	</div>
	

</div>
<script>

var scr = document.getElementById("log");
if (scr){
	scr.scrollTop = scr.scrollHeight;
}

</script>
</body> 

</html>