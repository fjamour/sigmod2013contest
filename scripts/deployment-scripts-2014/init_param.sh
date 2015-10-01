#  Copyright (C) 2013 by
#  Fuad Jamour <fjamour@gmail.com>
#  All rights reserved.
#  MIT license.

#=======================================
# Local machine parameters
#=======================================
LOC_IP="10.68.186.24"
SIGMOD_PATH="/home/fuad/Desktop/scratch/del2/sigmod2013contest"

WEB_TABLES_SQL_PATH="$SIGMOD_PATH/scripts/db-tables/web-tables.sql"
EVAL_TABLES_SQL_PATH="$SIGMOD_PATH/scripts/db-tables/eval-tables.sql"

WEB_SCRIPTS_PATH="$SIGMOD_PATH/scripts/web-scripts"
EVAL_SCRIPT_PATH="$SIGMOD_PATH/scripts/eval-scripts"

BACKUP_SCRIPT_PATH="$SIGMOD_PATH/scripts/backup-scripts"
BACKUP_DIR="/home/fuad/scratch/sigmod-backups"
BACKUP_SLEEP_TIME="60"
#=======================================

#=======================================
# Evaluation machine parameters
#=======================================
EVAL_IP="184.73.234.72"
EVAL_USER="ubuntu"
EVAL_RUNNER_USER="ubuntu"

EVAL_HOME="/home/$EVAL_USER"
EVAL_FOLDER="sigmod-eval"
EVAL_SUBMISSIONS_PATH="$EVAL_HOME/$EVAL_FOLDER/submissions"
EVAL_SCRIPT_PATH_R="$EVAL_HOME/$EVAL_FOLDER/eval-script"
EVAL_TESTER_PATH="$EVAL_SCRIPT_PATH_R/sigmod2013tester"
EVAL_WORKDIR_PATH="$EVAL_SCRIPT_PATH_R/work-dir"
EVAL_LOG_STREAM="$EVAL_HOME/$EVAL_FOLDER/out.log"

EVAL_DBNAME="evaldb"
EVAL_DBUSER="evaluser"
EVAL_DBPASS="123"
EVAL_RES_TBL_NAME="results"
EVAL_STR_SIZE="2048"

EVAL_SLEEP_TIME="0.5"
EVAL_MAX_RUNTIME_SMALL="60"
EVAL_MAX_RUNTIME_BIG="600"
EVAL_MAX_RUNTIME_NEW="600"
MAX_TIME_TO_RUN_BIGTEST="20"
MAX_TIME_TO_RUN_NEWTEST="30"
#=======================================

#=======================================
# Web server parameters
#=======================================
WEB_IP="184.73.234.72"
WEB_USER="ubuntu"
WEB_APACHE_GRP="ubuntu"

WEB_SITE_PATH="$SIGMOD_PATH/web/sigmod2013contest_web/trunk"
WEB_SITE_PATH_R="/var/www/sigmod2014"
WEB_SUBMISSIONS_PATH="$WEB_SITE_PATH_R/submissions"

WEB_HOME="/home/$WEB_USER"
WEB_FOLDER="sigmod-web"
WEB_SCRIPTS_PATH_R="$WEB_HOME/$WEB_FOLDER/scripts"

WEB_DB_ROOT_PASS=""
WEB_DBNAME="webdb"
WEB_DBUSER="webuser"
WEB_DBPASS="123"

WEB_PUSH_SLEEP_TIME="0.5"
WEB_PULL_SLEEP_TIME="0.5"

WEB_PUSH_STREAM="$WEB_HOME/$WEB_FOLDER/push.log"
WEB_PULL_STREAM="$WEB_HOME/$WEB_FOLDER/pull.log"
#=======================================

