<?php
session_start();
require_once 'dbconnect.php';
if($_GET['user_id'] != "")
{
	$authenticated = $_GET['authenticated'];
        $user_id = $_GET['user_id'];
	echo "$authenticated";
	if($authenticated == 1)
	{
 	        $query = $DBcon->query("UPDATE $DBuserTable SET authenticated = NULL WHERE user_id='$user_id'");
	}
	else
	{
	        $query = $DBcon->query("UPDATE $DBuserTable SET authenticated = 1 WHERE user_id='$user_id'");
	}

}
header("Location: index.php");
?>



















