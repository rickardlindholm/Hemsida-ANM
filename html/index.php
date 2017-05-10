<?php
session_start();
require_once 'dbconnect.php';
if (isset($_SESSION['userSession'])!="")
{
	header("Location: home.php");
	exit;
}

if (isset($_POST['btn-login']))
{
	$email = strip_tags($_POST['email']);
	$password = strip_tags($_POST['password']);
	$email = $DBcon->real_escape_string($email);
	$password = $DBcon->real_escape_string($password);

	$query = $DBcon->query("SELECT user_id, email, password, authenticated FROM $DBuserTable WHERE email='$email'");
	$row=$query->fetch_array();
	$count = $query->num_rows; // if email/password are correct returns must be 1 row
	if (password_verify($password, $row['password']) && $count==1 && $row['authenticated'] ==1)
	{
		$_SESSION['userSession'] = $row['user_id'];
		header("Location: home.php");
	}
	else
	{
		$msg = "<div class='alert alert-danger'>
		<span class='glyphicon glyphicon-info-sign'></span> &nbsp; Invalid Email or Password or not verified !
		</div>";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Coding Cage - Login & Registration System</title>
</head>
<body>

<div class="signin-form">

<div class="container">
<form class="form-signin" method="post" id="login-form">
<h2 class="form-signin-heading">Sign In</h2><hr />

<?php
if(isset($msg))
{
	echo $msg;
}
?>
<div class="form-group">
<input type="email" class="form-control" placeholder="Email address" name="email" required />
<span id="check-e"></span>
</div>
<div class="form-group">
<input type="password" class="form-control" placeholder="Password" name="password" required />
</div>
<hr />
<div class="form-group">
<button type="submit" class="btn btn-default" name="btn-login" id="btn-login">
<span class="glyphicon glyphicon-log-in"></span> &nbsp; Sign In
</button>
<a href="register.php" class="btn btn-default" style="float:right;">Sign UP Here</a>
</div>
</form>
</div>
</div>
<br>

<table border="3">
<thead>
<tr>
<th>ID</th>
<th>IP</th>
<th>Interfaces</th>
<th>UpTime</th>
<th>FirstTime</th>
<th>LastTime</th>
<th>InCounter</th>
<th>OutCounter</th>
<th>TotIn</th>
<th>TotOut</th>
<th>Count</th>
<th>AvgIn</th>
<th>AvgOut</th>
<th>InBitrate</th>
<th>OutBitrate</th>
</tr>
</thead>

<?php
if (!$query = $DBcon->query("SELECT * FROM $DBlogTable WHERE LastUpdateTime >DATE_SUB(NOW(), INTERVAL 5 MINUTE)"))
{
	printf("No Data: %s\n", $mysqli->error);
}
else
{
	while($row=$query->fetch_array())
	{
		echo "<tr><td>".$row["DeviceID"]."</td><td>".$row["IP"].
		"</td><td>".$row["Interface"]."</td><td>".$row["SysUpTime"]."</td><td>".$row["FirstUpdateTime"].
		"</td><td>".$row["LastUpdateTime"]."</td><td>".$row["InCounter"]."</td><td>".$row["OutCounter"].
		"</td><td>".$row["TotalInCounter"]."</td><td>".$row["TotalOutCounter"]."</td><td>".$row["Counter"].
		"</td><td>".$row["AverageIn"]."</td><td>".$row["AverageOut"]."</td><td>".$row["InBitRate"]."</td><td>".$row["OutBitRate"]."</td></tr>";
	}
}
?>
</tbody>
</table>
</body>
</html>
