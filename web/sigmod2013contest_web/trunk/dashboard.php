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
	if($_SESSION['auth'] != 1) {
		header('Location: login.php');
		exit(0);
	}
?>

<?php
	$has_no_final_sub   = True;
	$has_submissions    = false;
	$submissions_arr    = db_get_user_submissions($_SESSION['team_id']);
	if(count($submissions_arr) != 0) {
		$rows = array();
		for($i = 0; $i < count($submissions_arr); $i++) {
			$rows[$i]['submission_time'] = date('M d, Y - h:ia', $submissions_arr[$i]['submission_time']);
			$rows[$i]['timestamp']       = $submissions_arr[$i]['submission_time'];
			$rows[$i]['notes']           = $submissions_arr[$i]['notes'];
			$rows[$i]['style']           = '';
			$rows[$i]['filename']        = $submissions_arr[$i]['filename'];
			
			$rows[$i]['small_res']       = $submissions_arr[$i]['runtime_small'];
			$rows[$i]['big_res']         = $submissions_arr[$i]['runtime_big'];
			$rows[$i]['new_res']         = $submissions_arr[$i]['runtime_new'];
			if($submissions_arr[$i]['result_code'] == -1) {// submission has not been evaluated yet
				$rows[$i]['small_res']   = '--';
				$rows[$i]['big_res']     = '--';
				$rows[$i]['new_res']     = '--';
				$rows[$i]['status']      = 'pending';
			} else {
				if($submissions_arr[$i]['result_code'] == 3) { // passed all (new, big, and small)
					$rows[$i]['small_res']       = $submissions_arr[$i]['runtime_small'];
					$rows[$i]['big_res']         = $submissions_arr[$i]['runtime_big'];
					$rows[$i]['new_res']         = $submissions_arr[$i]['runtime_new'];
					$rows[$i]['status']          = 'succeeded';
					$rows[$i]['style']           = 'class="passed"';
					$rows[$i]['style_small_res'] = 'class="passed"';
					$rows[$i]['style_big_res']   = 'class="passed"';
				} elseif($submissions_arr[$i]['result_code'] == 2) { // passed big and small
					$rows[$i]['small_res']       = $submissions_arr[$i]['runtime_small'];
					$rows[$i]['big_res']         = $submissions_arr[$i]['runtime_big'];
					$rows[$i]['new_res']         = 'failed';
					$rows[$i]['status']          = 'failed';
					$rows[$i]['style']           = 'class="failed"';
					$rows[$i]['style_small_res'] = 'class="passed"';
					$rows[$i]['style_big_res']   = 'class="passed"';
				} elseif($submissions_arr[$i]['result_code'] == 1) { // passed small only
					$rows[$i]['new_res']         = 'failed';
					$rows[$i]['big_res']         = 'failed';
					$rows[$i]['small_res']       = $submissions_arr[$i]['runtime_small'];
					$rows[$i]['status']          = 'failed';
					$rows[$i]['style']           = 'class="failed"';
					$rows[$i]['style_big_res']   = 'class="failed"';
					$rows[$i]['style_small_res'] = 'class="passed"';
				} elseif($submissions_arr[$i]['result_code'] == 0) { // didn't pass the unit tests (so it the benchmark was not executed)
					$rows[$i]['new_res']         = 'failed';
					$rows[$i]['big_res']         = 'failed';
					$rows[$i]['small_res']       = 'failed';
					$rows[$i]['status']          = 'failed';
					$rows[$i]['style']           = 'class="failed"';
					$rows[$i]['style_big_res']   = 'class="failed"';
					$rows[$i]['style_small_res'] = 'class="failed"';
				}
				//for old submissions, show N/A instead of fail for the new data
				//and for status if they passed small and big before
				if($rows[$i]['timestamp'] < 1363287209) {
					if($submissions_arr[$i]['result_code'] == 2)
						$rows[$i]['status'] = "N/A";
					$rows[$i]['new_res'] = "N/A";
				}
			}
		}
		//mark the final submission (last .tar.gz that passed everything)
		for($i = 0; $i < count($rows); $i++) {
			if($rows[$i]['status'] == 'succeeded' && $rows[$i]['filename'] == 'impl.tar.gz') {
				$rows[$i]['style']           = 'class="final"';
				$rows[$i]['style_small_res'] = 'class="final"';
				$rows[$i]['style_big_res']   = 'class="final"';
				$has_no_final_sub            = False;
				break;
			}
		}
		$has_submissions = true;
	}
