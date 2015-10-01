#  Copyright (C) 2013 by
#  Fuad Jamour <fjamour@gmail.com>
#  All rights reserved.
#  MIT license.

"""
Keeps checking the evaluation machine for results every
SLEEP_TIME minutes.
Results are assumed to be stored in a MySQL database on
the evaluation machine. Results with timestamps larger than
LAST_TIMESTAMP will be retrieved and parsed (if necessary)
to update the submissions table and store a log file the
contestant can download
Note: some knowledge about the table schemas in
web/sigmod2013contest/mysql_tables.sql is hard coded
in some function in the this script
"""

#==============================================================================
# imports
#==============================================================================
import os
import sys
import time
import glob
import MySQLdb
import re
import datetime
#==============================================================================


#==============================================================================
# Globals. No config file, to update them modify them in the script (here)
#==============================================================================
# path of the submissions where the result log file will be stored for
# an evaluated submission
#SUBMISSIONS_PATH = "/home/fuad/public_html/sigmod2013contest/submissions/"
SUBMISSIONS_PATH = "/home/fuad/scratch/submissions/"

# timestamp of the last pulled result
LAST_TIMESTAMP = 0

# period of checking for new results (in minutes)
SLEEP_TIME = 0.5

# details of the evaluation machine (where the result db is hosted also)
DB_EVAL_HOST = "10.126.144.130"
DB_EVAL_USER = "evaluser"
DB_EVAL_PASS = "123"
DB_EVAL_DBNAME = "evaldb"
DB_EVAL_RESTBL = "results"

# details of the machine where the users table and the submissions table are
# (should be the localhost -- same machine)
DB_LOC_HOST   = "localhost"
DB_LOC_USER   = "root"
DB_LOC_PASS   = "123"
DB_LOC_DBNAME = "webdb_local"#"contest_db"
# the variables below are hardcoded (don't change them! unless proper changes are made to the correspnding .sql)
DB_LOC_SUBTBL = "submissions"
DB_LOC_USRTBL = "users"
#==============================================================================


#==============================================================================
# Connects to the database on the evaluation machine and fetches all the results
# with timestamps larger than @timestamp
# Returns a list of results [[team_name, timestamp, err, out, res, notes], ...]
# and the max timestamp.
# if no new results are found and empty list and the same input timestamp are
# returned
# Note: knowledge about the schema of the result table on the evaluation machine
# is hardcoded in this function
#==============================================================================
def pull_new_res(timestamp):
	try:
		print "Connecting to MySQL on evaluation machine..."
		con = MySQLdb.connect(host=DB_EVAL_HOST,
                              user=DB_EVAL_USER,
                              passwd=DB_EVAL_PASS,
                              db=DB_EVAL_DBNAME)
	except MySQLdb.Error, e:
		print "Error -- pull_new_res(...): can't connect to the database!"
		print "         %d: %s" % (e.args[0], e.args[1])
		return (), timestamp
	cur = con.cursor()
	print "Retrieving results from the database..."
	try:
		s = "SELECT * FROM {0} WHERE timestamp > {1} ORDER BY timestamp ASC;".format(DB_EVAL_RESTBL, timestamp)
		cur.execute(s)
		res = cur.fetchall()
		print "Found {0} new results".format(len(res))
		max_timestamp = timestamp
		if(len(res) > 0):
			max_timestamp = res[-1][1] # this is where the max timestamp should be stored [1]
		return res, max_timestamp
	except MySQLdb.Error, e:
		print "Error -- pull_new_res(...): can't retrieve results!"
		print "         %d: %s" % (e.args[0], e.args[1])
	finally:
		cur.close()
#==============================================================================


