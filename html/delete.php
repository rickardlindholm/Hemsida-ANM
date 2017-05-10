<?php
session_start();
require_once 'dbconnect.php';
if($_GET['id'] != "")
{
	$id = $_GET['id'];
        $query = $DBcon->query("DELETE FROM $DBdevices WHERE id='$id'");
}
header("Location: home.php");
?>
