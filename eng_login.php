<?php

require 'functions.php';

if ( isset($_POST['trap'])){	$trap=safety_first($_POST['trap']);	}

if ($trap){//honeypot trap

header("HTTP/1.1 301 Moved Permanently"); 
header("Location: index.php"); //exit with error hack attempt
exit(); 

} 

if ( isset($_POST['username'])){	$login_username=safety_first($_POST['username']);	}
if ( isset($_POST['password'])){	$login_password=safety_first($_POST['password']);	}



//get user data by username
//$login_user = ORM::for_table('users')->where('user_login', $login_username)->find_one();



$user= db_connect('SELECT * FROM users WHERE name = "'.$login_username.'"');


if ($user=='empty'){
	db_push("INSERT INTO `users` (`id`, `name`, `password`, `hp`, `dmg`) VALUES (NULL, '".$login_username."', '".$login_password."', '100', '10')");
	//echo 'вы зарегистрированы';
	
	$user= db_connect('SELECT * FROM users WHERE name = "'.$login_username.'"');
	
	$_SESSION['user_id']=$user['id'];
	
	header("HTTP/1.1 301 Moved Permanently"); 
	header("Location: index.php?message=2"); 
	exit(); 
	
}else{
	if ($user['password']==$login_password){
		$_SESSION['user_id']=$user['id'];
		//echo 'вы вошли';
		
		db_push('UPDATE users SET session=0 WHERE id="'.$_SESSION['user_id'].'"');
		db_push('UPDATE users SET status=NULL WHERE id="'.$_SESSION['user_id'].'"');

		header("HTTP/1.1 301 Moved Permanently"); 
		header("Location: index.php?message=3"); 
		exit(); 
		
	}else{
		//echo 'неправильный пароль';
		
		header("HTTP/1.1 301 Moved Permanently"); 
		header("Location: index.php?message=1"); 
		exit(); 		
		
	}
}

//header("HTTP/1.1 301 Moved Permanently"); 
//header("Location: index.php"); 
//exit(); 