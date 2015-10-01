#  Copyright (C) 2013 by
#  Fuad Jamour <fjamour@gmail.com>
#  All rights reserved.
#  MIT license.

"""
Keeps checking the folder SUBMISSIONS_PATH for new submissions to be sent
Submissions names whould follow the following format:
team_name-timestamp-filename.[so|tar.gz]
"""

#==============================================================================
# imports
#==============================================================================
import os
import sys
import signal
import time
import glob
import MySQLdb
import subprocess
from subprocess import Popen, PIPE
import datetime
import re
#==============================================================================

#==============================================================================
# Globals. No config file, to update them modify them in the script (here)
#==============================================================================
# path to expect new submissions at
SUBMISSIONS_PATH = "/home/fuad/sigmod-eval/submissions/"

# timestamp of the last evaluated submission
LAST_TIMESTAMP = 0

# period of checking for new results (in minutes)
SLEEP_TIME = 0.5

# details of the raw results database (local)
DB_HOST = "localhost"
DB_USER = "evaluser"
DB_PASS = "123"
DB_DBNAME = "evaldb"
DB_RESTBL = "results"
# max number of characters to get from stdout/stderr
DB_MAX_FILE_SZ = 2048

# directory where the tester package
TESTER_DIR = "/home/fuad/sigmod-eval/eval-script/sigmod2013tester/"
# directory where to do execution
WORK_DIR = "/home/fuad/sigmod-eval/eval-script/work-dir/"

# execution parameters
# max allowed runtime for small data test
MAX_RUNTIME_SMALL = 60
# max allowed runtime for big data test
MAX_RUNTIME_BIG   = 5*60
# max allowed runtime for new data
MAX_RUNTIME_NEW   = 10*60
# max allowed time on small data to run big data                           
MAX_TIME_TO_RUN_BIGTEST = 10
# max allowed time on big data to run new data
MAX_TIME_TO_RUN_NEWTEST = 30
#==============================================================================


#==============================================================================
# Constants
#==============================================================================
T_EXCEEDED = 10000
#==============================================================================


#==============================================================================
# Runs a process for a maximum of @timeout
# if the process finishes, the error code is returned,
# if the time limit is exceeded, T_EXCEEDED is returned
#
# @timeout should be a multiple of @t
#==============================================================================
def wait_timeout(proc, timeout, t):
	tot = 0
	time.sleep(0.5)  # warm up time, not counted as part of the runtime
	while(tot <= timeout):
		ret = proc.poll()
		if(ret is None):
			time.sleep(t)
			tot = tot + t
		else:
			break
	if(ret is None):  # finished max time without returning, kill
		os.killpg(proc.pid, signal.SIGTERM)
		return T_EXCEEDED
	else:
		return ret
#==============================================================================


#==============================================================================
# To be executed by Popen before running the submission
#==============================================================================
def prep_env_os():
	os.setsid()
#==============================================================================


#==============================================================================
# Produces a libcore.so out of the submission at @submission_path and places it
# in @workdir
# submissions can be either a ready libcore.so, in which case only cp is done
# or a impl.tar.gz, in which case the script make-lib.sh is used to compile 
# a libcore.so out of the .tar.gz
#==============================================================================
def make_lib(work_dir, submission_path):
	ret_code  = 1
	mklib_out = ''
	if(submission_path.split('.')[-1] == 'so'):
		cmd      = "cp {0} {1}libcore.so".format(submission_path, WORK_DIR)
		proc     = Popen(cmd, shell=True)
		ret_code = proc.wait()
	elif(submission_path.split('.')[-1] == 'gz'):
		cmd       = "cd {0}compile >/dev/null; set -e; bash make-lib.sh {1} {2}".format(TESTER_DIR, WORK_DIR, submission_path)
		proc      = Popen(cmd, stderr=subprocess.STDOUT, stdout=PIPE, shell=True)
		mklib_out = proc.communicate()[0]
		ret_code  = proc.returncode
	return mklib_out, ret_code
#==============================================================================


#==============================================================================
# Removes everything from @work_dir
#==============================================================================
def clean_work_dir(work_dir):
	cmd      = "rm -Rf {0}*".format(WORK_DIR)
	proc     = Popen(cmd, shell=True)
	ret_code = proc.wait()
#==============================================================================


