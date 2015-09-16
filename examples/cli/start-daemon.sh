#!/bin/bash
##
## starts flat cli script as dameon 
##
#######################################
#######################################
STATUS_BASEDIR=~
FLAT_CLI_PATH=~/flat/deploy/cli.php
WORKER=$1
WORKER_MIN_RUNTIME=10
CONFIG=$2
echo "WORKER: $WORKER"
echo "FLAT_CLI_PATH: $FLAT_CLI_PATH"
echo "NOHUP BASE DIR: $STATUS_BASEDIR"
blank=""
if [["$WORKER"=="$blank"||"$WORKER"=="help"||"$WORKER"=="-help"||"$WORKER"=="--help"]]; then
   echo ""
fi
if [[ "$CONFIG" == "{none}" ]]; then
   CONFIG=$blank
fi
if [[ "$CONFIG" == "$blank" ]]; then
   echo "CONFIG: {none}"
   o_workerId="-o workerId=$WORKER"
   LOG_FILE="$STATUS_BASEDIR/logs/$WORKER.log"
   PID_FILE="$STATUS_BASEDIR/pids/$WORKER.pid"
else
   echo "CONFIG: $2"
   o_workerId="-o workerId=$WORKER-$2 -o cli-config=$2"
   LOG_FILE="$STATUS_BASEDIR/logs/$WORKER-$2.log"
   PID_FILE="$STATUS_BASEDIR/pids/$WORKER-$2.pid"
fi
WORKER_CMD="$WORKER $o_workerId --restart_limit_total=0 --restart_limit_hourly=100 --dameon=true --limit=1"
echo "CMD: $WORKER_CMD"
echo "LOG: $LOG_FILE"
echo "PIDFILE: $PIDFILE"
nohup php $FLAT_CLI_PATH $WORKER_CMD > $LOG_FILE >&1& echo $! > $PID_FILE
PID=$(cat $PID_FILE)
echo "PID: $PID"
echo "waiting $WORKER_MIN_RUNTIME seconds..."
sleep $WORKER_MIN_RUNTIME
kill_test=`kill 0 $PID`
if [[ "$kill_test" != "$blank" ]]; then
   echo "--worker output--"
   cat $LOG_FILE
   echo "--end worker output--"
   echo "worker no longer running after minimum time of $WORKER_MIN_RUNTIME seconds"
else
   echo "worker says:"
   tail -3 $LOG_FILE
fi
#
#
#
#
