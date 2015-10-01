<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
?>

<?php session_start(); ?>

<?php
	//TODO caching stuff? Should disable caching
?>

<?php 
	$head_li_arr = array('index'=>'<li>',
                         'task-details'=>'<li>',
                         'dashboard'=>'<li>',
                         'leaderboard'=>'<li>',
                         'finalists'=>'<li>',
                         'forum'=>'<li>');

	$current_file = basename($_SERVER['PHP_SELF'], ".php");
	
	if($current_file == 'index')			$head_li_arr['index'] 			= '<li class="active">';
	if($current_file == 'task-details')		$head_li_arr['task-details']	= '<li class="active">';
	if($current_file == 'dashboard')		$head_li_arr['dashboard']		= '<li class="active">';
	if($current_file == 'leaderboard')		$head_li_arr['leaderboard']		= '<li class="active">';
	if($current_file == 'finalists')		$head_li_arr['finalists']		= '<li class="active">';

	//logout link stuff:
	$logout_head_entry = '';
	if($_SESSION['auth'] == 1) {
		$logout_head_entry = sprintf('<li><a href="logout.php" title="Logout">Logout(<span style="color: #dd6911;">%s</span>)</a></li>', $_SESSION['team_name']);
	}
?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>ACM SIGMOD 2013 Programming Contest</title>
		<link href="images/favicon.gif" rel="shortcut icon" type="image/x-icon" />
		<link rel="stylesheet" media="all" href="css/style.css" />
	</head>

	<body> 
		<header>
			<h2 class="logo"><!--King Abdullah University of Science and Technology--></h2>	        
			<h1>ACM SIGMOD 2013 Programming Contest</h1>
			<nav>
				<ul>
					<?php echo $head_li_arr['index'] ?>         <a href="index.php" title="Home">Home</a></li>
					<?php echo $head_li_arr['task-details'] ?>  <a href="task-details.php" title="Task Details">Task Details</a></li>
					<?php echo $head_li_arr['dashboard'] ?>     <a href="dashboard.php" title="Dashboard">Dashboard</a></li>
					<?php echo $head_li_arr['leaderboard'] ?>   <a href="leaderboard.php" title="Leaderboard">Leaderboard</a></li>
					<?php echo $head_li_arr['finalists'] ?>	    <a href="finalists.php" title="Finalists">Finalists</a></li>
					<?php echo $head_li_arr['forum'] ?>	        <a href="https://groups.google.com/forum/?fromgroups#!forum/sigmod2013contest" title="Forum" target="_blank">Forum</a></li>
					<?php echo $logout_head_entry ?>
				</ul>
			</nav>
		</header>
