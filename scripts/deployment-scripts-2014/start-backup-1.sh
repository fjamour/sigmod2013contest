#  Copyright (C) 2013 by
#  Fuad Jamour <fjamour@gmail.com>
#  All rights reserved.
#  MIT license.

set -e

#===========================================================
# Call the parameters script to load the parameters
#===========================================================
. ./init_param.sh
#===========================================================


#===========================================================
# Create the backup directory
#===========================================================
rm -Rf $BACKUP_DIR
mkdir $BACKUP_DIR
mkdir $BACKUP_DIR/logs
#===========================================================


#===========================================================
# Update the parameters in the backup script (in place)
#===========================================================
# change default parameters in backup-all-1.py (values taken from init_param.sh)
T_PATH=$(echo $BACKUP_DIR | sed 's/\//\\\//g')
sed -i "s/BACKUP_PATH *=.*/BACKUP_PATH = \"$T_PATH\/\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/SUBMISSIONS_BACKUP_PATH *=.*/SUBMISSIONS_BACKUP_PATH = \"$T_PATH\/bu_submissions\/\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
T_PATH=$(echo $WEB_SUBMISSIONS_PATH | sed 's/\//\\\//g')
sed -i "s/WEB_SUBMISSIONS_PATH *=.*/WEB_SUBMISSIONS_PATH = \"$T_PATH\/\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/SLEEP_TIME *=.*/SLEEP_TIME = $BACKUP_SLEEP_TIME/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/EVAL_USER *=.*/EVAL_USER = \"$EVAL_USER\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/EVAL_HOST *=.*/EVAL_HOST = \"$EVAL_IP\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/DB_EVAL_USER *=.*/DB_EVAL_USER = \"$EVAL_DBUSER\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/DB_EVAL_PASS *=.*/DB_EVAL_PASS = \"$EVAL_DBPASS\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/DB_EVAL_DBNAME *=.*/DB_EVAL_DBNAME = \"$EVAL_DBNAME\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/WEB_USER *=.*/WEB_USER = \"$WEB_USER\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/WEB_HOST *=.*/WEB_HOST = \"$WEB_IP\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/DB_WEB_USER *=.*/DB_WEB_USER = \"$WEB_DBUSER\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/DB_WEB_PASS *=.*/DB_WEB_PASS = \"$WEB_DBPASS\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
sed -i "s/DB_WEB_DBNAME *=.*/DB_WEB_DBNAME = \"$WEB_DBNAME\"/" $BACKUP_SCRIPT_PATH/backup-all-1.py
#===========================================================


#===========================================================
# Start the script
#===========================================================

#===========================================================









