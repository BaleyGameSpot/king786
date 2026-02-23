#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PANEL_PATH="`echo $SCRIPT_DIR | rev | cut -d'/' -f3- | rev`"

CURRENT_LOCAL_IP_DATA="`ip addr show`"

FILE_PATH=$(echo "$SCRIPT_DIR/server_information_usage.txt")

# Check Disk Usage 
TOTAL_DISK_SIZE=$(df -hT `echo $PANEL_PATH` | awk 'FNR == 2 {print $3}' | sed 's/G//')

USED_DISK_SIZE=$(df -hT `echo $PANEL_PATH` | awk 'FNR == 2 {print $4}' | sed 's/G//')

AVAIL_DISK_SIZE=$(df -hT `echo $PANEL_PATH` | awk 'FNR == 2 {print $5}' | sed 's/G//')

USED_DISK_SIZE_PERCENTAGE=$(df -hT `echo $PANEL_PATH` | awk 'FNR == 2 {print $6}' | sed 's/%//')


# PHP Process Count
PHP_PROCESS_COUNT=$(ps -Al | grep -c php)

# Node Process Count
NODE_PROCESS_COUNT=$(ps -Al | grep -c node)

# HTTPD Process Count
HTTPD_PROCESS_COUNT=$(ps -Al | grep -E -c 'httpd|apache2')

#Concurrent Apache Connections
CONCURRENT_APACHE_CONNECTIONS=$(netstat -ant | grep -E ':80|:443' | wc -l)

#CPU Usage
CURRENT_CPU_USAGE=$(top -b -n1 | grep 'Cpu(s)\:' | awk '{print $2}')

#CPU Avg. Load
CPU_AVG_LOAD=$(top -b -n1 | grep 'load average:' | awk '{print $13}' | sed 's/,//')


#RAM Usage
#RAM_USED_PERCENTAGE=$(free -t | awk 'NR == 2 {printf("%.2f"), $3/$2*100})
TOTAL_RAM=$(cat /proc/meminfo | grep 'MemTotal:' | awk '{print $2}')
AVAILABLE_RAM=$(cat /proc/meminfo | grep 'MemAvailable:' | awk '{print $2}')
RAM_USED_PERCENTAGE=$(echo "scale=3; ($TOTAL_RAM-$AVAILABLE_RAM)/$TOTAL_RAM*100" | bc | awk '{printf "%.2f",$0}')

TOTAL_RAM_GB=$(echo "scale=2; ($TOTAL_RAM)*0.000001/1" | bc | awk '{printf "%.2f",$0}')
AVAILABLE_RAM_GB=$(echo "scale=2; ($AVAILABLE_RAM)*0.000001/1" | bc | awk '{printf "%.2f",$0}')

#AVAILABLE_RAM_GB=$(echo "scale=2; ($AVAILABLE_RAM)*0.000001/1" | bc)
RAM_USED_PERCENTAGE_GB=$(echo "scale=5; ($TOTAL_RAM_GB-$AVAILABLE_RAM_GB)/$TOTAL_RAM_GB*100" | bc | awk '{printf "%.2f",$0}')

COMMAND_EXECUTED_TIME=$(date --utc +"%Y-%m-%dT%H:%M:%S@UTC")


