#  Copyright (C) 2013 by
#  Fuad Jamour <fjamour@gmail.com>
#  All rights reserved.
#  MIT license.

set -e

#===========================================================
# Assumption in this script
#===========================================================
# Make sure all required packages are installed on evaluation machine
#      --> sudo apt-get install python-mysqldb
# Make sure the public key is copied (ssh-copy-id)
# --> (1) from my machine
# --> (2) from the web server
# Make sure the 'bind-address = 127.0.0.1' in /etc/mysql/my.cnf is commented
#===========================================================


#===========================================================
# Call the parameters script to load the parameters
#===========================================================
. ./init_param.sh
#===========================================================


#===========================================================
# Create the folder hierarchy
#===========================================================
ssh $EVAL_USER@$EVAL_IP "rm -Rf $EVAL_FOLDER &&\\
                         mkdir $EVAL_FOLDER &&\\
                         mkdir $EVAL_SUBMISSIONS_PATH &&\\
                         mkdir $EVAL_SCRIPT_PATH_R"
#===========================================================


#===========================================================
# Create database, grant permissions, and create tables
# assume root login to MySQL doesn't require password
#===========================================================
ssh $EVAL_USER@$EVAL_IP "mysql -u root -e 'DROP DATABASE IF EXISTS $EVAL_DBNAME' &&\\
                         mysql -u root -e 'CREATE DATABASE $EVAL_DBNAME' &&\\
                         mysql -u root -e 'GRANT ALL ON $EVAL_DBNAME.* TO $EVAL_DBUSER@localhost IDENTIFIED BY \"$EVAL_DBPASS\"' &&\\
                         mysql -u root -e 'GRANT ALL ON $EVAL_DBNAME.* TO $EVAL_DBUSER@$WEB_IP IDENTIFIED BY \"$EVAL_DBPASS\"' &&\\
                         mysql -u root -e 'GRANT ALL ON $EVAL_DBNAME.* TO $EVAL_DBUSER@$LOC_IP IDENTIFIED BY \"$EVAL_DBPASS\"'"

rm -f tmp_tbls.sql
sed "s/_RESULTS_TBL_NAME/$EVAL_RES_TBL_NAME/" $EVAL_TABLES_SQL_PATH | sed "s/_RES_STR_SZ/$EVAL_STR_SIZE/g" > tmp_tbls.sql
ssh $EVAL_USER@$EVAL_IP "mysql -u root $EVAL_DBNAME" < tmp_tbls.sql
rm -f tmp_tbls.sql
#===========================================================


#===========================================================
# Prepare and send the evaluation package (script, work-dir, and compilation package)
#===========================================================
rm -Rf tmp_eval_dir
mkdir tmp_eval_dir
cp -R $EVAL_SCRIPT_PATH/* tmp_eval_dir
find tmp_eval_dir/ -type d -name '.svn' | xargs rm -Rf
rm -f tmp_eval_dir/*.sql tmp_eval_dir/*~
# change default parameters in the evaluation script (variables' values taken from init_param.sh)
T_PATH=$(echo $EVAL_SUBMISSIONS_PATH | sed 's/\//\\\//g')
sed -i "s/SUBMISSIONS_PATH *=.*/SUBMISSIONS_PATH = \"$T_PATH\/\"/" tmp_eval_dir/eval-submissions.py
sed -i "s/SLEEP_TIME *=.*/SLEEP_TIME = $EVAL_SLEEP_TIME/" tmp_eval_dir/eval-submissions.py
sed -i "s/DB_HOST *=.*/DB_HOST = \"localhost\"/" tmp_eval_dir/eval-submissions.py
sed -i "s/DB_USER *=.*/DB_USER = \"$EVAL_DBUSER\"/" tmp_eval_dir/eval-submissions.py
sed -i "s/DB_PASS *=.*/DB_PASS = \"$EVAL_DBPASS\"/" tmp_eval_dir/eval-submissions.py
sed -i "s/DB_DBNAME *=.*/DB_DBNAME = \"$EVAL_DBNAME\"/" tmp_eval_dir/eval-submissions.py
sed -i "s/DB_RESTBL *=.*/DB_RESTBL = \"$EVAL_RES_TBL_NAME\"/" tmp_eval_dir/eval-submissions.py
sed -i "s/DB_MAX_FILE_SZ *=.*/DB_MAX_FILE_SZ = $EVAL_STR_SIZE/" tmp_eval_dir/eval-submissions.py
sed -i "s/MAX_RUNTIME_SMALL *=.*/MAX_RUNTIME_SMALL = $EVAL_MAX_RUNTIME_SMALL/" tmp_eval_dir/eval-submissions.py
sed -i "s/MAX_RUNTIME_BIG *=.*/MAX_RUNTIME_BIG = $EVAL_MAX_RUNTIME_BIG/" tmp_eval_dir/eval-submissions.py
sed -i "s/MAX_RUNTIME_NEW *=.*/MAX_RUNTIME_NEW = $EVAL_MAX_RUNTIME_NEW/" tmp_eval_dir/eval-submissions.py
sed -i "s/MAX_TIME_TO_RUN_BIGTEST *=.*/MAX_TIME_TO_RUN_BIGTEST = $MAX_TIME_TO_RUN_BIGTEST/" tmp_eval_dir/eval-submissions.py
sed -i "s/MAX_TIME_TO_RUN_NEWTEST *=.*/MAX_TIME_TO_RUN_NEWTEST = $MAX_TIME_TO_RUN_NEWTEST/" tmp_eval_dir/eval-submissions.py
T_PATH=$(echo $EVAL_TESTER_PATH | sed 's/\//\\\//g')
sed -i "s/TESTER_DIR *=.*/TESTER_DIR = \"$T_PATH\/\"/" tmp_eval_dir/eval-submissions.py
T_PATH=$(echo $EVAL_WORKDIR_PATH | sed 's/\//\\\//g')
sed -i "s/WORK_DIR *=.*/WORK_DIR = \"$T_PATH\/\"/" tmp_eval_dir/eval-submissions.py
scp -r tmp_eval_dir/* $EVAL_USER@$EVAL_IP:$EVAL_SCRIPT_PATH_R
ssh $EVAL_USER@$EVAL_IP "chmod o+w $EVAL_WORKDIR_PATH"
ssh $EVAL_USER@$EVAL_IP "chmod o+w $EVAL_SCRIPT_PATH_R"
rm -Rf tmp_eval_dir
ssh $EVAL_USER@$EVAL_IP "cd $EVAL_TESTER_PATH && make"
#===========================================================


#===========================================================
# Start the evaluation script
#===========================================================
echo "Now go to the evaluation machine and run:"
echo "cd $EVAL_SCRIPT_PATH_R; sudo -u $EVAL_USER python -u eval-submissions.py &> $EVAL_LOG_STREAM &"
#echo ssh $EVAL_USER@$EVAL_IP "cd $EVAL_SCRIPT_PATH_R; sudo -u eval-user python -u eval-submissions.py > $EVAL_LOG_STREAM &"
#===========================================================









