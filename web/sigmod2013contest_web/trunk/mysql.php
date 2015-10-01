<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
?>

<?php include('mysql.config.php'); ?>

<?php //leaderboard query
//XXX NOTE: 1000000 in the query is used elsewhere as $INFINITY (be carefull, if $INFINITY changes, change it here)
$lb_sql = 
"SELECT * FROM (" .
"SELECT   S.team_id, S.submission_time, S.runtime_small, S.runtime_big, S.runtime_new, S.result_code, S.show_leaderboard, U.team_name, U.team_country, U.team_institute, U.show_instit, U.show_country " . 
"FROM     submissions S NATURAL JOIN users U " .
"WHERE    result_code=3 and runtime_new <= all (SELECT runtime_new FROM submissions S2 WHERE S.team_id=S2.team_id and S2.result_code=3) " .
"         and not exists (SELECT * FROM submissions S2 WHERE S.team_id = S2.team_id and S2.result_code=3 and S2.submission_time>S.submission_time and S2.runtime_new=S.runtime_new)" .
"         and show_leaderboard=TRUE" . 
") NEW_RES " .
"UNION " .		       
"SELECT * FROM (" .
"SELECT   S.team_id, S.submission_time, S.runtime_small, S.runtime_big, 1000000 as runtime_new, S.result_code, S.show_leaderboard, U.team_name, U.team_country, U.team_institute, U.show_instit, U.show_country " . 
"FROM     submissions S NATURAL JOIN users U " .
"WHERE    result_code=2 and runtime_big <= all (SELECT runtime_big FROM submissions S2 WHERE S.team_id=S2.team_id and S2.result_code=2) " .
"         and S.team_id NOT IN (SELECT team_id FROM submissions WHERE result_code=3) " .
"         and not exists (SELECT * FROM submissions S2 WHERE S.team_id = S2.team_id and S2.result_code=2 and S2.submission_time>S.submission_time and S2.runtime_big=S.runtime_big)" .
"         and show_leaderboard=TRUE" . 
") BIG_RES " .
"UNION " .
"SELECT * FROM (" .
"SELECT   S.team_id, S.submission_time, S.runtime_small, 1000000 as runtime_big, 1000000 as runtime_new, S.result_code, S.show_leaderboard, U.team_name, U.team_country, U.team_institute, U.show_instit, U.show_country " . 
"FROM     submissions S NATURAL JOIN users U " .
"WHERE    result_code=1 and runtime_small <= all (SELECT runtime_small FROM submissions S2 WHERE S.team_id=S2.team_id and S2.result_code=1) " .
"         and S.team_id NOT IN (SELECT team_id FROM submissions WHERE result_code=2 or result_code=3) " .
"         and not exists (SELECT * FROM submissions S2 WHERE S.team_id = S2.team_id and S2.result_code=1 and S2.submission_time>S.submission_time and S2.runtime_small=S.runtime_small)" .
"         and show_leaderboard=TRUE " . 
") SMALL_RES " .
"ORDER BY runtime_new, runtime_big, runtime_small";
?>