#==============================================================================
# Copies testdriver and the data(symbolic link) from @tester_dir to @workdir
# XXX contestant might mess with the data.. Make sure you run the script
#     with user who doesn't have any write permissions on teh data..
#==============================================================================
def prepare_work_dir(work_dir, tester_dir):
	cmd      = "cp {0}testdriver {1}".format(tester_dir, work_dir)
	proc     = Popen(cmd, shell=True)
	ret_code = proc.wait()
	cmd      = "ln -s {0}test_data {1}test_data".format(tester_dir, work_dir)
	proc     = Popen(cmd, shell=True)
	ret_code = proc.wait()
#==============================================================================


#==============================================================================
# Runs a submission (assumes that WORK_DIR is fully prepared -- has copy of the data
# and has a library)
#==============================================================================
def run_lib():
	res      = ""
	std_err  = ""
	std_out  = "" 
	c1       = "cd {0}; ".format(WORK_DIR)    # WORK_DIR is where the program testdriver is
	c2       = "ulimit -v 67108864; "         # limit memory usage to 64GB
	c3       = "ulimit -n 50; "               # limit open files, to make sure no disk interaction
	c4       = "ulimit -u 500; "              # limit number of processes to fork
	
	# run small data test
	print "Running small data test..."
	res      = res + "Starting the small data test...\n"
	c5       = "./testdriver 1;"
	cmd      = c1 + c2 + c3 + c4 + c5;
	proc     = Popen(cmd, stdout=PIPE, stderr=PIPE, shell=True, preexec_fn=prep_env_os, executable='/bin/bash')
	ret_code = wait_timeout(proc, MAX_RUNTIME_SMALL, 1)#MAX_RUNTIME_SMALL/10)
	try:
		res = res + open("{0}result.txt".format(WORK_DIR)).read()
		os.sys("rm -f result.txt")
	except:
		pass
	if(ret_code == T_EXCEEDED):  # process was killed because of exceeding time limit
		res = res + "\nProgram was killed because of exceeding the time limit ({0} seconds)!\n".format(MAX_RUNTIME_SMALL)
	std_err = std_err + proc.stderr.read()[0:DB_MAX_FILE_SZ-1]   # just consider the first DB_MAX_FILE_SIZE characters
	std_out = std_out + proc.stdout.read()[0:DB_MAX_FILE_SZ-1]   # just consider the first DB_MAX_FILE_SIZE characters
	#----------------------------------------------------------------
	
	# check small data result	
	m_small       = re.search('passed all tests', res)
	runtime_small = 10000000
	if(m_small):
		m1            = re.search('Time=(.*)\[.*\]', res)
		runtime_small = float(m1.group(1))/1000
		print "Passed small data test with runtime [{0}]".format(runtime_small)
	else:
		print "Failed small data"
	
	passed_small_data = (ret_code == 0 and runtime_small <= MAX_TIME_TO_RUN_BIGTEST)
	#----------------------------------------------------------------
	
	# run big data test
	if(passed_small_data):
		print "Running big data test..."
		res      = res + "\n\nStarting the big data test...\n"
		c5       = "./testdriver 2;"
		cmd      = c1 + c2 + c3 + c4 + c5;
		proc     = Popen(cmd, stdout=PIPE, stderr=PIPE, shell=True, preexec_fn=prep_env_os, executable='/bin/bash')
		ret_code = wait_timeout(proc, MAX_RUNTIME_BIG, 5)#MAX_RUNTIME_BIG/100)
		try:
			res = res + open("{0}result.txt".format(WORK_DIR)).read()
			os.sys("rm -f result.txt")
		except:
			pass
		if(ret_code == T_EXCEEDED):  # process was killed because of exceeding time limit
			res = res + "\nProgram was killed because of exceeding the time limit ({0} seconds)!\n".format(MAX_RUNTIME_BIG)
		std_err = std_err + proc.stderr.read()[0:DB_MAX_FILE_SZ-1]   # just consider the first DB_MAX_FILE_SIZE characters
		std_out = std_out + proc.stdout.read()[0:DB_MAX_FILE_SZ-1]   # just consider the first DB_MAX_FILE_SIZE characters
	#----------------------------------------------------------------	

	# check big data result	
	m_big       = re.search('big data.*passed all tests', res, flags=re.DOTALL)
	runtime_big = 10000000
	if(m_big):
		m1            = re.search('big data.*?Time=(.*?)\[.*?\]', res, flags=re.DOTALL)
		runtime_big = float(m1.group(1))/1000
		print "Passed big data test with runtime [{0}]".format(runtime_big)
	else:
		print "Failed big data"
	
	passed_big_data = (ret_code == 0 and runtime_big <= MAX_TIME_TO_RUN_NEWTEST)
	#----------------------------------------------------------------
	
	# run new data test
	if(passed_big_data):
		print "Running new data test..."
		res      = res + "\n\nStarting the new data test...\n"
		c5       = "./testdriver 3;"
		cmd      = c1 + c2 + c3 + c4 + c5;
		proc     = Popen(cmd, stdout=PIPE, stderr=PIPE, shell=True, preexec_fn=prep_env_os, executable='/bin/bash')
		ret_code = wait_timeout(proc, MAX_RUNTIME_NEW, 10)#MAX_RUNTIME_NEW/100)
		try:
			res = res + open("{0}result.txt".format(WORK_DIR)).read()
			os.sys("rm -f result.txt")
		except:
			pass
		if(ret_code == T_EXCEEDED):  # process was killed because of exceeding time limit
			res = res + "\nProgram was killed because of exceeding the time limit ({0} seconds)!\n".format(MAX_RUNTIME_NEW)
		std_err = std_err + proc.stderr.read()[0:DB_MAX_FILE_SZ-1]   # just consider the first DB_MAX_FILE_SIZE characters
		std_out = std_out + proc.stdout.read()[0:DB_MAX_FILE_SZ-1]   # just consider the first DB_MAX_FILE_SIZE characters
	#----------------------------------------------------------------

	# check new data result	
	m_new       = re.search('new data.*passed all tests', res, flags=re.DOTALL)
	runtime_new = 10000000
	if(m_new):
		m1            = re.search('new data.*?Time=(.*?)\[.*?\]', res, flags=re.DOTALL)
		runtime_new   = float(m1.group(1))/1000
		print "Passed new data test with runtime [{0}]".format(runtime_new)
	else:
		print "Failed new data"
	#----------------------------------------------------------------
	
	# check for cheating
	m_cheat  = re.search('.*FAS INCORRECT RESULTS. =P.*', res, flags=re.DOTALL)
	if(m_cheat):
		res      = "Incorrect results."
		std_err  = ""
		std_out  = ""
	#----------------------------------------------------------------
	return res, std_err, std_out
