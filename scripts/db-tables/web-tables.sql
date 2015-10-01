--  Copyright (C) 2013 by
--  Fuad Jamour <fjamour@gmail.com>
--  All rights reserved.
--  MIT license.

CREATE TABLE users(
	team_id          int PRIMARY KEY AUTO_INCREMENT,
	team_name        VARCHAR(64) UNIQUE,
	team_email       VARCHAR(128) UNIQUE,
	team_institute   VARCHAR(128),
	team_country     VARCHAR(128),
	show_instit      BOOL DEFAULT TRUE,
	show_country     BOOL DEFAULT TRUE,
	pass_hash        VARCHAR(128));


CREATE TABLE submissions(
	team_id             int references users(team_id),
	submission_time     int UNIQUE,
	notes               VARCHAR(256),
	runtime_small       DECIMAL(6,3) DEFAULT -1,
	runtime_big         DECIMAL(6,3) DEFAULT -1,
	runtime_new         DECIMAL(6,3) DEFAULT -1,
	result_code         int DEFAULT -1,
	show_dashboard      BOOL DEFAULT TRUE,
	show_leaderboard    BOOL DEFAULT TRUE,
	filename            VARCHAR(64),
	PRIMARY KEY(team_id, submission_time));
	

CREATE TABLE users_log(
	team_id          int,
	team_name        VARCHAR(64),
	team_email       VARCHAR(128),
	team_institute   VARCHAR(128),
	team_country     VARCHAR(128),
	show_instit      BOOL,
	show_country     BOOL,
	pass_hash        VARCHAR(128),
	change_time      int);
	
CREATE TABLE ip_log(
	team_id          int,
	submission_time  int,
	ip_addr          VARCHAR(128));
	
	
/*
result_code:
0 -- didn't pass anything (not even small tests or unit test)
1 -- didn't pass the big test (but passed the small tests or the unit tests)
2 -- didn't pass the new test (but passed the small and the big tests)
3 -- passed everything
*/
