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
#      --> sudo apt-get install php5-mysql
# Make sure the public key is copied (ssh-copy-id)
#===========================================================


#===========================================================
# Call the parameters script to load the parameters
#===========================================================
. ./init_param.sh
#===========================================================


#===========================================================
# Create the folder hierarchy for scripts (push and pull)
#===========================================================
ssh $WEB_USER@$WEB_IP "rm -Rf $WEB_FOLDER &&\\
                       mkdir $WEB_FOLDER &&\\
                       mkdir $WEB_SCRIPTS_PATH_R"
#===========================================================


#===========================================================
# Create database, grant permissions, and create tables
#===========================================================
ssh $WEB_USER@$WEB_IP "mysql -u root -e 'DROP DATABASE IF EXISTS $WEB_DBNAME' &&\\
                       mysql -u root -e 'CREATE DATABASE $WEB_DBNAME' &&\\
                       mysql -u root -e 'GRANT ALL ON $WEB_DBNAME.* TO $WEB_DBUSER@localhost IDENTIFIED BY \"$WEB_DBPASS\"' &&\\
                       mysql -u root -e 'GRANT ALL ON $WEB_DBNAME.* TO $WEB_DBUSER@$LOC_IP IDENTIFIED BY \"$WEB_DBPASS\"'"
                       
ssh $WEB_USER@$WEB_IP "sudo sed -i "s/bind-address/#bind-address/" /etc/mysql/my.cnf"
ssh $WEB_USER@$WEB_IP "sudo service mysql restart"

ssh $WEB_USER@$WEB_IP "mysql -u root $WEB_DBNAME" < $WEB_TABLES_SQL_PATH
#===========================================================


#===========================================================
# Prepare and send the scripts (push and pull scripts)
#===========================================================
rm -Rf tmp_web_dir
mkdir tmp_web_dir
cp -R $WEB_SCRIPTS_PATH/*.py tmp_web_dir
find tmp_web_dir/ -type d -name '.svn' | xargs rm -Rf
# change default paramteres in push-submissions-1.py (variables' values taken from init_param.sh)
T_PATH=$(echo $WEB_SUBMISSIONS_PATH | sed 's/\//\\\//g')
sed -i "s/SUBMISSIONS_PATH *=.*/SUBMISSIONS_PATH = \"$T_PATH\/\"/" tmp_web_dir/push-submissions-1.py
sed -i "s/SLEEP_TIME *=.*/SLEEP_TIME = $WEB_PUSH_SLEEP_TIME/" tmp_web_dir/push-submissions-1.py
sed -i "s/EVAL_MACHINE_IP *=.*/EVAL_MACHINE_IP = \"$EVAL_IP\"/" tmp_web_dir/push-submissions-1.py
sed -i "s/EVAL_MACHINE_USER *=.*/EVAL_MACHINE_USER = \"$EVAL_USER\"/" tmp_web_dir/push-submissions-1.py
T_PATH=$(echo $EVAL_SUBMISSIONS_PATH | sed 's/\//\\\//g')
sed -i "s/DEST_PATH *=.*/DEST_PATH = \"$T_PATH\/\"/" tmp_web_dir/push-submissions-1.py