#==============================================================================
# Update an entry in the submissions table so that the result is visible on
# the dashboard and the leaderboard
#==============================================================================
def db_update_submissions(team_name, timestamp, runtime_small, runtime_big, runtime_new, res_code):
	try:
		print "Connecting to local MySQL..."
		con = MySQLdb.connect(host=DB_LOC_HOST,
                              user=DB_LOC_USER,
                              passwd=DB_LOC_PASS,
                              db=DB_LOC_DBNAME)
		con.autocommit(True)
		cur = con.cursor()
		print "Updating the submissions table..."
		idq = "SELECT team_id FROM {0} WHERE team_name='{1}'".format(DB_LOC_USRTBL, team_name)
		s = "UPDATE {0} SET runtime_small={1}, runtime_big={2}, runtime_new={3}, result_code={4} WHERE team_id=({5}) AND submission_time={6};".format(DB_LOC_SUBTBL, runtime_small, runtime_big, runtime_new, res_code, idq, timestamp)
		cur.execute(s)
	except MySQLdb.Error, e:
		print "Error -- db_update_submissions(...): can't connect the database!"
		print "         %d: %s" % (e.args[0], e.args[1])
	finally:
		cur.close()
#==============================================================================


#==============================================================================
# Takes the result line, parses it, and updates the submissions table together
# with storing the a log file downloadable by the contestant
#
# Note: knowledge about the schema of the result table on the evaluation machine
# is hardcoded in this function
# Note: XXX knowledge about the format of the result file written by the 
# testdriver is encoded in this function
#==============================================================================
def parse_res(res_line):
	team_name = res_line[0]
	timestamp = res_line[1]
	res_str   = res_line[2]
	std_out   = res_line[3]
	std_err   = res_line[4]
	mklib_out = res_line[5]
	
	print "Parsing submission from team [{0}] with timestamp [{1}]...".format(team_name, timestamp)
	# XXX
	# The way result parsing works is as follows:
	# the result file written by the testdriver is checked
	# for the phrase 'passsed all tests' to see if passed everything
	# if passed the runtime is extracted.
	# if didn't pass the res_code will be fail all 
	# it's not possible now to have passed tests and not have passed evrything
	# (no silly tests for error codes and so =P)
	runtime_small  = -1;
	runtime_big    = -1;
	runtime_new    = -1;
	res_code       = 0  # assume didn't pass
	m_small        = re.search('small data.*?passed all tests', res_str, flags=re.DOTALL)
	if(m_small):     # passed small test
		res_code      = 1   # 1 means passed small test
		m1            = re.search('small data.*?Time=(.*?)\[.*?\]', res_str, flags=re.DOTALL)
		runtime_small = float(m1.group(1))/1000
	
	m_big = False  # assume failed big test
	if(res_code != 0):   # if passed small test check for big test
		m_big = re.search('big data.*?passed all tests', res_str, flags=re.DOTALL)		
	if(m_big): # passed big test
		res_code      = 2   # 2 means passed small and big data
		m1            = re.search('big data.*?Time=(.*?)\[.*?\]', res_str, flags=re.DOTALL)
		runtime_big   = float(m1.group(1))/1000
		
	m_new = False  # assume failed new test
	if(res_code != 0):   # if passed big test check for new test
		m_new = re.search('new data.*?passed all tests', res_str, flags=re.DOTALL)
	if(m_new): # passed big test
		res_code      = 3   # 3 means passed everything
		m1            = re.search('new data.*?Time=(.*?)\[.*?\]', res_str, flags=re.DOTALL)
		runtime_new   = float(m1.group(1))/1000
		
		
	print "Time(small): [{0}] --- Time(big): [{1}] --- Time(new): [{2}]".format(runtime_small, runtime_big, runtime_new)
	# update the submissions table
	db_update_submissions(team_name, timestamp, runtime_small, runtime_big, runtime_new, res_code)
	
	# create files with the outputs in them
	try:
		p = SUBMISSIONS_PATH + team_name + "/" + str(timestamp) + "/log.txt"
		print "Writing log file on {0}".format(p)
		f = open(p, "w")
		f.write("==================================================================\n")
		f.write('Test driver output:\n')
		f.write(res_str+'\n')
		f.write("==================================================================\n")
		f.write('Standard output:\n')
		f.write(std_out+'\n')
		f.write("==================================================================\n")
		f.write('Standard error:\n')
		f.write(std_err+'\n')
		f.write("==================================================================\n")
		f.write('Compilation output:\n')
		f.write(mklib_out+'\n')
		f.close()
		os.system("chmod o+r {0}".format(p))
	except Exception, e:
		print "Error -- parse_res(...): can't write the log file!"
		print "         %s" % e
