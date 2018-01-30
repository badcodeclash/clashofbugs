<?php 

session_start();
session_unset();
session_destroy();
session_write_close();
setcookie(session_name(),'',0,'/');


header("HTTP/1.1 301 Moved Permanently"); 
header("Location: index.php"); 
exit(); 