if [[ -f "$FILE_PATH" ]]; then

   while read line
    do

      if [ "`echo "$line" | awk -v RS=',' 'END{print NR-1}'`" -gt "62" ]; then
        
        IFS=',' read -ra LINE_ARR <<< "$line"
       
        LINE_ARR_LENGTH=${#LINE_ARR[@]}
        
        for (( i=${LINE_ARR_LENGTH}-1; i>=0; i-- ))
        do
            CURRENT_TMP_VALUE=$(echo ${LINE_ARR[$i]} | awk -F "=" '{ print $NF }')
      
            if [ $(($LINE_ARR_LENGTH - $i)) -eq "1" ]; then
                line_new=$(echo "$CURRENT_TMP_VALUE")
            elif [ $(($LINE_ARR_LENGTH - $i)) -lt "63" ]; then
                line_new=$(echo "$CURRENT_TMP_VALUE,$line_new")
            fi
        done
    
        LINE_PREFIX=$(echo "$line" | awk -F "=" '{ print $1 }')
        line=$(echo "$LINE_PREFIX=$line_new")

      fi    

      case "$line" in
         TOTAL_RAM*)
             CURRENT_DATA=$(sed "s/TOTAL_RAM=//g" <<< "$line")
             TOTAL_RAM_GB=$(echo "$CURRENT_DATA,$TOTAL_RAM_GB")
         ;;

         RAM_USED_PERCENTAGE*)
             CURRENT_DATA=$(sed "s/RAM_USED_PERCENTAGE=//g" <<< "$line")
             RAM_USED_PERCENTAGE_GB=$(echo "$CURRENT_DATA,$RAM_USED_PERCENTAGE_GB")
         ;;

         AVAILABLE_RAM*)
             CURRENT_DATA=$(sed "s/AVAILABLE_RAM=//g" <<< "$line")
             AVAILABLE_RAM_GB=$(echo "$CURRENT_DATA,$AVAILABLE_RAM_GB")
         ;;

         TOTAL_DISK_SIZE*)
             CURRENT_DATA=$(sed "s/TOTAL_DISK_SIZE=//g" <<< "$line")
             TOTAL_DISK_SIZE=$(echo "$CURRENT_DATA,$TOTAL_DISK_SIZE")
         ;;

         USED_DISK_SIZE_PERCENTAGE*)
             CURRENT_DATA=$(sed "s/USED_DISK_SIZE_PERCENTAGE=//g" <<< "$line")
             USED_DISK_SIZE_PERCENTAGE=$(echo "$CURRENT_DATA,$USED_DISK_SIZE_PERCENTAGE")
         ;;

         USED_DISK_SIZE*)
             CURRENT_DATA=$(sed "s/USED_DISK_SIZE=//g" <<< "$line")
             USED_DISK_SIZE=$(echo "$CURRENT_DATA,$USED_DISK_SIZE")
         ;;

         AVAIL_DISK_SIZE*)
             CURRENT_DATA=$(sed "s/AVAIL_DISK_SIZE=//g" <<< "$line")
             AVAIL_DISK_SIZE=$(echo "$CURRENT_DATA,$AVAIL_DISK_SIZE")
         ;;

         PHP_PROCESS_COUNT*)
             CURRENT_DATA=$(sed "s/PHP_PROCESS_COUNT=//g" <<< "$line")
             PHP_PROCESS_COUNT=$(echo "$CURRENT_DATA,$PHP_PROCESS_COUNT")
         ;;

         NODE_PROCESS_COUNT*)
             CURRENT_DATA=$(sed "s/NODE_PROCESS_COUNT=//g" <<< "$line")
             NODE_PROCESS_COUNT=$(echo "$CURRENT_DATA,$NODE_PROCESS_COUNT")
         ;;

         HTTPD_PROCESS_COUNT*)
             CURRENT_DATA=$(sed "s/HTTPD_PROCESS_COUNT=//g" <<< "$line")
             HTTPD_PROCESS_COUNT=$(echo "$CURRENT_DATA,$HTTPD_PROCESS_COUNT")
         ;;

         CONCURRENT_APACHE_CONNECTIONS*)
             CURRENT_DATA=$(sed "s/CONCURRENT_APACHE_CONNECTIONS=//g" <<< "$line")
             CONCURRENT_APACHE_CONNECTIONS=$(echo "$CURRENT_DATA,$CONCURRENT_APACHE_CONNECTIONS")
         ;;

         CURRENT_CPU_USAGE*)
             CURRENT_DATA=$(sed "s/CURRENT_CPU_USAGE=//g" <<< "$line")
             CURRENT_CPU_USAGE=$(echo "$CURRENT_DATA,$CURRENT_CPU_USAGE")
         ;;

         CPU_AVG_LOAD*)
             CURRENT_DATA=$(sed "s/CPU_AVG_LOAD=//g" <<< "$line")
             CPU_AVG_LOAD=$(echo "$CURRENT_DATA,$CPU_AVG_LOAD")
         ;;

         COMMAND_EXECUTED_TIME*)
             CURRENT_DATA=$(sed "s/COMMAND_EXECUTED_TIME=//g" <<< "$line")
             COMMAND_EXECUTED_TIME=$(echo "$CURRENT_DATA,$COMMAND_EXECUTED_TIME")
         ;;

      esac
    done < $FILE_PATH

fi


printf "TOTAL_RAM=$TOTAL_RAM_GB\n" >"$FILE_PATH"
printf "RAM_USED_PERCENTAGE=$RAM_USED_PERCENTAGE_GB\n" >>"$FILE_PATH"
printf "AVAILABLE_RAM=$AVAILABLE_RAM_GB\n" >>"$FILE_PATH"

printf "TOTAL_DISK_SIZE=$TOTAL_DISK_SIZE\n" >>"$FILE_PATH"
printf "USED_DISK_SIZE=$USED_DISK_SIZE\n" >>"$FILE_PATH"
printf "AVAIL_DISK_SIZE=$AVAIL_DISK_SIZE\n" >>"$FILE_PATH"
printf "USED_DISK_SIZE_PERCENTAGE=$USED_DISK_SIZE_PERCENTAGE\n" >>"$FILE_PATH"

printf "PHP_PROCESS_COUNT=$PHP_PROCESS_COUNT\n" >>"$FILE_PATH"
printf "NODE_PROCESS_COUNT=$NODE_PROCESS_COUNT\n" >>"$FILE_PATH"
printf "HTTPD_PROCESS_COUNT=$HTTPD_PROCESS_COUNT\n" >>"$FILE_PATH"
printf "CONCURRENT_APACHE_CONNECTIONS=$CONCURRENT_APACHE_CONNECTIONS\n" >>"$FILE_PATH"
printf "CURRENT_CPU_USAGE=$CURRENT_CPU_USAGE\n" >>"$FILE_PATH"
printf "CPU_AVG_LOAD=$CPU_AVG_LOAD\n" >>"$FILE_PATH"
printf "COMMAND_EXECUTED_TIME=$COMMAND_EXECUTED_TIME\n" >>"$FILE_PATH"

#vnstat Requirements
#yum install epel-release
#yum install NetworkManager = Network manager 

#iflist