#==============================================================================


#==============================================================================
# Takes a list of results (output from pull_new_res(...)) and updates
# submissions table and submission restuls files
#==============================================================================
def update_submissions(results):
	for res in results:
		print "------------------------------------------------------------------"
		parse_res(res)
#==============================================================================


#==============================================================================
# Keeps looping for new results
#==============================================================================
def start_pulling_res():
	global LAST_TIMESTAMP
	while(True):
		res_list, max_timestamp = pull_new_res(LAST_TIMESTAMP)
		if(len(res_list) > 0):   # there are new results
			LAST_TIMESTAMP = max_timestamp
			os.system("echo {0} > LAST_TIMESTAMP_PULL".format(LAST_TIMESTAMP))
			update_submissions(res_list)
		print "=================================================================="
		print "[{0}] Will check again after {1} minutes.".format(time.ctime(), SLEEP_TIME)
		t = datetime.datetime.fromtimestamp(int(LAST_TIMESTAMP)).strftime('%a %b  %d %H:%M:%S %Y')
		print "LAST_TIMESTAMP: {0} @ [{1}] ".format(LAST_TIMESTAMP, t)
		time.sleep(SLEEP_TIME * 60)
#==============================================================================


#==============================================================================
# Updates the global timestamp.
# if there is a file called LAST_TIMESTAMP_PULL next to the script the timestamp
# in it is used to initialize the global LAST_TIMESTAMP, if not, it's set to
# zero (all results will be fetched from the results table on the evaluation machine)
#==============================================================================
def init_timestamp():
	global LAST_TIMESTAMP
	try:
		LAST_TIMESTAMP = int(open('LAST_TIMESTAMP_PULL').read())
	except:
		LAST_TIMESTAMP = 0
#==============================================================================


#==============================================================================
# Actual things to do
#==============================================================================
init_timestamp()
print "=================================================================="
print "******************************************************************"
print "Welcome to result puller!"
print "The values of the parameters are:"
print "[SUBMISSIONS_PATH]:\t{0}".format(SUBMISSIONS_PATH)
print "[LAST_TIMESTAMP]:\t{0}".format(LAST_TIMESTAMP)
print "[DB_EVAL_HOST]:\t\t{0}".format(DB_EVAL_HOST)
print "[DB_EVAL_USER]:\t\t{0}".format(DB_EVAL_USER)
print "[DB_EVAL_PASS]:\t\t{0}".format(DB_EVAL_PASS)
print "[DB_EVAL_DBNAME]:\t{0}".format(DB_EVAL_DBNAME)
print "[DB_EVAL_RESTBL]:\t{0}".format(DB_EVAL_RESTBL)
print "[DB_LOC_HOST]:\t\t{0}".format(DB_LOC_HOST)
print "[DB_LOC_USER]:\t\t{0}".format(DB_LOC_USER)
print "[DB_LOC_PASS]:\t\t{0}".format(DB_LOC_PASS)
print "[DB_LOC_DBNAME]:\t{0}".format(DB_LOC_DBNAME)
print "[DB_LOC_SUBTBL]:\t{0}".format(DB_LOC_SUBTBL)
print "[DB_LOC_USRTBL]:\t{0}".format(DB_LOC_USRTBL)
print "[SLEEP_TIME]:\t\t{0}".format(SLEEP_TIME)
print "******************************************************************"
print "=================================================================="
start_pulling_res()