<?php //db related functions and globals
	$db_header_included = true;
	$db_connected = false;
	$db_con;

	function db_connect()
	{
		global $db_con, $db_connected, $db_salt, $db_host, $db_username, $db_password, $db_name;
		if($db_connected) {
			return $db_con;
		} else {
			//TODO implement an exception handler to show a page if db connection goes wrong
			$db_con = mysql_connect("$db_host", "$db_username", "$db_password") or die("Can't connect to DB!");
			mysql_select_db("$db_name") or die("Can't select DB");
			$db_connected = true;
			return $db_con;		
		}
	}

	function db_disconnect()
	{
		$db_connected = false;
		mysql_close($db_con);
	}

	//IMP: table names and attribute names hard coded here
	function db_check_team($team_name, $email, &$name_exists, &$email_exists)
	{
		$name_exists  = false;
		$email_exists = false;
		db_connect();
		$sql = sprintf("SELECT * FROM users WHERE team_name='%s';",
                       mysql_real_escape_string($team_name));
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_check_team)");
		}
		$num_rows = mysql_num_rows($sql_res);
		if($num_rows > 0)
			$name_exists = true;

		$sql = sprintf("SELECT * FROM users WHERE team_email='%s';",
                       mysql_real_escape_string($email));
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_check_team)");
		}
		$num_rows = mysql_num_rows($sql_res);
		if($num_rows > 0)
			$email_exists = true;
	}

	//IMP: table names and attribute names hard coded here
	function db_register_team($team_name,
                              $team_email,
                              $team_instit,
                              $team_country,
                              $team_pass,
                              $hide_instit,
                              $hide_country)
	{
		db_connect();
		$password_hash = hash('sha256',$team_pass.$salt);
		$sql = sprintf("INSERT INTO users (team_name, team_email, team_institute, team_country, pass_hash, show_instit, show_country) VALUES('%s', '%s', '%s', '%s', '%s', %s, %s);",
                       mysql_real_escape_string($team_name),
                       mysql_real_escape_string($team_email),
                       mysql_real_escape_string($team_instit),
                       mysql_real_escape_string($team_country),
                       mysql_real_escape_string($password_hash),
                       ($hide_instit ? 'FALSE' : 'TRUE'),
                       ($hide_country ? 'FALSE' : 'TRUE'));
		if(!mysql_query($sql)) {
			die("Can't register team! (db_register_team)");
		}
	}

	//IMP: table names and attribute names hard coded here
	function db_update_team($team_name,
                            $team_email,
                            $team_instit,
                            $team_country,
                            $team_pass,
                            $hide_instit,
                            $hide_country)
	{
		db_connect();
		//1. update users_log
		//2. update users
		$sql = sprintf("INSERT INTO users_log (SELECT *, UNIX_TIMESTAMP() as change_time FROM users WHERE team_name='%s');",
                       mysql_real_escape_string($team_name));
                       
		if(!mysql_query($sql)) {
			die("Can't update team! (db_update_team1)");
		}
		$password_hash = hash('sha256',$team_pass.$salt);
		$sql = sprintf("UPDATE users SET team_email='%s', team_institute='%s', team_country='%s', pass_hash='%s', show_instit=%s, show_country=%s WHERE team_name='%s';",
                       mysql_real_escape_string($team_email),
                       mysql_real_escape_string($team_instit),
                       mysql_real_escape_string($team_country),
                       mysql_real_escape_string($password_hash),
                       ($hide_instit ? 'FALSE' : 'TRUE'),
                       ($hide_country ? 'FALSE' : 'TRUE'),
                       mysql_real_escape_string($team_name));
		if(!mysql_query($sql)) {
			die("Can't update team! (db_update_team2)");
		}                       
	}

	//IMP: table names and attribute names hard coded here
	function db_check_login($team_name, $team_pass)
	{
		$succeed = false;
		db_connect();
		$password_hash = hash('sha256',$team_pass.$salt);
		$sql = sprintf("SELECT * FROM users WHERE team_name='%s' and pass_hash='%s';",
                       mysql_real_escape_string($team_name),
                       mysql_real_escape_string($password_hash));
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_check_login)");
		}
		$num_rows = mysql_num_rows($sql_res);
		if($num_rows > 0)
			$succeed = true;
		else
			$succeed = false;

		return $succeed;
	}

	//IMP: table names and attribute names hard coded here
	function db_get_user_row($team_name)
	{
		db_connect();
		$sql = sprintf("SELECT * FROM users WHERE team_name='%s';",
                       mysql_real_escape_string($team_name));
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_row)");
		}
		$num_rows = mysql_num_rows($sql_res);
		if($num_rows != 1) {
			die("Fatal error in db_get_row");
		}
		return mysql_fetch_assoc($sql_res);
	}

	//IMP: table names and attribute names hard coded here
	//NOTE: submission_time in submissions table is unique,
	//      this function will return True if the record was inserted,
	//      and False if not
	function db_insert_submission($team_id,
                                  $submission_time,
                                  $notes,
                                  $filename)
	{
		db_connect();
		$sql = sprintf("INSERT INTO submissions (team_id, submission_time, notes, filename) VALUES(%s, %s, '%s', '%s');",
                       mysql_real_escape_string($team_id),
                       mysql_real_escape_string($submission_time),
                       mysql_real_escape_string($notes),
                       mysql_real_escape_string($filename));
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			return False;
		}
		
		return True;
		//if(!$sql_res) {
		//	die("Can't execute db query! (db_insert_submission)");
		//}		
	}

	//IMP: table names and attribute names hard coded here
	function db_get_user_submissions($team_id)
	{
		db_connect();
		$sql = sprintf("SELECT * FROM submissions WHERE team_id=%s and show_dashboard=TRUE ORDER BY submission_time DESC;",
                       mysql_real_escape_string($team_id));
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_user_submissions)");
		}
		$num_rows = mysql_numrows($sql_res);
		$submissions_arr = array();
		for($i = 0; $i < $num_rows; $i++) {
			$submissions_arr[$i] = mysql_fetch_assoc($sql_res);
		}
		
		return $submissions_arr;
	}

	//IMP: table names and attribute names hard coded here
	function db_get_leaderboard_entries()
	{
		global $lb_sql;
		db_connect();
		$sql = $lb_sql;
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_leaderboard_entries)");
		}
		$num_rows = mysql_numrows($sql_res);
		$submissions_arr = array();
		for($i = 0; $i < $num_rows; $i++) {
			$submissions_arr[$i] = mysql_fetch_assoc($sql_res);
		}
		return $submissions_arr;		
	}
	
	// Doesn't really remove the submission from the database, but changes
	// the show_leaderboard and show_dashboard flags
	function db_del_submission($team_id, $timestamp)
	{
		db_connect();
		$sql = sprintf("UPDATE submissions SET show_dashboard=FALSE, show_leaderboard=FALSE WHERE team_id=%s and submission_time=%s;",
                       mysql_real_escape_string($team_id),
                       mysql_real_escape_string($timestamp));
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_del_submission)");
		}				
	}
	
	//IMP: table names and attribute names hard coded here
	function db_get_num_pending_submissions($team_id)
	{
		db_connect();
		$sql = sprintf("SELECT * FROM submissions WHERE team_id='%s' and result_code=-1;",
                       mysql_real_escape_string($team_id));
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_num_pending_submissions)");
		}
		$num_rows = mysql_numrows($sql_res);
		return $num_rows;
	}
	
	//IMP: table names and attribute names hard coded here
	function db_get_min_runtime_new()
	{
		global $lb_sql;
		db_connect();
		$sql = "SELECT min(runtime_new) as MIN_T FROM ( " .
               $lb_sql .
		       ") TT;";					
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_min_runtime_big)");
		}
		$num_rows = mysql_numrows($sql_res);
		if($num_rows == 1) {
			$row = mysql_fetch_assoc($sql_res);
			$t   = floatval($row['MIN_T']);
		} else {
			$t   = 100;
		}
		return $t;
	}
	
	//IMP: table names and attribute names hard coded here
	function db_get_min_runtime_big()
	{
		global $lb_sql;
		db_connect();
		$sql = "SELECT min(runtime_big) as MIN_T FROM ( " .
               $lb_sql .
		       ") TT;";					
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_min_runtime_big)");
		}
		$num_rows = mysql_numrows($sql_res);
		if($num_rows == 1) {
			$row = mysql_fetch_assoc($sql_res);
			$t   = floatval($row['MIN_T']);
		} else {
			$t   = 100;
		}
		return $t;
	}
	
	//IMP: table names and attribute names hard coded here
	function db_get_min_runtime_small()
	{
		global $lb_sql;
		db_connect();
		$sql = "SELECT min(runtime_small) as MIN_T FROM ( " .
               $lb_sql .
		       ") TT;";		
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_min_runtime_small)");
		}
		$num_rows = mysql_numrows($sql_res);
		if($num_rows == 1) {
			$row = mysql_fetch_assoc($sql_res);
			$t   = floatval($row['MIN_T']);
		} else {
			$t   = 100;
		}
		return $t;
	}
	
	//IMP: table names and attribute names hard coded here
	function db_insert_submission_ip($team_id, $timestamp, $ip_addr)
	{
		db_connect();
		$sql = sprintf("INSERT INTO ip_log VALUES(%s, %s, '%s');", $team_id, $timestamp, $ip_addr);
		$sql_res = mysql_query($sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_insert_submission_p)");
		}
	}
?>
