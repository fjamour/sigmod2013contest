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

<?php include('globals.php'); ?>

<?php
	if($_SESSION['auth'] != 1) {
		header('Location: login.php');
		exit(0);
	}
?>

<?php // deadline, don't accept anything!
/*
	$tm = time();
	if($tm > 1366059599) { // this is 23:59:59 Apr 15, 2013  Jeddah local time
		echo '<section id="main"><p>The deadline passed already, we are not accepting submissions anymore!</p></section>';
		include('footer.php');
		exit;
	}
*/
?>

<?php
	$err_msgs = array('file'=>'', 'notes'=>'');
	$upload_str = "Your notes are kept private and are there to help you organize your submissions. Limit your notes to 256 characters.";
?>

<?php //check if has pending submissions, to block!
	$block_submission  = (db_get_num_pending_submissions($_SESSION['team_id']) > 0);
?>

<?php if($block_submission) {?>
<section id="main">
	<p>
		You have a pending submission, you can not upload a new submission!
	</p>
</section>
<?php include('footer.php'); exit; }?>

<?php
	$upload_success = false;
	if(isset($_POST['Submit'])) {
		$pass_filename   = validate_lib_filename($_FILES['file'], $err_msgs['file']);
		$pass_notes      = validate_submission_notes($_POST['notes'], $err_msgs['notes']);

		if($pass_filename && $pass_notes) {
			$submission_time = time();
			if($_POST['notes'] != $upload_str) $notes = $_POST['notes']; else $notes = '<center>--</center>';
			if(db_insert_submission($_SESSION['team_id'], $submission_time, $notes, $_FILES['file']['name'])) {
				$submission_path = $SUBMISSIONS_PATH . '/' . $_SESSION['team_name'];
				//XXX might have permission problems here..
				if(!is_dir($submission_path)) {// first submission for this team, make a folder for this team submissions
					mkdir($submission_path);
					chmod($submission_path, 0777);
				}
				$submission_path = $submission_path . '/' . $submission_time;
				if(!is_dir($submission_path)) {// this assumes that two submissions with the same timestamp from the same team can happen!
					mkdir($submission_path);
					chmod($submission_path, 0777);				
				}
				$p = $submission_path . '/' . $_FILES["file"]["name"];
		  		move_uploaded_file($_FILES["file"]["tmp_name"], $p);
		  		chmod($p, 0744);	  		
				$upload_success = true;
				//insert an entry in the submissions ip log
				db_insert_submission_ip($_SESSION['team_id'], $submission_time, get_ip_address());
			}
		}
	}
?>

<?php if(!$upload_success) {?>
<section id="main">
	<p><h1>Upload a Submission</h1></p>
	<p>
		<form action="upload.php" enctype="multipart/form-data" method="post">
			<table class="no_background">
				<tr>
					<td>Library File:</td>
					<td><input type="file" name="file" id="file" /></td>
					<td id="error_style"> <?php echo $err_msgs['file']; ?> </td>
				</tr>
				<tr>
					<td style="vertical-align: top;">Notes:</td>
					<td>
						<textarea name="notes" rows="7" cols="40" onFocus="this.value=''; return false;" onKeyPress="return (this.value.length < 256);"><?php echo $upload_str; ?></textarea>
					</td>
					<td id="error_style"> <?php echo $err_msgs['notes']; ?> </td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="Submit" value="Upload File"></td>
				</tr>

			</table>
		</form>
	</p>
</section>
<?php } else {?>
<section id="main">
	<p>
		Your library was submitted successfully! Evaluating the submission might take a few minutes. <br>
		Go to <a href="dashboard.php">Dashboard</a> to check the result.
	</p>
</section>
<?php } ?>

<?php include('footer.php'); ?>
