
#!/bin/sh
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

bash "${SCRIPT_DIR}/configure_cron_services.sh"

PANEL_PATH="`echo $SCRIPT_DIR | rev | cut -d'/' -f3- | rev`"

PANEL_PATH_PROJECTS="`echo $SCRIPT_DIR | rev | cut -d'/' -f4- | rev`"

IS_PROJECT_SETUP=true

CURRENT_LOCAL_IP_DATA="`ip addr show`"

DOMAIN_DATA=$SCRIPT_DIR/domain_data.txt
ORIG_DOMAIN_DATA=$SCRIPT_DIR/orig_domain_data.txt
SERVER_DOMAIN_CERT_PATH=$SCRIPT_DIR/domain_cert_files/
ALL_SERVICE_DOMAINS_FILE=$SCRIPT_DIR/port_data/services_config.json

SYSTEM_SERVICE_FILE_NAME="updateSysServices4295206497.php"
CRON_SERVICE_FILE_NAME="system_cron_jobs4295206497.php"

MONGO_DB_URL="mongodb://localhost:27017/"

if [ -f "${PANEL_PATH_PROJECTS}"/mongo_conn.txt ]; then
    MONGO_DB_URL=$(tail -n 1 "${PANEL_PATH_PROJECTS}"/mongo_conn.txt)
fi

MAIN_DOMAIN_NAME=$(tail -n 1 $DOMAIN_DATA)
ORIG_DOMAIN_NAME=$(tail -n 1 $ORIG_DOMAIN_DATA)

USE_SSL_DATA="No"
HTTP_PROTOCOL="http://"