# change default paramteres in pull-results-1.py (variables' values taken from init_param.sh)
T_PATH=$(echo $WEB_SUBMISSIONS_PATH | sed 's/\//\\\//g')
sed -i "s/SUBMISSIONS_PATH *=.*/SUBMISSIONS_PATH = \"$T_PATH\/\"/" tmp_web_dir/pull-results-1.py
sed -i "s/SLEEP_TIME *=.*/SLEEP_TIME = $WEB_PULL_SLEEP_TIME/" tmp_web_dir/pull-results-1.py
sed -i "s/DB_EVAL_HOST *=.*/DB_EVAL_HOST = \"$EVAL_IP\"/" tmp_web_dir/pull-results-1.py
sed -i "s/DB_EVAL_USER *=.*/DB_EVAL_USER = \"$EVAL_DBUSER\"/" tmp_web_dir/pull-results-1.py
sed -i "s/DB_EVAL_PASS *=.*/DB_EVAL_PASS = \"$EVAL_DBPASS\"/" tmp_web_dir/pull-results-1.py
sed -i "s/DB_EVAL_DBNAME *=.*/DB_EVAL_DBNAME = \"$EVAL_DBNAME\"/" tmp_web_dir/pull-results-1.py
sed -i "s/DB_EVAL_RESTBL *=.*/DB_EVAL_RESTBL = \"$EVAL_RES_TBL_NAME\"/" tmp_web_dir/pull-results-1.py
sed -i "s/DB_LOC_HOST *=.*/DB_LOC_HOST = \"localhost\"/" tmp_web_dir/pull-results-1.py
sed -i "s/DB_LOC_USER *=.*/DB_LOC_USER = \"$WEB_DBUSER\"/" tmp_web_dir/pull-results-1.py
sed -i "s/DB_LOC_PASS *=.*/DB_LOC_PASS = \"$WEB_DBPASS\"/" tmp_web_dir/pull-results-1.py
sed -i "s/DB_LOC_DBNAME *=.*/DB_LOC_DBNAME = \"$WEB_DBNAME\"/" tmp_web_dir/pull-results-1.py

# scp the scripts to the web server (scripts ready to be run)
scp -r tmp_web_dir/* $WEB_USER@$WEB_IP:$WEB_SCRIPTS_PATH_R
rm -Rf tmp_web_dir
#===========================================================


#===========================================================
# Prepare and send the website
#===========================================================
rm -Rf tmp_site_dir
mkdir tmp_site_dir
cp -R $WEB_SITE_PATH/* tmp_site_dir
find tmp_site_dir/ -type d -name '.svn' | xargs rm -Rf
rm -f tmp_site_dir/*.sql tmp_site_dir/*~
# change default paramteres in globals.php (variables' values taken from init_param.sh)
T_PATH=$(echo $WEB_SUBMISSIONS_PATH | sed 's/\//\\\//g')
sed -i "s/\$SUBMISSIONS_PATH *=.*/\$SUBMISSIONS_PATH = '$T_PATH';/" tmp_site_dir/globals.php
# change default paramteres in mysql.config.php (variables' values taken from init_param.sh)
sed -i "s/\$db_salt *=.*/\$db_salt = \"someconst\";/" tmp_site_dir/mysql.config.php
sed -i "s/\$db_host *=.*/\$db_host = \"localhost\";/" tmp_site_dir/mysql.config.php
sed -i "s/\$db_username *=.*/\$db_username = \"$WEB_DBUSER\";/" tmp_site_dir/mysql.config.php
sed -i "s/\$db_password *=.*/\$db_password = \"$WEB_DBPASS\";/" tmp_site_dir/mysql.config.php
sed -i "s/\$db_name *=.*/\$db_name = \"$WEB_DBNAME\";/" tmp_site_dir/mysql.config.php
# scp the site and change permissions
ssh $WEB_USER@$WEB_IP "sudo chown $WEB_USER /var/www"
ssh $WEB_USER@$WEB_IP "rm -Rf $WEB_SITE_PATH_R &&\\
                       mkdir $WEB_SITE_PATH_R &&\\
                       rm -Rf $WEB_SUBMISSIONS_PATH &&\\
                       mkdir $WEB_SUBMISSIONS_PATH"
scp -r tmp_site_dir/* $WEB_USER@$WEB_IP:$WEB_SITE_PATH_R
rm -Rf tmp_site_dir
ssh $WEB_USER@$WEB_IP "chmod -R a+rx $WEB_SITE_PATH_R &&\\
                       chmod a+rw $WEB_SUBMISSIONS_PATH"
#===========================================================


#===========================================================
# Start the push/pull scripts
#===========================================================
ssh $WEB_USER@$WEB_IP "cd $WEB_SCRIPTS_PATH_R; python -u push-submissions-1.py &> $WEB_PUSH_STREAM &"
ssh $WEB_USER@$WEB_IP "cd $WEB_SCRIPTS_PATH_R; python -u pull-results-1.py &> $WEB_PULL_STREAM &"
#===========================================================








