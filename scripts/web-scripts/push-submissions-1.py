#  Copyright (C) 2013 by
#  Fuad Jamour <fjamour@gmail.com>
#  All rights reserved.
#  MIT license.

"""
Keeps checking for new submissions in the folder SUBMISSIONS_PATH.
When a new submission is found (submission with timestamp > LAST_TIMESTAMP)
it is pushed to the evaluation machine at EVAL_MACHINE_IP to path DEST_PATH.
It is assumed that the evaluation machine can be accessed from the machine
running this script through ssh without user credentials (see README). 
This script just copies the submissions (almost) blindly without updating the
submission table or anything.
"""


#==============================================================================
# imports
#==============================================================================
import os
import sys
import time
from subprocess import Popen, PIPE
import glob
import datetime
#==============================================================================


#==============================================================================
# Globals. No config file, to update them modify them in the script (here)
#==============================================================================
# local path where submissions will be stored by the web server
#SUBMISSIONS_PATH = "/home/fuad/public_html/sigmod2013contest/submissions/"
SUBMISSIONS_PATH = "/home/fuad/scratch/submissions/"


# timestamp of the last pushed submission
LAST_TIMESTAMP = 0

# details of the evaluation machine (where the submissions will be sent)
# Note: it's assumed that the evaluation machine has the public key in
#       USERHOME/.ssh/authorized_keys so that a password is not required
#       to connect
EVAL_MACHINE_IP = "10.126.144.130"
EVAL_MACHINE_USER = "fuad"
DEST_PATH = "/home/fuad/sigmod-eval/submissions/"

# period of checking for new submissions (in minutes)
SLEEP_TIME = 0.5

# allowed name and extension for the submission to be pushed
SUBMISSION_NAME = set(["libcore", "impl"])
VALID_EXT       = set(["so", "gz"])
#==============================================================================


#==============================================================================
# Send the file at @file_path to DEST_PATH (a remote copy)
#==============================================================================
def send_file(file_path, dest_fname=''):
	dest_path = DEST_PATH
	if(len(dest_fname) > 0): # if dest_fname is specified
		dest_path = DEST_PATH + dest_fname
	cmd      = "scp {0} {1}@{2}:{3}".format(file_path,
                                                 EVAL_MACHINE_USER,
                                                 EVAL_MACHINE_IP,
                                                 dest_path)
	proc     = Popen(cmd, stdout=PIPE, stderr=PIPE, shell=True)
	ret_code = proc.wait() 
	if(ret_code != 0):
		print "Error -- send_file(...): command [{0}] returned {1}".format(cmd, ret_code)
		print "      -- stdout: {0}".format(proc.stdout.read())
		print "      -- stderr: {0}".format(proc.stderr.read())
	return ret_code
#==============================================================================


#==============================================================================
# Returns the first submission with timestamp after @timestamp
# the return value is a list of two elements:
# [team-name, timestamp]
# 
# if there are no submissions with timestamp after @timestamp
# an empty list is returned
#==============================================================================
def get_next_submission(timestamp):
	# get team_name, submission_folder from the submissions folder
	submissions = [x.split('/')[-2:] for x in glob.glob(SUBMISSIONS_PATH + '*/*')]
	# element at [1] is the submission folder name (which is the timestamp)
	# sort by timestamp to find new submissions
	submissions.sort(key=lambda sub: int(sub[1]))
	next_sub = []
	# if anything enexpected happen (should not)
	# just return empty list, the caller should know
	# there's nothing new
	# Example: having a team with no submissions, just do nothing!
	try:
		next_sub = next(sub for sub in submissions if int(sub[1]) > timestamp)
	except: 
		pass
	return next_sub
#==============================================================================


#==============================================================================
# Returns the the name of a submission file from a folder
# submission name should be SUBMISSION_NAME with extention in VALID_EXT
# A valid file name is SUBMISSIONS_NAME.ext, ext in VALID_EXT set
# will return the file name if there a valid file in the folder
# will return an empty string if there is no valid file/there are many files
# Because it's assumed that if a submission folder is existent then it should
# have content, this function doesn't do much checking
# What if there are both libcore.so and libcore.tar.gz? Shouldn't happen at all!
#==============================================================================
def get_submission_name(path):
	files       = [x.split('/')[-1:] for x in glob.glob(path + '*')]
	valid_files = filter(lambda x: x[0].split('.')[-1] in VALID_EXT and x[0].split('.')[0] in SUBMISSION_NAME , files) 
	if(len(valid_files) == 0):
		print "Error -- get_submission_name(...): there is no valid files in the submission folder."
		print "         folder has: {0}".format(", ".join([x[0] for x in files]))
		return ''
	else:
		return valid_files[0][0]
