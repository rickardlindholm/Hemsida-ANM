<?php
$DBhost = "localhost";
$DBuser = "root";
$DBpass = "medo";
$DBname = "task3";
$DBlogTable = "Log_Table";
$DBuserTable = "USERS";
$DBdevices = "DEVICES";

$DBcon = new MySQLi($DBhost,$DBuser,$DBpass,$DBname);
if ($DBcon->connect_errno)
{
	die("ERROR : -> ".$DBcon->connect_error);
}
