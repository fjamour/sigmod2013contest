#  Copyright (C) 2013 by
#  Fuad Jamour <fjamour@gmail.com>
#  All rights reserved.
#  MIT license.

"""
Produces backups of all the databases involved
Will run on a third machine (not the web server and not the evaluation server)

Note: this script assumes the machine running it has passwordless access to the
machine involved (do ssh-copy-id before running the script).
"""

#==============================================================================
# imports
#==============================================================================
import os
import sys
import time
import glob
import re
import datetime
import subprocess
from subprocess import Popen, PIPE
#==============================================================================


#==============================================================================
# Globals. No config file, to update them modify them in the script (here)
#==============================================================================
# path where to store the backups (folder should be there beforehand)
BACKUP_PATH = "/home/fuad/scratch/sigmod-backups/"

# path where to store the submissions backup (folder not necessarily there)
SUBMISSIONS_BACKUP_PATH = "/home/fuad/scratch/sigmod-backups/bu_submissions/"

# path of the submissions on the web server
WEB_SUBMISSIONS_PATH = "/var/www/html/sigmod-pilot/submissions/"

# period of checking for new results (in minutes)
SLEEP_TIME = 60

# details of the evaluation machine (where the result db is hosted also)
EVAL_USER = "fuad"
EVAL_HOST = "10.126.144.130"
DB_EVAL_USER = "evaluser"
DB_EVAL_PASS = "123"
DB_EVAL_DBNAME = "evaldb"

# details of the machine where the users table and the submissions table are
WEB_USER = "jamourft"
WEB_HOST = "10.254.33.130"
DB_WEB_USER = "webuser"
DB_WEB_PASS = "123"
DB_WEB_DBNAME = "webdb"
#==============================================================================


#==============================================================================
# Uses 'mysqldump' to get a backup of @db_name on @db_host
# the output sql file will be written to @file_path
#==============================================================================
def get_db_backup_remote_mysqldump(db_host, db_user, db_pass, db_name, file_path, log_path=""):
	try:
		print "------------------------------------------------------------------"
		print "Getting db backup using mysqldump..."
		print "host: [{0}]\ndb_name: [{1}]\nfile_path: [{2}]".format(host, db_name, file_path)
		cmd      = "mysqldump --host={0} --user={1} --password={2} {3} > {4}".format(db_host, db_user, db_pass, db_name, file_path)
		proc     = Popen(cmd, stdout=PIPE, stderr=subprocess.STDOUT, shell=True)
		if(log_path != ""):
			f = open(log_path, "w")
			f.write(proc.communicate()[0] + '\n')
	except Exception, e:
		print "Error in get_db_backup_remote_mysqldump(...): %s" % e
#==============================================================================


#==============================================================================
# Uses ssh to produce the backup on the remote machine and stores the output sql
# at @file_path
#==============================================================================
def get_db_backup_ssh(host, user, db_user, db_pass, db_name, file_path, log_path=""):
	try:
		print "------------------------------------------------------------------"
		print "Getting db backup using ssh..."
		print "host: [{0}]\ndb_name: [{1}]\nfile_path: [{2}]".format(host, db_name, file_path)
		cmd      = "ssh {0}@{1} mysqldump -u{2} -p{3} {4} > {5}".format(user, host, db_user, db_pass, db_name, file_path)
		print cmd
		proc     = Popen(cmd, stdout=PIPE, stderr=subprocess.STDOUT, shell=True)
		if(log_path != ""):
			f = open(log_path, "w")
			f.write(proc.communicate()[0] + '\n')
	except Exception, e:
		print "Error in get_db_backup_ssh(...): %s" % e
#==============================================================================


#==============================================================================
# Uses rsync to get an incremental backup of @remote_folder, to be stored in
# @folder_path
#==============================================================================
def get_folder_backup_rsync(host, user, remote_folder, folder_path, log_path=""):
	try:
		print "------------------------------------------------------------------"
		print "Getting submissions backup using rsync..."	
		print "host: [{0}]\nremote_folder: [{1}]\nfolder_path: [{2}]".format(host, remote_folder, folder_path)
		cmd      = "rsync --recursive --update --verbose {0}@{1}:{2} {3}".format(user, host, remote_folder, folder_path)
		print cmd
		proc     = Popen(cmd, stdout=PIPE, stderr=subprocess.STDOUT, shell=True)
		if(log_path != ""):
			f = open(log_path, "w")
			f.write(proc.communicate()[0] + '\n')
	except Exception, e:
		print "Error in get_folder_backup_ss(...): %s" % e
