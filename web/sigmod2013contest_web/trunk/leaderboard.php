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

<?php include('country_names_map.php'); ?>

<?php
	$min_runtime_new   = db_get_min_runtime_new();
	$min_runtime_big   = db_get_min_runtime_big();
	$min_runtime_small = db_get_min_runtime_small();

	$leaderboard_entries = db_get_leaderboard_entries();
	$rows = array();
	if(count($leaderboard_entries) != 0) {
		for($i = 0; $i < count($leaderboard_entries); $i++) {
			$rows[$i]['num']             = $i+1;
			$rows[$i]['name']            = $leaderboard_entries[$i]['team_name'];
			$rows[$i]['instit']          = $leaderboard_entries[$i]['team_institute'];
			$rows[$i]['runtime_new']     = $leaderboard_entries[$i]['runtime_new'];
			$rows[$i]['runtime_big']     = $leaderboard_entries[$i]['runtime_big'];
			$rows[$i]['runtime_small']   = $leaderboard_entries[$i]['runtime_small'];
			$rows[$i]['submission_time'] = date('M d - h:ia', $leaderboard_entries[$i]['submission_time']);
			$rows[$i]['show_instit']     = $leaderboard_entries[$i]['show_instit'];
			$rows[$i]['show_country']    = $leaderboard_entries[$i]['show_country'];
			$rows[$i]['result_code']     = $leaderboard_entries[$i]['result_code'];
			
			$team_row                 = db_get_user_row($rows[$i]['name']);
			$country                  = $team_row['team_country'];
			$rows[$i]['flag']         = strtolower($COUNTRY_TO_CODE[$country]);

			//check if the flag gif exists
			$flag_path = sprintf("images/flags/%s.gif", $rows[$i]['flag']);
			if(!file_exists($flag_path))
				$rows[$i]['flag'] = 'empty';
			//check if the contestant asked to hide country	
			if(!$rows[$i]['show_country'])
				$rows[$i]['flag'] = 'empty';

			if($rows[$i]['result_code'] == 3) {        // pass all
				$rows[$i]['bar_width_new']   = round($min_runtime_new*100/$rows[$i]['runtime_new']);
				$rows[$i]['bar_width_big']   = round($min_runtime_big*100/$rows[$i]['runtime_big']);
				$rows[$i]['bar_width_small'] = round($min_runtime_small*100/$rows[$i]['runtime_small']);			
			}
			elseif($rows[$i]['result_code'] == 2) {  // pass small and big
				$rows[$i]['bar_width_new']   = 0;
				$rows[$i]['bar_width_big']   = round($min_runtime_big*100/$rows[$i]['runtime_big']);
				$rows[$i]['bar_width_small'] = round($min_runtime_small*100/$rows[$i]['runtime_small']);
				
				$rows[$i]['runtime_new']     = 'N/A';	
			} else {                                  // pass small only
				$rows[$i]['bar_width_new']   = 0;
				$rows[$i]['bar_width_big']   = 0;
				$rows[$i]['bar_width_small'] = round($min_runtime_small*100/$rows[$i]['runtime_small']);
				
				$rows[$i]['runtime_new']     = 'N/A';
				$rows[$i]['runtime_big']     = 'N/A';
			}

		}
	}
?>

<section id="main">
			<div class="logos">
	       		<a href="http://www.sigmod.org/" target="_blank"><img src="images/logo-acm-sigmod.jpg" alt="ACM SIGMOD" width="225"/></a>       
				<a href="http://www.microsoft.com/" target="_blank"><img src="images/logo-microsoft.jpg" alt="Microsoft" /></a>
				<a href="http://www.csail.mit.edu/" target="_blank"><img src="images/logo-mit.jpg" alt="MIT" /></a>
				<a href="http://www.kaust.edu.sa/" target="_blank"><img src="images/logo-kaust.jpg" alt="KAUST" /></a>
			</div>
			<br>
	<p>
		The leaderboard ranks submissions based on their performance in three tests (in order): 
		New, Big, and Small. If a submission doesn't pass one or more of the bigger tests (Big and New)
		it will appear below the submissions that passed all the tests.
	</p>
	<br>
	<p>
		<table class="leaderboard">
			<thead>
				<th style="width:25px;"></th>
				<th>Team</th>
				<th style="width: 85px;">Small (sec)</th>
				<th style="width: 85px;">Big   (sec)</th>
				<th style="width: 85px;">New   (sec)</th>
				<th style="width: 155px;">Upload Time</th>
			</thead>
			<tbody>
				<?php for($i = 0; $i < count($rows); $i++) {?>
					<tr>
						<td>
							<?php echo $rows[$i]['num']?>
						</td>
						<td>
							<img class="flag" src=<?php echo sprintf("images/flags/%s.gif", $rows[$i]['flag']); ?> />
							<?php echo $rows[$i]['name']; ?>
							<?php if($rows[$i]['show_instit']) echo sprintf(" (%s)", $rows[$i]['instit']); ?>
						</td>
						<td>
							<div class="bar" <?php echo sprintf('style="width: %s%%"', $rows[$i]['bar_width_small']); ?> >
								<span style="padding:5px 10px;">
									<?php echo $rows[$i]['runtime_small'];?>
								</span>
							</div>
						</td>
						<td>
							<div class="bar" <?php echo sprintf('style="width: %s%%"', $rows[$i]['bar_width_big']); ?> >
								<span style="padding:5px 10px;">
									<?php echo $rows[$i]['runtime_big'];?>
								</span>
							</div>
						</td>
						<td>
							<div class="bar" <?php echo sprintf('style="width: %s%%"', $rows[$i]['bar_width_new']); ?> >
								<span style="padding:5px 10px;">
									<?php echo $rows[$i]['runtime_new'];?>
								</span>
							</div>
						</td>
						<td>
							<?php echo $rows[$i]['submission_time']?>
						</td>
					</tr>
				<?php }?>
		</table>
	</p>

</section>
<?php include('footer.php'); ?>
