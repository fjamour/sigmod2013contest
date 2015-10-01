<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
?>

<?php include('header.php'); ?>

<?php include('util.php'); ?>

<?php include('mysql.php') ?>

<?php
	//if already logged in? don't allow to log in again!
	if($_SESSION['auth'] == 1) {
		header('Location: dashboard.php');
		exit(0);
	}
?>

<?php
	$err_msgs = array('login'=>'');
	$login_success = false;
	if(isset($_POST['Submit'])) {
		$dum_var;
		$pass_credentials = false;

		$pass_name     = validate_name($_POST['name'], $dum_var);
		$pass_password = validate_password($_POST['password'], $_POST['password'], $dum_var);
		if($pass_name && $pass_password)
			$pass_credentials = db_check_login($_POST['name'], $_POST['password']);

		if($pass_credentials) {
			session_start();
			setcookie("name", $name, time()+(84600*30));
			$_SESSION['auth'] = 1;
			$user_row = db_get_user_row($_POST['name']);
			$_SESSION['team_name'] = $user_row['team_name'];
			$_SESSION['team_id']   = $user_row['team_id'];
			$login_success = true;
		} else {
			$err_msgs['login'] = "Wrong username or password! Try again.";
		}
	}
?>

<?php if(!$login_success) { ?>
<section id="main">
			<div class="logos">
	       		<a href="http://www.sigmod.org/" target="_blank"><img src="images/logo-acm-sigmod.jpg" alt="ACM SIGMOD" width="225"/></a>       
				<a href="http://www.microsoft.com/" target="_blank"><img src="images/logo-microsoft.jpg" alt="Microsoft" /></a>
				<a href="http://www.csail.mit.edu/" target="_blank"><img src="images/logo-mit.jpg" alt="MIT" /></a>
				<a href="http://www.kaust.edu.sa/" target="_blank"><img src="images/logo-kaust.jpg" alt="KAUST" /></a>
			</div>
			<br>
	<p>Don't have an account yet? <a href="create_account.php">Create one!</a></p>
	<p><h1>Login</h1></p>
	<p>
		<table class="no_background">
			<form name="login_form" method="post" action="login.php">
				<tr>
					<td>Team Name:</td>
					<td><input name="name" type="text" id="name"></td>
					<td id="error_style"> <?php echo $err_msgs['login']; ?> </td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input name="password" type="password" id="password"></td>
					<td id="error_style"></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="Submit" value="Login"></td>
				</tr>
			</form>
		</table>
	</p>
</section>
<?php } else { ?>
<section id="main">
	<p>
		Login succeeded! You can go to <a href="dashboard.php">Dashboard</a> to upload submissions.
	</p>
</section>
<?php } ?>

<?php include('footer.php'); ?>
