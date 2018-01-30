<?php

require 'config.php';

session_start();

$_SESSION['sql_time'] = 0;
$_SESSION['sql_num']=0;
	
message();
function message(){

	if (isset($_SESSION['user_id'])){
		if(isset($_GET['message'])){

			if (safety_first($_GET['message'])==1){
				$_SESSION['log']=$_SESSION['log'].'<p class="log">Неправильный пароль</p>';
			}
			if (safety_first($_GET['message'])==2){
				$_SESSION['log']=$_SESSION['log'].'<p class="log">Вы зарегистрированы</p>';
			}
			if (safety_first($_GET['message'])==3){
				$_SESSION['log']=$_SESSION['log'].'<p class="log">Вы вошли</p>';
			}
			if (safety_first($_GET['message'])==4){
				$_SESSION['log']=$_SESSION['log'].'<p class="log">Нет подходящих игроков</p>';
			}	
			if (safety_first($_GET['message'])==5){
				$_SESSION['log']=$_SESSION['log'].'<p class="log">Login already exist</p>';
			}
			if (safety_first($_GET['message'])==6){
				$_SESSION['log']=$_SESSION['log'].'<p class="log">New record has been created successfully</p>';
			}			
	
	
		}
	}

	
}	

function push_log($message){
	$_SESSION['log']=$_SESSION['log'].'<p class="log">'.$message.'</p>';
} 
	
	
function db_push($request){
	
	global $db_host;
	global $db_username;
	global $db_password;
	global $db_name;
	
	$started = microtime(true); //получаем время
	
	$connect = mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$end = microtime(true);//получаем время еще раз
	$difference = $end - $started; //находим разницу
	$queryTime = number_format($difference, 10);//формат
	
	$_SESSION['sql_time'] = $_SESSION['sql_time']+$queryTime;//хоба!
	$_SESSION['sql_num']++;
	
	if (mysqli_query($connect, $request) === TRUE) {
		return 'true';
	} else {
		return 'false';
	}

	mysqli_close($connect);
}

function db_connect($request){
	
	global $db_host;
	global $db_username;
	global $db_password;
	global $db_name;
	
	$started = microtime(true); //получаем время
	
	$connect = mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$end = microtime(true);//получаем время еще раз
	$difference = $end - $started; //находим разницу
	$queryTime = number_format($difference, 10);//формат
	
	$_SESSION['sql_time'] = $_SESSION['sql_time']+$queryTime;//хоба!
	$_SESSION['sql_num']++;
	
	if ($connect->connect_error) {
		$response = 'error';
	}else{
		$response = $connect->query($request);
		
		if(mysqli_num_rows($response)==1){
			
		$response= mysqli_fetch_assoc($response);
			
		}elseif(mysqli_num_rows($response)>1) {//если есть результаты
			
			$response_array = array();
	
			while ($row = mysqli_fetch_assoc($response)) {//ассоциативизируем ответ
				array_push($response_array, $row);
			}
			
			$response = $response_array;

	
		}else{
			$response = 'empty';//быдлокод
		}
	} 
	
	mysqli_close($connect);
	
	return $response;
	
}


function safety_first($data) {
	
global $db_host;
global $db_username;
global $db_password;
global $db_name;
	
$mysqli = mysqli_connect($db_host, $db_username, $db_password, $db_name);

$data = str_replace(array("\n", "\r", "\r\n"), ' ', $data);
$data = $mysqli->real_escape_string($data);

$data = strip_tags($data);
$data = htmlentities($data);


return $data; 	
}




function page_controller(){
	
	$pages = array('home', 'battle', 'duels');
	
	if(isset($_GET['page'])){

		$page=safety_first($_GET['page']);
		
		if (!in_array($page, $pages)){
			$page='home';
		}
	}else{
		$page='home';
	}
	
	if ($page=='home'){
		page_home();
	}
	if ($page=='duels'){ //ЕСЛИ НА СТРАНИЦЕ ДУЭЛЕЙ
		duel();
	}
}