#==============================================================================


#==============================================================================
# Gets an incremental backup of the submissions folder and of the database on
# the web server
#==============================================================================
def get_web_backup():
	ts             = int(time.time())
	web_db_path    = BACKUP_PATH + str(ts) + "-web_db_bu.sql"
	log_db_path    = BACKUP_PATH + "logs/" + str(ts) + "-web_db_bu.log"
	log_sub_path   = BACKUP_PATH + "logs/" + str(ts) + "-submissions_bu.log"
	get_db_backup_ssh(host      = WEB_HOST,
	                  user      = WEB_USER, 
	                  db_user   = DB_WEB_USER, 
	                  db_pass   = DB_WEB_PASS, 
	                  db_name   = DB_WEB_DBNAME, 
	                  file_path = web_db_path,
	                  log_path  = log_db_path)
	                  
	get_folder_backup_rsync(host          = WEB_HOST,
	                        user          = WEB_USER,
	                        remote_folder = WEB_SUBMISSIONS_PATH,
	                        folder_path   = SUBMISSIONS_BACKUP_PATH,
	                        log_path      = log_sub_path)
#==============================================================================


#==============================================================================
# Gets a backup of the database on the evaluation machine
#==============================================================================
def get_eval_backup():
	ts             = int(time.time())
	eval_db_path   = BACKUP_PATH + str(ts) + "-eval_db_bu.sql"
	log_db_path    = BACKUP_PATH + "logs/" + str(ts) + "-eval_db_bu.log"
	get_db_backup_ssh(host      = EVAL_HOST,
	                  user      = EVAL_USER, 
	                  db_user   = DB_EVAL_USER, 
	                  db_pass   = DB_EVAL_PASS, 
	                  db_name   = DB_EVAL_DBNAME, 
	                  file_path = eval_db_path,
	                  log_path  = log_db_path)
#==============================================================================


#==============================================================================
# Takes a backup of what's on the web server and on the evaluation machine
# every SLEEP_TIME minutes
#
# Web server:   1. database with users and submissions data
#               2. user submissions in the submissions folder
# Eval machine: 1. database of raw results
#==============================================================================
def start_bu():
	while(True):
		print "=================================================================="
		print "Getting backups.."
		get_web_backup()
		#get_eval_backup()
		print "=================================================================="
		print "[{0}] Will take another backup after {1} minutes.".format(time.ctime(), SLEEP_TIME)
		time.sleep(SLEEP_TIME*60)
#==============================================================================

#==============================================================================
# Start running!
#==============================================================================
print "=================================================================="
print "******************************************************************"
print "Welcome to backup script!"
print "The values of the parameters are:"
print "[BACKUP_PATH]:\t\t\t{0}".format(BACKUP_PATH)
print "[SUBMISSIONS_BACKUP_PATH]:\t{0}".format(SUBMISSIONS_BACKUP_PATH)
print "[WEB_SUBMISSIONS_PATH]:\t\t{0}".format(WEB_SUBMISSIONS_PATH)
print "[SLEEP_TIME]:\t\t\t{0}".format(SLEEP_TIME)
print "[EVAL_USER]:\t\t\t{0}".format(EVAL_USER)
print "[EVAL_HOST]:\t\t\t{0}".format(EVAL_HOST)
print "[DB_EVAL_USER]:\t\t\t{0}".format(DB_EVAL_USER)
print "[DB_EVAL_PASS]:\t\t\t{0}".format(DB_EVAL_PASS)
print "[DB_EVAL_DBNAME ]:\t\t{0}".format(DB_EVAL_DBNAME )
print "[WEB_USER]:\t\t\t{0}".format(WEB_USER)
print "[WEB_HOST]:\t\t\t{0}".format(WEB_HOST)
print "[DB_WEB_USER]:\t\t\t{0}".format(DB_WEB_USER)
print "[DB_WEB_PASS]:\t\t\t{0}".format(DB_WEB_PASS)
print "[DB_WEB_DBNAME]:\t\t{0}".format(DB_WEB_DBNAME)
print "******************************************************************"
print "=================================================================="
start_bu()
