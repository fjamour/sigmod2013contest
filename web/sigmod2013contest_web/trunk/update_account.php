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

<?php include('countries_options.php'); ?>

<?php
	if($_SESSION['auth'] != 1) {
		header('Location: login.php');
		exit(0);
	}
?>

<?php
	$user_row = db_get_user_row($_SESSION['team_name']);
?>

<?php
	$dum_var;
	$pass_credentials = false;
	$err_msgs = array('email'=>'', 'institution'=>'', 'country'=>'', 'password'=>'', 'password_c'=>'');
	$account_updated = false;
	if(isset($_POST['Update'])) {// user submitted information, do all the checks on the inputs
		$pass_email          = validate_email($_POST['email'], $err_msgs['email']);
		$pass_instit         = validate_instit($_POST['institution'], $err_msgs['institution']);
		$pass_country        = validate_country($_POST['country'], $err_msgs['country']);
		$pass_password       = validate_password($_POST['password'], $_POST['password2'], $err_msgs['password']);
		
		$pass_password_c     = validate_password($_POST['password_c'], $_POST['password_c'], $dum_var);
		if($pass_password_c)
			$pass_credentials = db_check_login($user_row['team_name'], $_POST['password_c']);
			
		if(!$pass_credentials)
			$err_msgs['password_c'] = 'Wrong password, please try again.';

		if($pass_email && $pass_instit && $pass_country && $pass_password && $pass_password_c && $pass_credentials) {
			db_update_team($user_row['team_name'],
                           $_POST['email'],
                           $_POST['institution'],
                           $_POST['country'],
                           $_POST['password'],
                           isset($_POST['hide_instit']),
                           isset($_POST['hide_country']));
			$user_row = db_get_user_row($_SESSION['team_name']);
			$account_updated = true;
		}
	}
?>

<?php if(!$account_updated) {?>
<section id="main">
	<p><h1>Update Account Details</h1></p>
	<p>
		<table class="no_background">
			<form name="create_account_form" method="post" action="update_account.php">
				<tr>
					<td>Team Name:</td>
					<td> <?php echo $user_row['team_name'];?> </td>
					<td id="error_style"> <?php echo $err_msgs['team_name']; ?> </td>
				</tr>
				<tr>
					<td>Email:</td>
					<?php $email = sprintf('value="%s"', $user_row['team_email']);?>
					<td><input name="email" type="text" id="email" size="30" <?php echo $email;?>></td>
					<td id="error_style"> <?php echo $err_msgs['email']; ?> </td>
				</tr>
				<tr>
					<td style="vertical-align: top;">Institution:</td>
					<td>
						<?php $instit = sprintf('value="%s"', $user_row['team_institute']);?>
						<input name="institution" type="text" id="institution" size="30" <?php echo $instit;?> >
						<br>
						<?php $checked_i = $user_row['show_instit']?"":"checked";?>
						<input type="checkbox" name="hide_instit" <?php echo $checked_i;?> >
						<span style="font-size:small;">Do not show my institution name on the leaderboard.</span>
					</td>
					<td id="error_style"> <?php echo $err_msgs['institution']; ?> </td>
				</tr>
				<tr>
					<td style="vertical-align: top;">Country:</td>
					<td>
						<select name="country" type="text" id="country" width="30"> <?php echo get_countries($user_row['team_country']); ?> </select>
						<br>
						<?php $checked_c = $user_row['show_country']?"":"checked";?>
						<input type="checkbox" name="hide_country" <?php echo $checked_c?> >
						<span style="font-size:small;">Do not show my country flag on the leaderboard.</span>					
					</td>
					<td id="error_style"> <?php echo $err_msgs['country']; ?> </td>
				</tr>
				<tr>
					<td>Current Password:</td>
					<td><input name="password_c" type="password" id="password_c" size="30"></td>
					<td id="error_style"> <?php echo $err_msgs['password_c']; ?> </td>
				</tr>
				<tr>
					<td>New Password:</td>
					<td><input name="password" type="password" id="password" size="30"></td>
					<td id="error_style"> <?php echo $err_msgs['password']; ?> </td>
				</tr>
				<tr>
					<td>Repeat New Password:</td>
					<td><input name="password2" type="password" id="password2" size="30"></td>
					<td id="error_style"> <?php //no need to have anything here ?> </td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="Update" value="Update Account"></td>
				</tr>
			</form>
		</table>
	</p>
</section>
<?php } else {?>
<section id="main">
	<p>
		Your account details have been updated successfully!
		<br>
		Do you want to go to your <a href='dashboard.php'>dashboard</a>?
	</p>
</section>
<?php } ?>

<?php include('footer.php'); ?>