function duel(){
	//ПОЛУЧАЕМ ДАННЫЕ ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ
	$user_data= db_connect('SELECT * FROM users WHERE id = "'.$_SESSION['user_id'].'"');
		
	if ($user_data['status']==''){//ЕСЛИ НЕТ СТАТУСА СТАВИМ ЖДЕТ ПАРУ (FREE)
	//db_push('UPDATE users SET session='.random_int(1,9999)*time().' WHERE id="'.$_SESSION['user_id'].'"');
	db_push('UPDATE users SET status="free" WHERE id="'.$_SESSION['user_id'].'"');
	
	header("HTTP/1.1 301 Moved Permanently"); 
	header("Location: index.php?page=duels"); //exit with error hack attempt
	exit(); 
	
	
	}
	if ($user_data['status']=='free'){ //ЕСЛИ ЖДЕТ ПАРУ ИЩЕМ ПОДХОДЯЩЕГО
		$users_session= db_connect('SELECT * FROM users WHERE status = "free"');
	
		if ((count($users_session)!=9)&&($users_session!='empty')){
			foreach ($users_session as $user_session){
						
			echo $user_session['id'].' '.$user_session['name'].' '.$user_session['session'];
						
				if (($user_session['id']!=$_SESSION['user_id'])){
							
				$session_id=random_int(1,9999)*time();
	
				db_push('UPDATE users SET session='.$session_id.' WHERE id="'.$_SESSION['user_id'].'"');
				db_push('UPDATE users SET session='.$session_id.' WHERE id="'.$user_session['id'].'"');
				db_push('UPDATE users SET status="fight" WHERE id="'.$_SESSION['user_id'].'"');
				db_push('UPDATE users SET status="fight" WHERE id="'.$user_session['id'].'"');	
				db_push('UPDATE users SET current_hp="'.$user_data['hp'].'" WHERE id="'.$_SESSION['user_id'].'"');
				db_push('UPDATE users SET current_hp="'.$user_session['hp'].'" WHERE id="'.$user_session['id'].'"');
				push_log('Противник найден.');



				}
			}
		}else {
			push_log('Нет пары.');
		}
	
		echo '<a href="?page=duels">Обнови страницу чтобы не проспать битву!</a>';
	}
	if ($user_data['status']=='fight'){//ЕСЛИ БИТВА УЖЕ НАЧАЛАСЬ
				
		
		$users_fighters= db_connect('SELECT * FROM users WHERE session = "'.$user_data['session'].'"');
		foreach ($users_fighters as $users_fighter){
			if ($users_fighter['id']!=$_SESSION['user_id']){
				$enemy_data=$users_fighter;
			}
		}
	


		echo '<div class="status">';
		echo '<p class="name">'.$user_data['name'].'</p>';
		echo '<div class="bar">';
		echo '<div style="width:'.($user_data['current_hp']/$user_data['hp']*100).'%;"></div>';
		echo '</div>';
		echo '<p class="name">hp:'.$user_data['hp'].' dmg:'.$user_data['dmg'].'</p>';
		echo '</div>';
	
		echo '<p class="vs">VS</p>';
	
		echo '<div class="status">';
		echo '<p class="name">'.$enemy_data['name'].'</p>';
		echo '<div class="bar">';
		echo '<div style="width:';
		if(($enemy_data['current_hp']/$enemy_data['hp']*100)>0){
			echo ($enemy_data['current_hp']/$enemy_data['hp']*100);
		}else{
			echo '0';
		}
		
		echo '%;"></div>';
		echo '</div>';
		echo '<p class="name">hp:'.$enemy_data['hp'].' dmg:'.$enemy_data['dmg'].'</p>';
		echo '</div>';	
	
		if ($enemy_data['current_hp']<=0){
			push_log($enemy_data['name'].' повержен! Вы победили!');
			echo '<a href="?page=home">На главную!</a>';
			
			db_push('UPDATE users SET current_hp=0 WHERE id="'.$enemy_data['id'].'"');
			db_push('UPDATE users SET hp=hp+1 WHERE id="'.$enemy_data['id'].'"');
			db_push('UPDATE users SET dmg=dmg+1 WHERE id="'.$enemy_data['id'].'"');
			db_push('UPDATE users SET session=0 WHERE id="'.$enemy_data['id'].'"');
			db_push('UPDATE users SET status="def" WHERE id="'.$enemy_data['id'].'"');
			db_push('UPDATE users SET highscore=highscore-1 WHERE id="'.$enemy_data['id'].'"');

			db_push('UPDATE users SET hp=hp+1 WHERE id="'.$_SESSION['user_id'].'"');
			db_push('UPDATE users SET dmg=dmg+1 WHERE id="'.$_SESSION['user_id'].'"');
			db_push('UPDATE users SET session=0 WHERE id="'.$_SESSION['user_id'].'"');
			db_push('UPDATE users SET status=NULL WHERE id="'.$_SESSION['user_id'].'"');
			db_push('UPDATE users SET highscore=highscore+1 WHERE id="'.$_SESSION['user_id'].'"');

		}elseif($user_data['current_hp']<=0){
			push_log('Вы повержены! Победитель '.	$enemy_data['name'].'!');
			
			echo '<a href="?page=home">На главную.</a>';


		}else{
	
			echo '<a href="?page=duels&attack=1">АТАКЕ111!</a>';
	
			if((isset($_GET['attack']))&&(safety_first($_GET['attack'])==1)){
	
				db_push('UPDATE users SET current_hp="'.($enemy_data['current_hp']-$user_data['dmg']).'" WHERE id="'.$enemy_data['id'].'"');
	
			}
		}
	
					
	
		
	
		}
		if ($user_data['status']=='def'){//ЕСЛИ ПОРАЖЕНИЕ
			db_push('UPDATE users SET status=NULL WHERE id="'.$_SESSION['user_id'].'"');
			push_log('Поражение');
			header("HTTP/1.1 301 Moved Permanently"); 
			header("Location: index.php"); 
			exit(); 
		}
}

function page_home(){
	if (isset($_SESSION['user_id'])){
		$user_data= db_connect('SELECT * FROM users WHERE id = "'.$_SESSION['user_id'].'"');
		?>		
		<div class='status'>
		<p><?php echo $user_data['name']; ?></p>
		<?php echo 'hp:'.$user_data['hp'].' dmg:'.$user_data['dmg'].' highscore:'.$user_data['highscore'];?>
		</div>
		<a href='eng_logout.php'>Выход</a>
		<a href='?page=duels'>Битва</a>
		<?php		
	}else{
		?>		
		<form action='eng_login.php' method='POST'>
			<input name='username' required type='text' 	placeholder='username' maxlength='20'>
			<input name='password' required type='password' placeholder='password' maxlength='20'>
			<input name='trap'				type='text'>
			<input							type='submit' value='Вход'>
		</form>
		<?php
	}
}