if [ -f "${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/privkey.pem ] && [ -f "${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/fullchain.pem ] ; then
    USE_SSL_DATA="Yes"
	HTTP_PROTOCOL="https://"
fi

ALL_SERVICE_DOMAINS_ARR=( $(jq -r 'keys[]' ${ALL_SERVICE_DOMAINS_FILE}) )

forever stop $PANEL_PATH/assets/libraries/SocketClsNode/server.js

forever stop $PANEL_PATH/assets/libraries/SocketClient/server.js

if [[  -f "$PANEL_PATH/assets/libraries/adminMongo/app.js" ]] ; then
    forever stop $PANEL_PATH/assets/libraries/adminMongo/app.js
fi

if [[  -f "$PANEL_PATH/assets/libraries/mapsApiServiceProvider/app.js" ]] ; then
    forever stop $PANEL_PATH/assets/libraries/mapsApiServiceProvider/app.js
fi

if [[  -f "$PANEL_PATH/assets/libraries/wrtc/server.js" ]] ; then
    forever stop $PANEL_PATH/assets/libraries/wrtc/server.js
fi

if [[  -f "$PANEL_PATH/assets/libraries/appservice/server.js" ]] ; then
    forever stop $PANEL_PATH/assets/libraries/appservice/server.js
fi

if [[  -f "$PANEL_PATH/assets/libraries/media_server/app.js" ]] ; then
    forever stop $PANEL_PATH/assets/libraries/media_server/app.js
fi

for SERVICE_DOMAIN_ELEMENT in "${ALL_SERVICE_DOMAINS_ARR[@]}"
do
	
	SERVICE_DOMAIN_TMP="\"${SERVICE_DOMAIN_ELEMENT}\""
	
	if [[ ${IS_PROJECT_SETUP} == false && "${SCRIPT_DIR}" != *"/XXXXXXXX/"* && "${SERVICE_DOMAIN_TMP}" == *"XXXXXXXX"* ]]; then
	    continue;
	fi
	
	if [[ ${IS_PROJECT_SETUP} == false && "${SCRIPT_DIR}" != *"/XXXXXXXX/"* && "${SERVICE_DOMAIN_TMP}" == *"XXXXXXXX"* ]]; then
	    continue;
	fi
	
	echo ""
	echo ""
	echo "======================================================="
	echo "Initiating Services for '${SERVICE_DOMAIN_ELEMENT}'"
	echo "======================================================="
	echo ""
	echo ""
	
	SOCKET_CLUSTER_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.SOCKET_CLUSTER_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	SOCKET_PHP_CLIENT_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.SOCKET_PHP_CLIENT_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	MAPS_API_SERVICE_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.MAPS_API_SERVICE_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	WRTC_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.WRTC_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	APP_SERVICE_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.APP_SERVICE_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	ADMIN_MONGO_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.ADMIN_MONGO_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	
	ENABLE_DATA_ENCRYPTION=$( jq -r ".${SERVICE_DOMAIN_TMP}|.ENABLE_DATA_ENCRYPTION // \"No\"" "${ALL_SERVICE_DOMAINS_FILE}" )
	ENABLE_CRON_JOB=$( jq -r ".${SERVICE_DOMAIN_TMP}|.ENABLE_CRON_JOB // \"No\"" "${ALL_SERVICE_DOMAINS_FILE}" )

	ENABLE_MEDIA_SERVER=$( jq -r ".${SERVICE_DOMAIN_TMP}|.ENABLE_MEDIA_SERVER // \"No\"" "${ALL_SERVICE_DOMAINS_FILE}" )
	MEDIA_SERVER_RTMP_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.MEDIA_SERVER_RTMP_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	MEDIA_SERVER_RTMPS_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.MEDIA_SERVER_RTMPS_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	MEDIA_SERVER_HTTP_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.MEDIA_SERVER_HTTP_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	MEDIA_SERVER_HTTPS_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.MEDIA_SERVER_HTTPS_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	MEDIA_SERVER_PHP_PORT=$( jq -r ".${SERVICE_DOMAIN_TMP}|.MEDIA_SERVER_PHP_PORT" "${ALL_SERVICE_DOMAINS_FILE}" )
	
	SYSTEM_SERVICE_URL="https://${SERVICE_DOMAIN_ELEMENT}/${SYSTEM_SERVICE_FILE_NAME}"
	CRON_SERVICE_URL="https://${SERVICE_DOMAIN_ELEMENT}/${CRON_SERVICE_FILE_NAME}"
	
	CRONTAB_LIST_OUTPUT="`crontab -l`"
	
	###### Add Cron Job ######
	if [ "$CRONTAB_LIST_OUTPUT" != *$CRON_SERVICE_URL* ] && [ "$ENABLE_CRON_JOB" == "Yes" ]; then
		(echo ""; crontab -l 2>/dev/null; echo "* * * * * wget -q -O /dev/null \"${CRON_SERVICE_URL}\" --no-check-certificate"; echo "") | crontab -
		
	fi
	
	#if [[ "$CRONTAB_LIST_OUTPUT" != *$CRON_SERVICE_URL* ]]; then
	#	(echo ""; crontab -l 2>/dev/null; echo "* * * * * wget -q -O /dev/null \"${CRON_SERVICE_URL}\" --no-check-certificate"; echo "") | crontab -
	#fi

	(crontab -l 2>/dev/null;) | sed '/^$/d' | crontab -
	###### Add Cron Job ######

	#Start Admin Mongo
	ENABLE_DATA_ENCRYPTION="${ENABLE_DATA_ENCRYPTION}" IS_USE_SSL="${USE_SSL_DATA}" PORT="${ADMIN_MONGO_PORT}" SSL_KEY_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/privkey.pem SSL_CERT_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/fullchain.pem forever start $PANEL_PATH/assets/libraries/adminMongo/app.js

	# Start Socket Cluster
	ENABLE_DATA_ENCRYPTION="${ENABLE_DATA_ENCRYPTION}" IS_USE_SSL="${USE_SSL_DATA}" SOCKETCLUSTER_SOCKET_CHANNEL_LIMIT=9999999999999999 SOCKETCLUSTER_PORT="${SOCKET_CLUSTER_PORT}" ENV='prod' SOCKETCLUSTER_LOG_LEVEL=0 SSL_KEY_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/privkey.pem SSL_CERT_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/fullchain.pem forever start $PANEL_PATH/assets/libraries/SocketClsNode/server.js

	#Start Socket base for PHP
	ENABLE_DATA_ENCRYPTION="${ENABLE_DATA_ENCRYPTION}" IS_USE_SSL="${USE_SSL_DATA}" SOCKET_CLS_HOST="${MAIN_DOMAIN_NAME}" SOCKET_CLS_PROTOCOL="${HTTP_PROTOCOL}" SOCKET_CLS_PORT="${SOCKET_CLUSTER_PORT}" SERVICE_PORT="${SOCKET_PHP_CLIENT_PORT}" SSL_KEY_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/privkey.pem SSL_CERT_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/fullchain.pem forever start $PANEL_PATH/assets/libraries/SocketClient/server.js


	#Start Socket base for APIs
	if [[  -f "$PANEL_PATH/assets/libraries/mapsApiServiceProvider/app.js" ]] ; then
		ENABLE_DATA_ENCRYPTION="${ENABLE_DATA_ENCRYPTION}" IS_USE_SSL="${USE_SSL_DATA}" SYS_SERVICE_URL="${SYSTEM_SERVICE_URL}" MAPS_API_PORT="${MAPS_API_SERVICE_PORT}" MONGO_DB_CONNECTION_URL="${MONGO_DB_URL}" SSL_KEY_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/privkey.pem SSL_CERT_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/fullchain.pem forever start $PANEL_PATH/assets/libraries/mapsApiServiceProvider/app.js
	fi

	#Start RTC base 
	if [[  -f "$PANEL_PATH/assets/libraries/wrtc/server.js" ]] ; then
		ENABLE_DATA_ENCRYPTION="${ENABLE_DATA_ENCRYPTION}" IS_USE_SSL="${USE_SSL_DATA}" SYS_SERVICE_URL="${SYSTEM_SERVICE_URL}" SERVICE_PORT="${WRTC_PORT}" SSL_KEY_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/privkey.pem SSL_CERT_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/fullchain.pem forever start $PANEL_PATH/assets/libraries/wrtc/server.js
	fi

	sleep 20
	
	#Start AppService
	if [[  -f "$PANEL_PATH/assets/libraries/appservice/server.js" ]] ; then
		ENABLE_DATA_ENCRYPTION="${ENABLE_DATA_ENCRYPTION}" IS_USE_SSL="${USE_SSL_DATA}" SYS_SERVICE_URL="${SYSTEM_SERVICE_URL}" SERVICE_PORT="${APP_SERVICE_PORT}" MONGO_DB_CONNECTION_URL="${MONGO_DB_URL}" SSL_KEY_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/privkey.pem SSL_CERT_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/fullchain.pem forever start $PANEL_PATH/assets/libraries/appservice/server.js
	fi

	sleep 20

	if [ -f "$PANEL_PATH/assets/libraries/media_server/app.js" ] && [ "$ENABLE_MEDIA_SERVER" == "Yes" ]; then
		ENABLE_DATA_ENCRYPTION="${ENABLE_DATA_ENCRYPTION}" IS_USE_SSL="${USE_SSL_DATA}" SYS_SERVICE_URL="${SYSTEM_SERVICE_URL}" SERVICE_PORT="${MEDIA_SERVER_RTMP_PORT}" SECURE_SERVICE_PORT="${MEDIA_SERVER_RTMPS_PORT}" HTTP_SERVICE_PORT="${MEDIA_SERVER_HTTP_PORT}" HTTPS_SERVICE_PORT="${MEDIA_SERVER_HTTPS_PORT}" PHP_SERVICE_PORT="${MEDIA_SERVER_PHP_PORT}" SSL_KEY_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/privkey.pem SSL_CERT_FILE_PATH="${SERVER_DOMAIN_CERT_PATH}"live/"${MAIN_DOMAIN_NAME}"/fullchain.pem forever start $PANEL_PATH/assets/libraries/media_server/app.js
	fi

	sleep 20

	echo ""
	echo ""
done

echo ""
echo ""

echo "======================================================="
echo "All Service are initialized."
echo "======================================================="

echo ""
echo ""
