<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
?>

<?php include('header.php'); ?>

<?php include('mysql.php'); ?>

<?php include('util.php'); ?>

<?php
	function check_account_exists($name, $email, &$err_msg_name, &$err_msg_email)
	{
		$name_exists;
		$email_exists;
		db_check_team($name, $email, $name_exists, $email_exists);
		if($name_exists)
			$err_msg_name = 'Team name already exists, please choose another name!';
		if($email_exists)
			$err_msg_email = 'Another team registered with this email, please enter another one!';

		if($name_exists || $email_exists)
			return false;
		else
			return true;
	}
?>

<?php
	//TODO May be should do something if somebody tried to access this page while logged in?
	$err_msgs = array('team_name'=>'', 'email'=>'', 'institution'=>'', 'country'=>'', 'password'=>'');
	$account_created = false;
	if(isset($_POST['Submit'])) {// user submitted information, do all the checks on the inputs
		$pass_name           = validate_name($_POST['name'], $err_msgs['team_name']);
		$pass_email          = validate_email($_POST['email'], $err_msgs['email']);
		$pass_instit         = validate_instit($_POST['institution'], $err_msgs['institution']);
		$pass_country        = validate_country($_POST['country'], $err_msgs['country']);
		$pass_password       = validate_password($_POST['password'], $_POST['password2'], $err_msgs['password']);

		if($pass_name && $pass_email)
			$pass_account_exists = check_account_exists($_POST['name'], $_POST['email'], $err_msgs['team_name'], $err_msgs['email']);
		else
			$pass_account_exists = false;

		if($pass_name && $pass_email && $pass_instit && $pass_country && $pass_password && $pass_account_exists) {
			db_register_team($_POST['name'],
                             $_POST['email'],
                             $_POST['institution'],
                             $_POST['country'],
                             $_POST['password'],
                             isset($_POST['hide_instit']),
                             isset($_POST['hide_country']));
			$account_created = true;
			// TODO may be account confirmation by email or something? LATER
		}
	}
?>

<?php if(!$account_created) {?>
<section id="main">
	<p><h1>Create an Account</h1></p>
	<p>
		<table class="no_background">
			<form name="create_account_form" method="post" action="create_account.php">
				<tr>
					<td>Team Name:</td>
					<td><input name="name" type="text" id="name" size="30"></td>
					<td id="error_style"> <?php echo $err_msgs['team_name']; ?> </td>
				</tr>
				<tr>
					<td>Email:</td>
					<td><input name="email" type="text" id="email" size="30"></td>
					<td id="error_style"> <?php echo $err_msgs['email']; ?> </td>
				</tr>
				<tr>
					<td style="vertical-align: top;">Institution:</td>
					<td>
						<input name="institution" type="text" id="institution" size="30">
						<br>
						<input type="checkbox" name="hide_instit">
						<span style="font-size:small;">Do not show my institution name on the leaderboard.</span>
					</td>
					<td id="error_style"> <?php echo $err_msgs['institution']; ?> </td>
				</tr>
				<tr>
					<td style="vertical-align: top;">Country:</td>
					<td>
						<select name="country" type="text" id="country" width="30"> <?php include('countries_options.php'); echo $countries_op; ?> </select>
						<br>
						<input type="checkbox" name="hide_country">
						<span style="font-size:small;">Do not show my country flag on the leaderboard.</span>					
					</td>
					<td id="error_style"> <?php echo $err_msgs['country']; ?> </td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input name="password" type="password" id="password" size="30"></td>
					<td id="error_style"> <?php echo $err_msgs['password']; ?> </td>
				</tr>
				<tr>
					<td>Repeat Password:</td>
					<td><input name="password2" type="password" id="password2" size="30"></td>
					<td id="error_style"> <?php //no need to have anything here ?> </td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="Submit" value="Create Account"></td>
				</tr>
			</form>
		</table>
	</p>
</section>
<?php } else {?>
<section id="main">
	<p>
		Account created successfully <strong><?php echo $_POST['name']; ?></strong>!
		You can <a href="login.php">log in</a> now.
	</p>
</section>
<?php } ?>

<?php include('footer.php'); ?>
