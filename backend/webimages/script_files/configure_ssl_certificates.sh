#!/bin/sh

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

YUM_CMD=$(which yum)
APT_GET_CMD=$(which apt-get)
APT_HTTPD_CMD=$(which httpd)
NGINX_CMD=$(which nginx)

DOMAIN_DATA=$SCRIPT_DIR/domain_data.txt

DOMAIN_CERT_FOLDER="domain_cert_files"

SERVER_DOMAIN_CERT_PATH="${SCRIPT_DIR}/${DOMAIN_CERT_FOLDER}/"
SERVER_DOMAIN_CERT_FOLDER="${SCRIPT_DIR}/${DOMAIN_CERT_FOLDER}"
WORKING_DIR_REPLACE_PATH="${SCRIPT_DIR}/${DOMAIN_CERT_FOLDER}"

MAIN_PUBLIC_HTML_PATH="$(echo $SERVER_DOMAIN_CERT_PATH | rev | cut -d/ -f5- | rev)"

RENEWAL_CONF_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}renewal/"

TECH_TEAM_ERROR_MSG="Please contact to technical team."

mkdir -p $SERVER_DOMAIN_CERT_PATH


if [[  ! -z $NGINX_CMD ]]; then
	APT_APACHE_CMD="nginx"
elif [[  ! -z $APT_HTTPD_CMD ]]; then
	APT_APACHE_CMD="httpd"
else
	APT_APACHE_CMD="apache2"
fi

if [[ ! -z $SYS_CTL_CMD ]]; then
	PRE_HOOK_COMMAND="sudo systemctl stop $APT_APACHE_CMD"
	POST_HOOK_COMMAND="sudo systemctl restart $APT_APACHE_CMD"
else
	PRE_HOOK_COMMAND="service $APT_APACHE_CMD stop"
	POST_HOOK_COMMAND="service $APT_APACHE_CMD start"
fi

printHeaderMsg(){
    echo ""
    echo ""
    echo -e "\033[0;32m====================================================\033[0m"
	echo -e "\033[0;32m${1}\033[0m"
	echo -e "\033[0;32m====================================================\033[0m"
	echo ""
	echo ""
}

throwErrorMsg(){
    echo ""
    echo ""
    echo -e "\033[31m====================================================\033[0m"
	echo -e "\033[31m${1}\033[0m"
	echo -e "\033[31m====================================================\033[0m"
	echo ""
	echo ""
	exit 1
}

restartService(){
	if [[ ! -z $SYS_CTL_CMD ]]; then
		systemctl restart "${1}"
	else
		service ${1} restart
	fi
}

restartApacheServer(){
	restartService "$APT_APACHE_CMD"
}