#==============================================================================


#==============================================================================
# Evaluates the submission at @path and returns the results
# The test driver will be used to evaluate submission and any useful
# output will be returned to be submitted to the contestant
# WORK_DIR should have the binary testdriver in it
#
# A shared library (libcore.so) is expected at @path
# Currently ulimit is used to limit resources, TODO do better!
#==============================================================================
def eval_submission(path):
	print "Evaluating submission at [{0}]".format(path)
	clean_work_dir(WORK_DIR)
	mklib_out, ret_code = make_lib(WORK_DIR, path)
	prepare_work_dir(WORK_DIR, TESTER_DIR)
	res      = ""
	std_err  = ""
	std_out  = "" 
	if(ret_code == 0):   # managed to make the submission .so
		res, std_err, std_out = run_lib()
	else:
		print "Warning -- eval_submission(...): Cannot run the submission!"
	return res, std_err, std_out, mklib_out
#==============================================================================


#==============================================================================
# Returns the first submission with timestamp after @timestamp
# the return value is the name of the file
# File names are expected to have the following format:
# team_name-timestamp-filename.[so|tar.gz]
# where none of the three substrings has a dash or spaces in it
#
# If there are no submissions with timestamp after @timestamp
# an empty list is returned
#==============================================================================
def get_next_submission(timestamp):
	submissions = [x.split('/')[-1] for x in glob.glob(SUBMISSIONS_PATH + '*')]
	submissions.sort(key=lambda sub: int(sub.split('-')[1]))
	next_sub = []
	# if anything enexpected happen (should not)
	# just return empty list, the caller should know
	# there's nothing new
	try:
		next_sub = next(sub for sub in submissions if int(sub.split('-')[1]) > timestamp)
	except: 
		pass
	return next_sub
#==============================================================================


#==============================================================================
# Inserts result record for a submission into the raw results database
#==============================================================================
def update_db(team_name, timestamp, res, err, out, mklib_out):
	print "Connecting to MySQL..."
	con = MySQLdb.connect(host=DB_HOST,
	                     user=DB_USER,
	                     passwd=DB_PASS,
	                     db=DB_DBNAME)
	cur = con.cursor()
	print "Inserting result record..."
	try:
		s = "SELECT * FROM {0} WHERE team_name=%s and timestamp=%s".format(DB_RESTBL)
		cur.execute(s, (team_name, int(timestamp)))
		f = cur.fetchall()
		if(f and len(f)>0):  # the submission was evaluated before
			s = "UPDATE {0} SET res_str=%s, std_out=%s, std_err=%s, mklib_out=%s WHERE team_name=%s and timestamp=%s".format(DB_RESTBL)
			cur.execute(s, (res, out, err, mklib_out, team_name, int(timestamp)))
		else:
			s = "INSERT INTO {0} values(%s, %s, %s, %s, %s, %s)".format(DB_RESTBL)
			cur.execute(s, (team_name, int(timestamp), res, out, err, mklib_out))
	except MySQLdb.Error, e:
		print "Error -- update_db(...): Can't insert record into the local database!"
		print "         %d: %s" % (e.args[0], e.args[1])
	finally:
		con.commit()
		cur.close()
