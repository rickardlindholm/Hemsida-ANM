<?php
session_start();
require_once 'dbconnect.php';
if (!isset($_SESSION['userSession']))
{
header("Location: index.php");
}
$query = $DBcon->query("SELECT * FROM $DBuserTable WHERE user_id=".$_SESSION['userSession']);
$userRow=$query->fetch_array();
if(isset($_POST['btn-addDevice']))
{
	$ip = strip_tags($_POST['ip']);
	$port = strip_tags($_POST['port']);
	$com = strip_tags($_POST['community']);
	$iface = strip_tags($_POST['interfaces']);
	$ip = $DBcon->real_escape_string($ip);
	$port = $DBcon->real_escape_string($port);
	$com = $DBcon->real_escape_string($com);
	$iface = $DBcon->real_escape_string($iface);

	$query = "INSERT INTO $DBdevices(IP,PORT,COMMUNITY,INTERFACES) VALUES('$ip','$port','$com','$iface')";
	if ($DBcon->query($query))
	{
		$msg = "<div class='alert alert-success'>
		<span class='glyphicon glyphicon-info-sign'></span> &nbsp; Successfully registered probing device!
		</div>";
	}
	else
	{
		$msg = "<div class='alert alert-danger'>
		<span class='glyphicon glyphicon-info-sign'></span> &nbsp; Error while registering device!
		</div>";
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Welcome -
		<?php
		echo $userRow['email'];
		?>
	</title>
</head>
<body>

	<ul class="nav navbar-nav navbar-right">
	<li><a href="#"><span class="glyphicon glyphicon-user"></span>&nbsp; <?php echo $userRow['email']; ?></a></li>
	<li><a href="logout.php?logout"><span class="glyphicon glyphicon-log-out"></span>&nbsp; Logout</a></li>
	</ul>
	<div class = "deviceform">
		<div  class = "container">
			<form class="formdevice" method="post" id="addform">
			<h2 class="form-add-device-heading">Add Device</h2><hr />
                        <?php
                        if (isset($msg))
                        {
                                echo $msg;
                        }
                        ?>
			<div class="form-group">
				<input type="text" class="form-control" placeholder="IP" name="ip" required  />
			</div>
			<div class="form-group">
				<input type="text" class="form-control" placeholder="Port" name="port" required  />
			</div>
			<div class="form-group">
			<input type="text" class="form-control" placeholder="Community" name="community" required  />
			</div>
			<div class="form-group">
				<input type="text" class="form-control" placeholder="Interfaces" name="interfaces" required  />
			</div>

			<div class="form-group">
				<button type="submit" class="btn btn-default" name="btn-addDevice">
				<span class="glyphicon glyphicon-log-in"></span> &nbsp; Add Device
				</button>
			</div>
		</div>
	</div>
<table border="3">
	<thead>
		<tr>
			<th>Email</th>
			<th>Authenticated</th>
			<th>Update</th>
		</tr>
	</thead>
	<tbody>
		<h2>Authenticate</h2><hr />
		<?php
		$query = $DBcon->query("SELECT * FROM $DBuserTable");
		while($user = $query->fetch_array())
		{
			echo "<tr><td>{$user['email']}</td><td>";
			if($user['authenticated']==1)
			{
				echo "Yes";
			}
			else
			{
				echo "No";
			}
			echo "</td><td><a href='update.php?user_id=".urlencode($user['user_id'])."&authenticated=".urlencode($user['authenticated']).";"."'>Toggle</a>";
			echo "</td></tr>";
		}
		?>
	</tbody>
</table>
<table border="3">
	<thead>
	<tr>
		<th>IP</th>
		<th>Port</th>
		<th>Community</th>
		<th>Interfaces</th>
		<th>Remove</th>
		</tr>
	</thead>
	<tbody>
	<h2>List Devices</h2><hr />
	<?php
	$query = $DBcon->query("SELECT * FROM $DBdevices");
	while($devices = $query->fetch_array())
	{
		echo "<tr><td>{$devices['IP']}</td><td>{$devices['PORT']}</td><td>{$devices['COMMUNITY']}</td><td>{$devices['INTERFACES']}</td><td>";
		echo "<a href='delete.php?id=".$devices['id']."'>Delete</a>";
		echo "</td></tr>";
	}
	?>
	</tbody>
</table>
</body>
</html>