#==============================================================================
	
	
#==============================================================================
# Sends the next submission that was not sent to the evaluation machine
# works by checking the submissions folder for submissions with a timestamp
# bigger than the global LAST_TIMESTAMP
# returns True if found submission else returns False
#==============================================================================
def send_next_submission():
	global LAST_TIMESTAMP
	print "------------------------------------------------------------------"
	print "Looking for a new submission..."
	next_sub = get_next_submission(LAST_TIMESTAMP)
	if(len(next_sub) == 0): # no new submissions
		print "No new submissions"
		return False
	else:                   # a submission has been found and should be sent
		LAST_TIMESTAMP = int(next_sub[1])
		os.system("echo {0} > LAST_TIMESTAMP_PUSH".format(LAST_TIMESTAMP))
		print "New submission -- team: {0}, timestamp: {1}".format(next_sub[0], next_sub[1])
		print "Checking validity of the submitted file..."
		src_path = SUBMISSIONS_PATH + "/".join(next_sub) + "/"
		fname    = get_submission_name(src_path)
		if(len(fname) == 0):
			print "No valid files! Will not send it to the evaluation machine."
			# TODO make a big deal.. like notify by email or something
		else:			
			dst_path = "-".join(next_sub) + "-" + fname
			src_path = src_path + fname
			print "Sending new submission..."
			print "Source path: {0}".format(src_path)
			print "Dest path: {0}".format(dst_path)
			ret_code = send_file(src_path, dst_path)
			if(ret_code != 0): # SERIOUS ERROR, couldn't send file to evaluation server
				print "Submission was not sent."
				# TODO make a big deal.. like notify by email or something
			else:
				print "Submission was sent successfully!"
		return True
#==============================================================================


#==============================================================================
# keeps checking for new submissions and sends them to the evluation machine
# checks every SLEEP_TIME minutes
#==============================================================================
def start_sending():
	while(True):
		while(send_next_submission()):
			pass
		print "=================================================================="
		print "[{0}] Will check again after {1} minutes.".format(time.ctime(), SLEEP_TIME)
		t = datetime.datetime.fromtimestamp(int(LAST_TIMESTAMP)).strftime('%a %b  %d %H:%M:%S %Y')
		print "LAST_TIMESTAMP: {0} @ [{1}] ".format(LAST_TIMESTAMP, t)
		time.sleep(SLEEP_TIME*60)
#==============================================================================


#==============================================================================
# Updates the global timestamp.
# if there is a file called LAST_TIMESTAMP_PUSH next to the script the timestamp
# in it is used to initialize the global LAST_TIMESTAMP, if not, it's set to
# zero (all submissions will be pushed to the evaluation machine)
#==============================================================================
def init_timestamp():
	global LAST_TIMESTAMP
	try:
		LAST_TIMESTAMP = int(open('LAST_TIMESTAMP_PUSH').read())
	except:
		LAST_TIMESTAMP = 0
#==============================================================================


#==============================================================================
# Actual things to do
#==============================================================================
init_timestamp()
print "=================================================================="
print "******************************************************************"
print "Welcome to submission pusher!"
print "The values of the parameters are:"
print "[SUBMISSIONS_PATH]:\t{0}".format(SUBMISSIONS_PATH)
print "[LAST_TIMESTAMP]:\t{0}".format(LAST_TIMESTAMP)
print "[EVAL_MACHINE_IP]:\t{0}".format(EVAL_MACHINE_IP)
print "[EVAL_MACHINE_USER]:\t{0}".format(EVAL_MACHINE_USER)
print "[DEST_PATH]:\t\t{0}".format(DEST_PATH)
print "[SUBMISSION_NAME]:\t{0}".format(SUBMISSION_NAME)
print "[VALID_EXT]:\t\t{0}".format(VALID_EXT)
print "[SLEEP_TIME]:\t\t{0}".format(SLEEP_TIME)
print "******************************************************************"
print "=================================================================="
start_sending()