#==============================================================================


#==============================================================================
# Sends the next submission that was not sent to the evaluation machine
# works by checking the submissions folder for submissions with a timestamp
# later bigger than the global LAST_TIMESTAMP
# returns True if found submission else returns False
#==============================================================================
def eval_next_submission():
	global LAST_TIMESTAMP
	print "------------------------------------------------------------------"
	print "Looking for a new submission..."
	next_sub = get_next_submission(LAST_TIMESTAMP)
	if(len(next_sub) == 0): # no new submissions
		return False
	else:                   # a submission has been found and should be sent
		team_name = next_sub.split('-')[0]
		timestamp = next_sub.split('-')[1]
		LAST_TIMESTAMP = int(timestamp)
		os.system("echo {0} > LAST_TIMESTAMP_EVAL".format(LAST_TIMESTAMP))
		print "New submission -- team: {0}, timestamp: {1}".format(team_name, timestamp)
		res, err, out, mklib_out = eval_submission(SUBMISSIONS_PATH + next_sub)
		update_db(team_name, timestamp, res, err, out, mklib_out)
		#TODO append the result to a log also, to make sure nothing is lost 
		return True
#==============================================================================


#==============================================================================
# Keeps checking for new submissions
#==============================================================================
def start_eval():
	while(True):
		while(eval_next_submission()):
			pass
		print "=================================================================="
		print "[{0}] Will check again after {1} minutes.".format(time.ctime(), SLEEP_TIME)
		t = datetime.datetime.fromtimestamp(int(LAST_TIMESTAMP)).strftime('%a %b  %d %H:%M:%S %Y')
		print "LAST_TIMESTAMP: {0} @ [{1}] ".format(LAST_TIMESTAMP, t)
		time.sleep(SLEEP_TIME * 60)
#==============================================================================


#==============================================================================
# Updates the global timestamp.
# if there is a file called LAST_TIMESTAMP_EVAL next to the script the timestamp
# in it is used to initialize the global LAST_TIMESTAMP, if not, it's set to
# zero (all submissions in the submissions folder will be evaluated)
#==============================================================================
def init_timestamp():
	global LAST_TIMESTAMP
	try:
		LAST_TIMESTAMP = int(open('LAST_TIMESTAMP_EVAL').read())
	except:
		LAST_TIMESTAMP = 0
#==============================================================================


#==============================================================================
# Actual things to do
#==============================================================================
init_timestamp()
print "=================================================================="
print "******************************************************************"
print "Welcome to submission evaluator!"
print "The values of the parameters are:"
print "[SUBMISSIONS_PATH]:\t\t{0}".format(SUBMISSIONS_PATH)
print "[LAST_TIMESTAMP]:\t\t{0}".format(LAST_TIMESTAMP)
print "[SLEEP_TIME]:\t\t\t{0}".format(SLEEP_TIME)
print "[WORK_DIR]:\t\t\t{0}".format(WORK_DIR)
print "[DB_HOST]:\t\t\t{0}".format(DB_HOST)
print "[DB_USER]:\t\t\t{0}".format(DB_USER)
print "[DB_HOST]:\t\t\t{0}".format(DB_HOST)
print "[DB_PASS]:\t\t\t{0}".format(DB_PASS)
print "[DB_DBNAME]:\t\t\t{0}".format(DB_DBNAME)
print "[DB_RESTBL]:\t\t\t{0}".format(DB_RESTBL)
print "[MAX_RUNTIME_SMALL]:\t\t{0}".format(MAX_RUNTIME_SMALL)
print "[MAX_RUNTIME_BIG]:\t\t{0}".format(MAX_RUNTIME_BIG)
print "[MAX_RUNTIME_NEW]:\t\t{0}".format(MAX_RUNTIME_NEW)
print "[MAX_TIME_TO_RUN_BIGTEST]:\t{0}".format(MAX_TIME_TO_RUN_BIGTEST)
print "[MAX_TIME_TO_RUN_NEWTEST]:\t{0}".format(MAX_TIME_TO_RUN_NEWTEST)
print "******************************************************************"
print "=================================================================="
start_eval()


