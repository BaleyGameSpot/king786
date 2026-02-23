#!/bin/sh

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CRONTAB_LIST_OUTPUT="`crontab -l`"

SYS_SCRIPT_PATH=$SCRIPT_DIR/sys_prj_config.sh
SERVER_PERFORMANCE_SCRIPT_PATH=$SCRIPT_DIR/server_performace_info.sh
SSL_CERTI_SCRIPT_PATH=$SCRIPT_DIR/configure_ssl_certificates.sh

SYSTEM_PATH_DATA="$( echo $PATH )"

CRONTAB_DATA_PATH_CMP="$( echo "${CRONTAB_LIST_OUTPUT}" | grep -P "(^| )PATH=\"${SYSTEM_PATH_DATA}\"($| )" )"

if [ -z $CRONTAB_DATA_PATH_CMP ] && [ ! -z $SYSTEM_PATH_DATA ]; then

    (crontab -l 2>/dev/null;) | sed '/^PATH="/d' | crontab -
    
    (crontab -l 2>/dev/null;) | sed '/^$/d' | crontab -

    (echo ""; echo "PATH=\"$SYSTEM_PATH_DATA\"";  echo ""; crontab -l 2>/dev/null) | crontab -
    
fi

if [[ "$CRONTAB_LIST_OUTPUT" != *$SCRIPT_DIR/sys_prj_config.sh* ]]; then
    (crontab -l 2>/dev/null; echo ""; echo "@reboot bash ${SYS_SCRIPT_PATH}"; echo "15 0 * * * bash ${SYS_SCRIPT_PATH}"; echo "") | crontab -
fi


if [[ "$CRONTAB_LIST_OUTPUT" != *$SCRIPT_DIR/server_performace_info.sh* ]]; then
	(echo ""; crontab -l 2>/dev/null; echo "* * * * * bash ${SERVER_PERFORMANCE_SCRIPT_PATH}") | crontab -
fi


if [[ "$CRONTAB_LIST_OUTPUT" != *$SCRIPT_DIR/configure_ssl_certificates.sh* ]]; then
    (echo ""; crontab -l 2>/dev/null; echo "25 0 * * * bash ${SSL_CERTI_SCRIPT_PATH}"; echo "") | crontab -
fi

(crontab -l 2>/dev/null;) | sed '/^$/d' | crontab -

chmod +x "${SYS_SCRIPT_PATH}"
chmod +x "${SERVER_PERFORMANCE_SCRIPT_PATH}"
chmod +x "${SSL_CERTI_SCRIPT_PATH}"