?>

<section id="main">	        
	<p>
		Hi <strong><?php echo $_SESSION['team_name']; ?></strong>,
		this is your dashboard, where you can submit and review your implementations.		
	</p>
	<br>
	<p>
		To review the submission guidelines <a href="submission_guidelines.php">click here</a>
	</p>
	<br>
	<p>
		To update your account details <a href="update_account.php">click here</a>
	</p>
</section>

<?php if($has_no_final_sub) {?>
<section id="main">	        
	<p style="color:red; text-align: center;">
		<strong>You haven't made any source code submission yet!</strong> Check <a href="https://groups.google.com/d/msg/sigmod2013contest/EB9i7u_h3tI/D2xJZr6xb3AJ" target="_blank">this post</a>.
	</p>
</section>
<?php }?>

<section id="main">	        
	<p>
		<strong>To upload a new submission <a href="upload.php">click here</a></strong>
		<!-- <strong>This is embarrassing, but we can't receive submissions at the moment! Please come back later.</a></strong> -->
	</p>
</section>


<?php if(!$has_submissions) {?>
<section id="main">
	<p>
		<center>You don't have any submission.</center>
	</p>
</section>
<?php } else {?>
<section id="main">
	<p>
		Note: when Status field is 'pending', your submissions is either being evaluated or is queued for evaluation.
	</p>
	<p>
		Note: the submission with dark green background is your final submission (so far). See <a href="submission_guidelines.php">submission guidelines</a> for more details.
	</p>
	<p>
		<table class="dashboard">
			<thead>
				<th style="width:180px;">Upload Time</th>
				<th style="width:80px;">Small data</th>
				<th style="width:80px;">Big data</th>
				<th style="width:80px;">New data</th>
				<th style="width:60px;">Status</th>
				<th>Notes</th>
				<th style="width:70px;">File</th>
				<th style="width:70px;">Log</th>
			</thead>
			<tbody>
				<?php for($i = 0; $i < count($rows); $i++) {?>
					<tr>
						<td <?php echo $rows[$i]['style']?> >
							<?php echo $rows[$i]['submission_time']; ?>
						</td>
						<td <?php echo $rows[$i]['style_small_res']?> style="text-align:center;">
							<?php echo $rows[$i]['small_res'] ?>
						</td>
						<td <?php echo $rows[$i]['style_big_res']?> style="text-align:center;">
							<?php echo $rows[$i]['big_res'] ?>
						</td>
						<td <?php echo $rows[$i]['style']?> style="text-align:center;">
							<?php echo $rows[$i]['new_res'] ?>
						</td>
						<td <?php echo $rows[$i]['style']?> style="text-align:center;">
							<?php echo $rows[$i]['status'] ?>
						</td>
						<td <?php echo $rows[$i]['style']?> >
							<?php echo $rows[$i]['notes'] ?>
						</td>
						<td <?php echo $rows[$i]['style']?> style="text-align:center;">
							<a <?php echo sprintf('href="%s"', 'download.php?f=' . urlencode(encrypt_timestamp($rows[$i]['timestamp'], 'lib'))); ?> >
								Download
							</a>
						</td>
						<td <?php echo $rows[$i]['style']?> style="text-align:center;">
							<?php if($rows[$i]['status'] != 'pending') {?>
							<a <?php echo sprintf('href="%s"', 'download.php?f=' . urlencode(encrypt_timestamp($rows[$i]['timestamp'], 'log'))); ?> target="_blank">
								Download
							</a>
							<?php } ?>
						</td>
					</tr>
				<?php }?>
			</tbody>			
		</table>
	</p>
</section>
<?php }?>
	    
<?php include('footer.php'); ?>