configSSLCertificate(){

	#=========================================
	# SSL certificate Generation
	#=========================================
	
	printHeaderMsg "Generating SSL certificates for ${1} Started"
	
	RENEWAL_CONF_FILE_PATH="${RENEWAL_CONF_FILE_PATH}${1}.conf"
	
	if [ -e $RENEWAL_CONF_FILE_PATH ]; then
	
		CURRENT_RENEWAL_SSL_DIR_PATH="$( cat ${RENEWAL_CONF_FILE_PATH} | grep 'archive_dir' | awk '{print $3}' )"
		
		CURRENT_RENEWAL_SSL_ACCOUNT="$( cat ${RENEWAL_CONF_FILE_PATH} | grep 'account' | awk '{print $3}' )"
		
		CURRENT_RENEWAL_SSL_SERVER_PATH="$( cat ${RENEWAL_CONF_FILE_PATH} | grep 'server' | awk '{print $3}' | sed 's/https:\/\///g' )"
		CURRENT_RENEWAL_SSL_SERVER_PATH=${SERVER_DOMAIN_CERT_PATH}accounts/$CURRENT_RENEWAL_SSL_SERVER_PATH
		
		CURRENT_RENEWAL_SSL_PRE_HOOK_CMD="$( cat ${RENEWAL_CONF_FILE_PATH} | grep 'pre_hook' | awk '{ $1=$2="";$0=$0;} NF=NF' )"
		CURRENT_RENEWAL_SSL_POST_HOOK_CMD="$( cat ${RENEWAL_CONF_FILE_PATH} | grep 'post_hook' | awk '{ $1=$2="";$0=$0;} NF=NF' )"
		

		find $CURRENT_RENEWAL_SSL_SERVER_PATH -mindepth 1 -name $CURRENT_RENEWAL_SSL_ACCOUNT -prune -o -exec rm -rf {} +;
		
		sed -i "s|$CURRENT_RENEWAL_SSL_PRE_HOOK_CMD|$PRE_HOOK_COMMAND|g" $RENEWAL_CONF_FILE_PATH
		
		sed -i "s|$CURRENT_RENEWAL_SSL_POST_HOOK_CMD|$POST_HOOK_COMMAND|g" $RENEWAL_CONF_FILE_PATH
		
		
		RENEWAL_SSL_PUBLIC_HTML_PATH="$(echo $CURRENT_RENEWAL_SSL_DIR_PATH | rev | cut -d/ -f6- | rev)"
		CURRENT_DOMAIN_CERT_DIR="$(echo $CURRENT_RENEWAL_SSL_DIR_PATH | rev | cut -d/ -f3- | rev)"
		
		CURRENT_RENEWAL_WORK_DIR_PATH="$( cat ${RENEWAL_CONF_FILE_PATH} | grep 'work_dir' | awk '{print $3}' )"
		
		sed -i "s|$CURRENT_DOMAIN_CERT_DIR|$SERVER_DOMAIN_CERT_FOLDER|g" $RENEWAL_CONF_FILE_PATH
		
		sed -i "s|$RENEWAL_SSL_PUBLIC_HTML_PATH|$MAIN_PUBLIC_HTML_PATH|g" $RENEWAL_CONF_FILE_PATH
		sed -i "s|$CURRENT_RENEWAL_WORK_DIR_PATH|$WORKING_DIR_REPLACE_PATH|g" $RENEWAL_CONF_FILE_PATH
		
		
		# Reset Existing Files / Symlinks
		
		TOTAL_CERT_FILE_COUNT="$( find ${SERVER_DOMAIN_CERT_PATH}archive/${1}/ -type f | wc -l )"
		
		TOTAL_AVAILABLE_CERT_SET=$(($TOTAL_CERT_FILE_COUNT / 4))
		
		
		rm -rf ${SERVER_DOMAIN_CERT_PATH}live/${1}/cert.pem
		rm -rf ${SERVER_DOMAIN_CERT_PATH}live/${1}/chain.pem
		rm -rf ${SERVER_DOMAIN_CERT_PATH}live/${1}/fullchain.pem
		rm -rf ${SERVER_DOMAIN_CERT_PATH}live/${1}/privkey.pem
		
		
		ln -s ${SERVER_DOMAIN_CERT_PATH}archive/${1}/cert${TOTAL_AVAILABLE_CERT_SET}.pem ${SERVER_DOMAIN_CERT_PATH}live/${1}/cert.pem
		
		ln -s ${SERVER_DOMAIN_CERT_PATH}archive/${1}/chain${TOTAL_AVAILABLE_CERT_SET}.pem ${SERVER_DOMAIN_CERT_PATH}live/${1}/chain.pem
		
		ln -s ${SERVER_DOMAIN_CERT_PATH}archive/${1}/fullchain${TOTAL_AVAILABLE_CERT_SET}.pem ${SERVER_DOMAIN_CERT_PATH}live/${1}/fullchain.pem
		
		ln -s ${SERVER_DOMAIN_CERT_PATH}archive/${1}/privkey${TOTAL_AVAILABLE_CERT_SET}.pem ${SERVER_DOMAIN_CERT_PATH}live/${1}/privkey.pem
		
	fi
	
	certbot certonly --standalone -d "${1}" --keep-until-expiring --agree-tos --email markduo19@gmail.com --non-interactive --cert-path "$SERVER_DOMAIN_CERT_PATH" --config-dir "$SERVER_DOMAIN_CERT_PATH" --work-dir "$SERVER_DOMAIN_CERT_PATH" --logs-dir "$SERVER_DOMAIN_CERT_PATH" --pre-hook "$PRE_HOOK_COMMAND" --post-hook "$POST_HOOK_COMMAND"
	
	printHeaderMsg "Generating SSL certificates for ${1} Completed"
}

if [[ -z $(which certbot) ]]; then
	throwErrorMsg "SSLCertGenrationError:0100: Certbot Package is not available. ${TECH_TEAM_ERROR_MSG}"
fi

if [[ -f "$DOMAIN_DATA" && -s "$DOMAIN_DATA" ]]; then
    
	#last_line=$(wc -l < $DOMAIN_DATA)
	
	SERVICE_MAIN_DOMAIN_NAME=""
	
	while read domain
	  do
		SERVICE_MAIN_DOMAIN_NAME=$(echo "$domain")
		configSSLCertificate "${domain}"
	done < <(grep "" $DOMAIN_DATA)
	
	# if [[ ! -z $SERVICE_MAIN_DOMAIN_NAME ]]; then
		# MAIN_DOMAIN_NAME=$(basename "$SERVICE_MAIN_DOMAIN_NAME" | awk -F '.' '{print $(NF-1)"."$NF}')
		# configSSLCertificate "${MAIN_DOMAIN_NAME}"
	# fi

else 
	throwErrorMsg "SSLCertGenrationError:0100: Error can't install required components. Required data is not generated. Kindly browse your website home page by opening 'https://xxx.xx/'."
fi

restartApacheServer

bash "${SCRIPT_DIR}/sys_prj_config.sh"
