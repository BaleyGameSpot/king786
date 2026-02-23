#!/bin/sh
YUM_CMD=$(which yum)
APT_GET_CMD=$(which apt-get)

MONGOD_CMD=$(which mongod)
SYS_CTL_CMD=$(which systemctl)
LSB_REL_CMD=$(which lsb_release)
CURRENT_PATH=$(pwd)
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CENTOS_VERSION="`rpm --eval '%{centos_ver}'`"

RHEL_OS_VERSION=$(cat /etc/redhat-release | cut -d'.' -f1 | awk '{print $NF}')

UBUNTU_CODE_NAME=$(cat /etc/os-release | grep UBUNTU_CODENAME | cut -d = -f 2)

IS_WHM_INSTALLED=$(/usr/local/cpanel/cpanel -V)

APT_HTTPD_CMD=$(which httpd)
NGINX_CMD=$(which nginx)

INSTALL_MONGO_DB=true
INSTALL_RTC_SUPPORT=true

PANEL_PATH="`echo $SCRIPT_DIR | rev | cut -d'/' -f3- | rev`"
SCRIPT_PATH=$SCRIPT_DIR/sys_prj_config.sh
SERVER_INFO_SCRIPT_PATH=$SCRIPT_DIR/server_performace_info.sh
SSL_CERTI_SCRIPT_PATH=$SCRIPT_DIR/configure_ssl_certificates.sh
CRON_SCRIPT_PATH=$SCRIPT_DIR/configure_cron_services.sh

PORT_LIST_FILE=${SCRIPT_DIR}/port_data/services_config.json
THEME_COLOR_FILE=${SCRIPT_DIR}/system_theme_color.json

SERVER_DOMAIN_CERT_LOG_PATH=$SCRIPT_DIR/cert_domain_log.txt
DOMAIN_DATA=$SCRIPT_DIR/domain_data.txt

SERVER_DOMAIN_CERT_PATH=$SCRIPT_DIR/domain_cert_files/

GENERAL_ERROR_MSG="Error can't install required components. Please contact to technical team."

TECH_TEAM_ERROR_MSG="Please contact to technical team."

UNKNOWN_OS_ERROR_MSG="System OS is not supported. Please contact to technical team."

domainRegex='^([a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]\.)+[a-zA-Z]{2,}$'


if [[  ! -z $NGINX_CMD ]]; then
	APT_APACHE_CMD="nginx"
elif [[  ! -z $APT_HTTPD_CMD ]]; then
	APT_APACHE_CMD="httpd"
else
	APT_APACHE_CMD="apache2"
fi

chmod +x "${SCRIPT_PATH}"
chmod +x "${SERVER_INFO_SCRIPT_PATH}"
chmod +x "${SSL_CERTI_SCRIPT_PATH}"
chmod +x "${CRON_SCRIPT_PATH}"


if [[ ! -f "${SCRIPT_DIR}/installer-packages/rhel/v${RHEL_OS_VERSION}/mongodb-org-7.0.repo" ]]; then
	RHEL_OS_VERSION="9"
fi

if [[ ! -f "${SCRIPT_DIR}/installer-packages/ubuntu/${UBUNTU_CODE_NAME}/mongodb-org-7.0.list" ]]; then
	UBUNTU_CODE_NAME="jammy"
fi

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

throwErrorMsgNoExit(){
    echo ""
    echo ""
    echo -e "\033[31m====================================================\033[0m"
	echo -e "\033[31m${1}\033[0m"
	echo -e "\033[31m====================================================\033[0m"
	echo ""
	echo ""
}

printHeaderMsg(){
    echo ""
    echo ""
    echo -e "\033[0;32m====================================================\033[0m"
	echo -e "\033[0;32m${1}\033[0m"
	echo -e "\033[0;32m====================================================\033[0m"
	echo ""
	echo ""
}

printColorMsg(){
    echo ""
	echo -e "\033[0;32m${1}\033[0m"
}

printColorMsgNoNewLine(){
	echo -e "\033[0;32m${1}\033[0m"
}

manageService(){
    
    if [ ${1} == "STOP" ]; then
        if [[ ! -z $SYS_CTL_CMD ]]; then
        	sudo systemctl daemon-reload
        	sudo systemctl stop ${2}
        	sudo systemctl disable ${2}
        else
        	service ${2} stop
        	chkconfig ${2} off
        fi
    elif [ ${1} == "RESTART" ]; then
        manageService "STOP" ${2}
        manageService "START" ${2}
    else
        if [[ ! -z $SYS_CTL_CMD ]]; then
        	sudo systemctl daemon-reload
        	sudo systemctl start ${2}
        	sudo systemctl enable ${2}
        else
        	service ${2} start
        	chkconfig ${2} on
        fi
    fi
}

isServiceAvailable(){
	
	if [[ ! -z $(systemctl list-unit-files --type service | grep -F "${1}") ]] || [[  $(service ${1} status | grep 'Active') == *"Active"* ]]; then
		return 0
	fi
	
	return 1
}


rhelInstall(){
    yum install -y $SCRIPT_DIR/installer-packages/rhel/v${RHEL_OS_VERSION}/${1}*
    
    #if [[ "$CENTOS_VERSION" == "7" ]]; then
	#	yum install -y $SCRIPT_DIR/installer-packages/rhel/v${RHEL_OS_VERSION}#/${1}*
	#elif [[ "$CENTOS_VERSION" == "8" ]]; then
	#	dnf install -y $SCRIPT_DIR/installer-packages/rhel/v${RHEL_OS_VERSION}#/${1}*
	#elif [[ "$CENTOS_VERSION" == "9" ]]; then
	#	dnf install -y $SCRIPT_DIR/installer-packages/rhel/v${RHEL_OS_VERSION}#/${1}*
	#fi
}

rhelRepoConfig(){
    if [[ "$RHEL_OS_VERSION" == "7" ]]; then
        yum-config-manager --enable remi
        yum-config-manager --enable rpmfusion-free-updates
    elif [[ "$RHEL_OS_VERSION" == "8" ]]; then
		dnf config-manager --set-enabled powertools
		dnf config-manager --set-enabled rpmfusion-free-updates
		dnf config-manager --set-enabled remi
	elif [[ "$RHEL_OS_VERSION" == "9" ]]; then
		dnf config-manager --set-enabled rpmfusion-free-updates
		dnf config-manager --set-enabled remi
		dnf config-manager --set-enabled crb
	fi
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

restartPHPFpmService(){
	
	if [[  ! -z $IS_WHM_INSTALLED ]]; then
		
		restartService "cpanel_php_fpm.service"
		
		# Restart php-fpm for each PHP version
		for version in $(ls /opt/cpanel | grep ea-php); do
			restartService "${version}-php-fpm"
		done
		
	elif [ -f "/scripts/restart_cwpsrv" ] && [ ! -z $SYS_CTL_CMD ]; then
		for service in $(systemctl list-units --type=service | grep php | awk '{print $1N}'); do
			restartService "${service}"
		done
	fi
}


configurePHPiniFile(){
	sed -i "s|^file_uploads[[:blank:]]*=.*|file_uploads = On|g" "${1}"
	sed -i "s|^max_file_uploads[[:blank:]]*=.*|max_file_uploads = 50|g" "${1}"
	sed -i "s|^allow_url_fopen[[:blank:]]*=.*|allow_url_fopen = On|g" "${1}"
	sed -i "s|^allow_url_include[[:blank:]]*=.*|allow_url_include = Off|g" "${1}"
	sed -i "s|^upload_max_filesize[[:blank:]]*=.*|upload_max_filesize = 1500M|g" "${1}"
	sed -i "s|^max_execution_time[[:blank:]]*=.*|max_execution_time = 0|g" "${1}"
	sed -i "s|^post_max_size[[:blank:]]*=.*|post_max_size = 1500M|g" "${1}"
	sed -i "s|^memory_limit[[:blank:]]*=.*|memory_limit = -1|g" "${1}"
	sed -i "s|^max_input_time[[:blank:]]*=.*|max_input_time = 0|g" "${1}"
	sed -i "s|^display_errors[[:blank:]]*=.*|display_errors = Off|g" "${1}"
	sed -i "s|^session.gc_maxlifetime[[:blank:]]*=.*|session.gc_maxlifetime = 43200|g" "${1}"
	sed -i "s|^zlib.output_compression[[:blank:]]*=.*|zlib.output_compression = On|g" "${1}"
	sed -i "s|^short_open_tag[[:blank:]]*=.*|short_open_tag = On|g" "${1}"
	sed -i -E "/^;?\s*max_input_vars\s*=/s/^;?\s*max_input_vars\s*=.*/max_input_vars = 15000/" "${1}"
}

killall node

#=========================================
# Resolving Dependencies
#=========================================

if [[ -z $(which curl) ]]; then
	throwErrorMsg "CURL is not installed. ${TECH_TEAM_ERROR_MSG}"
fi

if [ ! -z $YUM_CMD ] && [ ! -z $RHEL_OS_VERSION ]; then

	yum remove -y nodejs
	yum remove -y epel-release
	yum remove -y ImageMagick
	
	#yum remove -y imh-cpanel-cache-manager
	#yum remove -y ea-apache24-mod_security2
	#yum remove -y mod_security mod_security_crs
	
	rm -rf /var/cache/yum
	rm -rf /etc/yum.repos.d/nodesource*
	yum clean all
	
	yum install -y bind-utils
	yum install -y epel-release
	
	rhelInstall "remi-release"
	rhelInstall "rpmfusion"
	
	rhelRepoConfig
	
	rhelInstall "libnghttp2"
	
	curl -sL https://rpm.nodesource.com/setup_lts.x | bash -
	
	packages_to_install=("jq" "wget" "bc" "java" "java-devel" "gcc" "gcc-c++" "kernel-devel" "make" "psmisc" "nghttp2" "libnghttp2-devel" "openssl-devel" "Development Tools" "pkg-config*" "gpg" "git" "mlocate" "php-devel" "python3" "zlib-devel" "libev-devel" "c-ares-devel" "libevent" "libevent-devel" "openssl*" "nodejs" "ffmpeg" "ffmpeg-devel" "libmicrohttp*" "libcrypto*" "glibc*" "libssl*" "*rpmlib*" "coturn*" "libmariadb*" "libncurses*" "xz-libs*" "perl-libwww-perl.noarch" "perl-LWP-Protocol-https.noarch" "perl-GDGraph" "oniguruma" "libsodium" "telnet" "x265*" "libheif" "libheif-devel" "libpng-devel" "libjpeg-devel" "libwebp-devel" "harfbuzz-devel" "librsvg2*" "libzstd*" "libraqm*" "libraw*" "liblzma*" "libgnutls*" "libtool*" "libtiff*" "libwmf*" "libxml2*" "freetype*" "libopenjp2*" "openexr*" "libzip*" "libperl*" "gmp-devel" "djvulibre*" "SDL2*" "meson" "meson*" "gtk-doc" "gnupg*" "ninja-build*" "texinfo*" "yasm*" "nasm*" "zlib1g*" "libx264*" "libx265*" "x264*" "gnutls*" "fdk-aac-free*" "mod_dav_svn" "subversion" "certbot")
	
	#"libnuma*" "libvpx*" "libfdk*" "libopus*" "opus*" "libaom*" "libsvtav1*" "libdav1d*" "libvmaf*" "libunistring*" "libva*" "libvdpau*" "libvorbis*" "libxcb1*" "libxcb*" "libavcodec*" "libavformat*" "libavutil*" "libavfilter*" "libavdevice*" "libswresample*" "libswscale*" "libass*" "libfreetype*"  "libmp3lame*" "libsdl2*" "harfbuzz*" "fribidi*"   "libltdl*" "libgs*" 
	
	# Install each package
	for package in "${packages_to_install[@]}"; do
		if [[ "$package" == "Development Tools" ]];then
			yum groupinstall -y "$package"
			continue
		fi
		yum install -y "$package"
	done
	
	#pkg-config libheif --modversion
	
	npm install -y -g npm@10.8.1
	
elif [ ! -z $APT_GET_CMD ] && [ ! -z $UBUNTU_CODE_NAME ]; then

	#To Prevent graphical Interface for restarting services.
	grep -qxF "\$nrconf{restart} = 'a'" "/etc/needrestart/needrestart.conf" && echo "Skipping line" ||  echo "\$nrconf{restart} = 'a'" >> "/etc/needrestart/needrestart.conf"
	
	sudo apt-get -y purge --auto-remove nodejs npm
	
	sudo apt-get purge -y imagemagick-*
	sudo apt-get remove -y graphicsmagick
	sudo apt-get remove -y imagemagick*
	
	#sudo apt-get remove -y imh-cpanel-cache-manager
	#sudo apt-get remove -y ea-apache24-mod_security2
	#sudo apt-get remove -y libapache2-mod-security2 modsecurity-crs
	
	sudo add-apt-repository universe
    sudo add-apt-repository multiverse
	
	sudo apt-get autoclean
	sudo apt-get -y update
	
	curl -sL https://deb.nodesource.com/setup_lts.x | sudo bash -
	
	packages_to_install=("dnsutils" "wget" "gnome-keyring" "default-jre" "default-jdk" "build-essential" "checkinstall" "git-all" "mlocate" "pkg-config" "pkg-config*" "gpg" "gpg*" "openssl*" "nodejs" "php-dev" "aptitude" "python-is-python3" "gnupg*" "libxi-dev" "libglu1-mesa-dev" "libglew-dev" "nghttp2" "libnghttp2-dev" "libssl-dev" "gzip" "bc" "jq" "libmemcached-tools" "libevent-dev" "libssl1.1" "libmicrohttp*" "texinfo*" "libcrypto*" "glibc*" "libssl*" "*rpmlib*" "coturn*" "libraw-dev" "libdjvulibre-dev" "libwmf-dev" "libzstd-dev" "libopenjp2-tools" "libopenjp2*" "libopenexr-dev" "xz-libs*" "libonig-dev" "libsodium-dev" "libmariadbclient*" "libmariadb*" "telnet" "libheif-dev" "libwebp-dev" "libjpeg-dev" "libpng-dev" "libxml2-dev" "libx265-dev" "librsvg2-bin" "liblqr*" "librsvg2-dev" "liblzma-dev" "libtiff-dev" "libtiff-tools" "libtiff-opengl" "libfreetype6-dev" "meson" "gtk-doc-tools" "libfreetype*" "libgnutls*" "libtool*" "meson*" "ninja-build*" "yasm*" "nasm*" "zlib1g*" "libunistring*" "libx264*" "libx265*" "x264*" "gnutls*" "ffmpeg" "subversion" "certbot")
	
	# "fdk-aac-free*" "libmp3lame*" "libsdl2*" "libva*" "libvdpau*" "libvorbis*" "libxcb1*" "libxcb*" "libnuma*" "libvpx*" "libfdk*" "libaom*" "libdav1d*" "libopus*" "libsvtav1*" "libvmaf*" "libraqm-dev" "libltdl-dev" "libgs-dev" "libzip-dev" "libperl-dev" "libghc-bzlib-dev" "libsdl2-dev" "libavcodec*" "libavformat*" "libavutil*" "libavfilter*" "libavdevice*" "libswresample*" "libharfbuzz-dev" "libfribidi-dev" "libswscale*" "libass*"
	
	# Install each package
	for package in "${packages_to_install[@]}"; do
		sudo DEBIAN_FRONTEND=noninteractive apt-get install -y "$package"
	done
	
	npm install -y -g npm@10.8.1
else
	throwErrorMsg "${UNKNOWN_OS_ERROR_MSG}"
fi

if [[ -z $(which openssl) ]]; then
	throwErrorMsg "Can't install OpenSSL. ${TECH_TEAM_ERROR_MSG}"
fi

if [ -z $(which node) ] ||  [ -z $(which npm) ]; then
    throwErrorMsg "Unable to install NodeJS. ${TECH_TEAM_ERROR_MSG}"
fi

if [[ -z $(which dig) ]]; then
    throwErrorMsg "Didn't find dig (dns utils) package. ${TECH_TEAM_ERROR_MSG}"
fi

if [[ -z $(which gpg) ]]; then
    throwErrorMsg "Didn't find gpg package. ${TECH_TEAM_ERROR_MSG}"
fi

if [[ -z $(which certbot) ]]; then
	throwErrorMsg "Didn't find Certbot package. ${TECH_TEAM_ERROR_MSG}"
fi


#=========================================
# Installing npm - NodeJs Components
#=========================================
npm install forever -g

npm install -g socketcluster


#=========================================
# CURL Installation
#=========================================

printHeaderMsg "Installing CURL."

CURL_INSTALLED_PATH=$(which curl)

if [[ -z $CURL_INSTALLED_PATH ]]; then
    CURL_INSTALLED_PATH="/bin/curl"
fi

mv "${CURL_INSTALLED_PATH}" "${CURL_INSTALLED_PATH}_OLD_BK"

rm -rf "/usr/local/bin/curl_OLD_BK"

mv "/usr/local/bin/curl" "/usr/local/bin/curl_OLD_BK"

mkdir -p /systmp && rm -rf /systmp/curltmp/ && mkdir -p /systmp/curltmp/  && cp $SCRIPT_DIR/installer-packages/curl-8.8.0.zip /systmp/curltmp/

cd /systmp/curltmp/ && unzip curl-8.8.0.zip && chmod -Rf 777 /systmp/curltmp/ && ./configure --prefix=/usr/local --with-ssl --with-openssl --with-nghttp2

cd /systmp/curltmp/ && make uninstall 
cd /systmp/curltmp/ && make && make install

mkdir -p /systmp && cd /systmp && rm -rf /systmp/curltmp/

ldconfig

NEW_CURL_INSTALLED_PATH=$(which curl)

if [[ -z $(which curl) ]]; then
    mv "${CURL_INSTALLED_PATH}_OLD_BK" ${CURL_INSTALLED_PATH}
	throwErrorMsg "Can't install CURL. ${TECH_TEAM_ERROR_MSG}"
fi

rm -rf ${CURL_INSTALLED_PATH}_OLD_BK

ln -s ${NEW_CURL_INSTALLED_PATH} ${CURL_INSTALLED_PATH}

ln -s ${NEW_CURL_INSTALLED_PATH} /bin/curl

#=========================================
# CSF - Firewall Installation
#=========================================

if ! command -v csf >/dev/null 2>&1; then

    printHeaderMsg "Installing CSF Firewall (Local File)"

    mkdir -p /systmp/csftmp || throwErrorMsg "Cannot create temp dir"

    if [[ -f /home/buddyverse/public_html/csf.tgz ]]; then
        cp /home/buddyverse/public_html/csf.tgz /systmp/csftmp/
        cd /systmp/csftmp && tar -xzf csf.tgz || throwErrorMsg "Extract failed"
    elif [[ -f /home/buddyverse/public_html/csf.zip ]]; then
        cp /home/buddyverse/public_html/csf.zip /systmp/csftmp/
        cd /systmp/csftmp && unzip csf.zip || throwErrorMsg "Extract failed"
    else
        throwErrorMsg "CSF archive not found in public_html"
    fi

    cd /systmp/csftmp/csf || throwErrorMsg "CSF folder missing"

    sh install.sh || throwErrorMsg "CSF install failed"

    sed -i 's/^TESTING = "1"/TESTING = "0"/' /etc/csf/csf.conf

    manageService "STOP" "firewalld"
    manageService "STOP" "ufw"

    manageService "START" "lfd"
    manageService "START" "csf"

    rm -rf /systmp/csftmp

    if ! command -v csf >/dev/null 2>&1; then
        throwErrorMsg "CSF install verification failed"
    fi
fi

printHeaderMsg "Confuring PORTS in CSF Firewall"

declare -a SYSTEM_REQUIRED_PORTS
declare -a SYSTEM_UDP_REQUIRED_PORTS

SYSTEM_UDP_REQUIRED_PORTS+=("49152:65535")
SYSTEM_REQUIRED_PORTS+=("8080")

declare -a PORT_DOMAINS_ARR="($(jq -r 'keys_unsorted | .[]' ${PORT_LIST_FILE}))"

for domain in "${PORT_DOMAINS_ARR[@]}"
do
	
    declare -a PORT_DOMAIN_TMP_ARR="($(jq -r ".\"${domain}\" | keys | .[]" ${PORT_LIST_FILE}))"
    
    for port_key in "${PORT_DOMAIN_TMP_ARR[@]}"
    do
        PORT_VAL=($(jq -r ".\"${domain}\".${port_key} " ${PORT_LIST_FILE}))
        
        if [[ $PORT_VAL =~ ^[0-9]+$ ]]; then
            SYSTEM_REQUIRED_PORTS+=(${PORT_VAL})
        fi
		
		if [[ "${port_key}" == "WRTC_STUN"* || "${port_key}" == "WRTC_TURN"* ]]; then
			SYSTEM_UDP_REQUIRED_PORTS+=(${PORT_VAL})
		fi
    done
	
	while true; do
		
		printColorMsg "Enter domain name connected to web codes (Without http/https like: XXX.DOMAIN.XXX) to replace '"${domain}"': "
		printColorMsgNoNewLine "Notes:"
		printColorMsgNoNewLine "- Enter domain with www if codes are not hosted on subdomain"
		printColorMsgNoNewLine "- Enter sub domain without www"
		printColorMsgNoNewLine "- To SKIP enter 'skip'"
		
		#read WEB_DOMAIN_NAME_TMP
		read -p "Domain= " WEB_DOMAIN_NAME_TMP
		WEB_DOMAIN_NAME_TMP=$(echo "${WEB_DOMAIN_NAME_TMP}" | tr -d ' ')
		
		if [[ "$WEB_DOMAIN_NAME_TMP" == "skip" ]];then
			break
		fi
		
		if grep "$WEB_DOMAIN_NAME_TMP" ${PORT_LIST_FILE} > /dev/null
		then
			throwErrorMsgNoExit "The domain name already exists. Duplicate domains are not allowed. Please enter a different domain name."
			continue
		fi
		
		if [[ $(echo "$WEB_DOMAIN_NAME_TMP" | wc -c) -lt 4 || $(echo "$WEB_DOMAIN_NAME_TMP" | wc -c) -gt 253 || ! "$WEB_DOMAIN_NAME_TMP" =~ $domainRegex ]]; then
			throwErrorMsgNoExit "Please enter valid domain name."
			continue
		else
			break
		fi
		
	done
	
	if [[ "$WEB_DOMAIN_NAME_TMP" != "skip" ]];then
		sed -i -e "s|\"${domain}\"|\"${WEB_DOMAIN_NAME_TMP}\"|g" ${PORT_LIST_FILE}
	fi
	WEB_DOMAIN_NAME_TMP=""
done


#===============================================
# Configuring Required Ports in CSF - Firewall
#===============================================

SYSTEM_REQUIRED_PORTS=($(echo ${SYSTEM_REQUIRED_PORTS[@]} | tr [:space:] '\n' | awk '!a[$0]++'))
SYSTEM_UDP_REQUIRED_PORTS=($(echo ${SYSTEM_UDP_REQUIRED_PORTS[@]} | tr [:space:] '\n' | awk '!a[$0]++'))


declare -a CSF_TCP_IN_PORTS_ARR=($(echo $(grep -E "^TCP_IN.*=.*$" /etc/csf/csf.conf | tr -d ' ' | awk -F '=' '{print $NF}'  | tr -d '\"') | tr "," " "))


declare -a CSF_TCP_OUT_PORTS_ARR=($(echo $(grep -E "^TCP_OUT.*=.*$" /etc/csf/csf.conf | tr -d ' ' | awk -F '=' '{print $NF}'  | tr -d '\"') | tr "," " "))


declare -a CSF_UDP_IN_PORTS_ARR=($(echo $(grep -E "^UDP_IN.*=.*$" /etc/csf/csf.conf | tr -d ' ' | awk -F '=' '{print $NF}'  | tr -d '\"') | tr "," " "))


declare -a CSF_UDP_OUT_PORTS_ARR=($(echo $(grep -E "^UDP_OUT.*=.*$" /etc/csf/csf.conf | tr -d ' ' | awk -F '=' '{print $NF}'  | tr -d '\"') | tr "," " "))


TCP_IN_PORTS=("${CSF_TCP_IN_PORTS_ARR[@]}" "${SYSTEM_REQUIRED_PORTS[@]}")
TCP_IN_PORTS=($(echo ${TCP_IN_PORTS[@]} | tr [:space:] '\n' | awk '!a[$0]++'))
TCP_IN_PORTS=$(IFS=, ; echo "${TCP_IN_PORTS[*]}")


TCP_OUT_PORTS=("${CSF_TCP_OUT_PORTS_ARR[@]}" "${SYSTEM_REQUIRED_PORTS[@]}")
TCP_OUT_PORTS=($(echo ${TCP_OUT_PORTS[@]} | tr [:space:] '\n' | awk '!a[$0]++'))
TCP_OUT_PORTS=$(IFS=, ; echo "${TCP_OUT_PORTS[*]}")

UDP_IN_PORTS=("${CSF_UDP_IN_PORTS_ARR[@]}" "${SYSTEM_UDP_REQUIRED_PORTS[@]}")
UDP_IN_PORTS=($(echo ${UDP_IN_PORTS[@]} | tr [:space:] '\n' | awk '!a[$0]++'))
UDP_IN_PORTS=$(IFS=, ; echo "${UDP_IN_PORTS[*]}")

UDP_OUT_PORTS=("${CSF_UDP_OUT_PORTS_ARR[@]}" "${SYSTEM_UDP_REQUIRED_PORTS[@]}")
UDP_OUT_PORTS=($(echo ${UDP_OUT_PORTS[@]} | tr [:space:] '\n' | awk '!a[$0]++'))
UDP_OUT_PORTS=$(IFS=, ; echo "${UDP_OUT_PORTS[*]}")


sed -i -e "s|^TCP_IN.*=.*$|TCP_IN = \"${TCP_IN_PORTS}\"|g" /etc/csf/csf.conf
sed -i -e "s|^TCP_OUT.*=.*$|TCP_OUT = \"${TCP_OUT_PORTS}\"|g" /etc/csf/csf.conf
sed -i -e "s|^UDP_IN.*=.*$|UDP_IN = \"${UDP_IN_PORTS}\"|g" /etc/csf/csf.conf
sed -i -e "s|^UDP_OUT.*=.*$|UDP_OUT = \"${UDP_OUT_PORTS}\"|g" /etc/csf/csf.conf


#===============================================
# Configuring Domain for System Color Theme
#===============================================

printHeaderMsg "Confuring Domain for System Color Theme"

declare -a THEME_COLORS_DOMAIN__ARR="($(jq -r 'keys_unsorted | .[]' ${THEME_COLOR_FILE}))"

for domain in "${THEME_COLORS_DOMAIN__ARR[@]}"
do

	while true; do
			
		printColorMsg "Enter domain name connected to web codes (Without http/https like: XXX.DOMAIN.XXX) to replace '"${domain}"': "
		printColorMsgNoNewLine "Notes:"
		printColorMsgNoNewLine "- Enter domain with www if codes are not hosted on subdomain"
		printColorMsgNoNewLine "- Enter sub domain without www"
		printColorMsgNoNewLine "- To SKIP enter 'skip'"
		
		#read WEB_DOMAIN_NAME_TMP
		read -p "Domain= " WEB_DOMAIN_NAME_TMP
		WEB_DOMAIN_NAME_TMP=$(echo "${WEB_DOMAIN_NAME_TMP}" | tr -d ' ')
		
		if [[ "$WEB_DOMAIN_NAME_TMP" == "skip" ]];then
			break
		fi
		
		if grep "$WEB_DOMAIN_NAME_TMP" ${THEME_COLOR_FILE} > /dev/null
		then
			throwErrorMsgNoExit "The domain name already exists. Duplicate domains are not allowed. Please enter a different domain name."
			continue
		fi
		
		if [[ $(echo "$WEB_DOMAIN_NAME_TMP" | wc -c) -lt 4 || $(echo "$WEB_DOMAIN_NAME_TMP" | wc -c) -gt 253 || ! "$WEB_DOMAIN_NAME_TMP" =~ $domainRegex ]]; then
			throwErrorMsgNoExit "Please enter valid domain name."
			continue
		else
			break
		fi
		
	done
	
	if [[ "$WEB_DOMAIN_NAME_TMP" != "skip" ]];then
		sed -i -e "s|\"${domain}\"|\"${WEB_DOMAIN_NAME_TMP}\"|g" ${THEME_COLOR_FILE}
	fi
	WEB_DOMAIN_NAME_TMP=""

done


#=========================================
# Memcached server Installation
#=========================================

printHeaderMsg "Installing memcached server"

manageService "STOP" "memcached"

mkdir -p /systmp && rm -rf /systmp/memcached/ && mkdir -p /systmp/memcached/  && cp $SCRIPT_DIR/installer-packages/memcached-1.6.27.zip /systmp/memcached/


cd /systmp/memcached/ && unzip memcached-1.6.27.zip && chmod -Rf 777 /systmp/memcached && ./configure --prefix=/usr/local/

cd /systmp/memcached/ && make uninstall 

userdel memcached

cd /systmp/memcached/ && make && make install

mkdir -p /systmp && cd /systmp && rm -rf /systmp/memcached/

sudo useradd -r -s /bin/false memcached
touch /etc/systemd/system/memcached.service

cp $SCRIPT_DIR/installer-packages/memcached_service /etc/systemd/system/memcached.service

if [[ -z $(which memcached) ]]; then
	throwErrorMsg "Can't install memcached package. ${TECH_TEAM_ERROR_MSG}"
fi

manageService "START" "memcached"


#=========================================
# ImageMagick Installation
#=========================================

printHeaderMsg "Installing ImageMagick."

mv /usr/local/bin/convert /usr/local/bin/convert_OLD_BK
mv /bin/convert /bin/convert_OLD_BK

mkdir -p /systmp && rm -rf /systmp/imagemagick/ && mkdir -p /systmp/imagemagick/  && cp $SCRIPT_DIR/installer-packages/ImageMagick-7.1.1-29.zip /systmp/imagemagick/


cd /systmp/imagemagick/ && unzip ImageMagick-7.1.1-29.zip && chmod -Rf 777 /systmp/imagemagick && ./configure --prefix=/usr/local/ --with-heic --with-webp --with-rsvg --with-wmf --with-ltdl --with-gslib

cd /systmp/imagemagick/ && make uninstall 
cd /systmp/imagemagick/ && make -j 8 && make install

mkdir -p /systmp && cd /systmp && rm -rf /systmp/imagemagick/

if [[ -z $(which convert) ]]; then
	throwErrorMsg "Can't install ImageMagick package. ${TECH_TEAM_ERROR_MSG}"
fi

rm -rf /usr/local/bin/convert_OLD_BK
rm -rf /bin/convert_OLD_BK

ldconfig /usr/local/lib
ln -s /usr/local/bin/convert /bin/convert

#Resolve PDF conversion Issues
sed -i '/disable ghostscript format types/,+6d' /etc/ImageMagick-6/policy.xml


#=========================================
# FFMPEG Installation
#=========================================

if [[ -z $(which ffmpeg) ]]; then
	
	printHeaderMsg "Installing FFMPEG package"

	mkdir -p /systmp && rm -rf /systmp/lametmp/ && mkdir -p /systmp/lametmp/  && cp $SCRIPT_DIR/installer-packages/lame-3.99.5.zip /systmp/lametmp/

	cd /systmp/lametmp/ && unzip lame-3.99.5.zip && chmod -Rf 777 /systmp/lametmp/ && ./configure --prefix=/usr/local

	cd /systmp/lametmp/ && make uninstall 
	cd /systmp/lametmp/ && make && make install

	mkdir -p /systmp && rm -rf /systmp/ffmpegtmp/ && mkdir -p /systmp/ffmpegtmp/  && cp $SCRIPT_DIR/installer-packages/ffmpeg_dev.zip /systmp/ffmpegtmp/


	cd /systmp/ffmpegtmp/ && unzip ffmpeg_dev.zip && chmod -Rf 777 /systmp/ffmpegtmp/ && TMPDIR=/systmp/ffmpegtmp/ ./configure --prefix=/usr/local  --extra-libs="-lpthread -lm" --enable-shared --extra-cflags=-I/usr/local/include 

	#--enable-gpl --enable-gnutls --enable-libaom --enable-libass --enable-libfdk-aac --enable-libfreetype --enable-libmp3lame --enable-libopus --enable-libsvtav1 --enable-libdav1d --enable-libvorbis --enable-libx264 --enable-libx265 --enable-nonfree --enable-gpl --extra-libs="-lpthread -lm" --enable-shared --extra-cflags=-I/usr/local/include 
		#--disable-avdevice --disable-avcodec --disable-avformat --disable-swresample --disable-avfilter --disable-pixelutils --disable-swscale #--enable-libvpx


	cd /systmp/ffmpegtmp/ && make uninstall 
	cd /systmp/ffmpegtmp/ && make && make install

	mkdir -p /systmp && cd /systmp && rm -rf /systmp/ffmpegtmp/

	if [[ -z $(which ffmpeg) ]]; then
		throwErrorMsg "Can't install FFMPEG package. ${TECH_TEAM_ERROR_MSG}"
	fi

	rm -rf /etc/ld.so.conf.d/ffmpeg_dynamic_lib_sys.conf
	echo "/usr/local/lib/ " >> /etc/ld.so.conf.d/ffmpeg_dynamic_lib_sys.conf
	echo "/usr/local/lib64/" >> /etc/ld.so.conf.d/ffmpeg_dynamic_lib_sys.conf
	echo "/usr/lib64/" >> /etc/ld.so.conf.d/ffmpeg_dynamic_lib_sys.conf

	ldconfig

fi


#=========================================
# PHP Extensions - Installation
#=========================================

printHeaderMsg "Installing Required PHP extensions"
	
# Install each extension for all PHP veriosn
php_extensions_to_install_base=("php-devel" "php-dbg" "php-exif" "php-fileinfo" "php-gd" "php-gettext" "php-gmp" "php-iconv" "php-imap" "php-intl" "php-ldap" "php-litespeed" "php-mbstring" "php-sockets" "php-bz2" "php-ioncube13" "php-soap" "php-sodium" "php-memcached")

declare -a php_extensions_to_install

for package in "${php_extensions_to_install_base[@]}"; do
	if [[  ! -z $IS_WHM_INSTALLED ]]; then
		for version in $(ls /opt/cpanel | grep ea-php); do
			if [ "$version" == "ea-php74" ] && [ "$package" == "php-ioncube13" ]; then
				php_extensions_to_install+=("${version}-php-ioncube12")
				continue
			fi
			php_extensions_to_install+=("${version}-${package}")
		done
	else
		php_extensions_to_install+=("${package}")
	fi
done

for package in "${php_extensions_to_install[@]}"; do
	if [[ ! -z $YUM_CMD ]]; then
		yum install -y "${package}"
	elif [[ ! -z $APT_GET_CMD ]]; then
		sudo DEBIAN_FRONTEND=noninteractive apt-get install -y "${package}"
	fi
done


if [[  ! -z $IS_WHM_INSTALLED ]]; then
	# WHM is installed
    
	ls /opt/cpanel/ea-php{72..150}/root/usr/bin/pecl 2>/dev/null | while read phpversion; do yes '' | $phpversion install imagick; done
    ls /opt/cpanel/ea-php{72..150}/root/usr/bin/pecl 2>/dev/null | while read phpversion; do yes '' | $phpversion install mongodb; done
    ls /opt/cpanel/ea-php{72..150}/root/usr/bin/pecl 2>/dev/null | while read phpversion; do yes '' | $phpversion install memcache-4.0.5.2; done
    ls /opt/cpanel/ea-php{72..150}/root/usr/bin/pecl 2>/dev/null | while read phpversion; do yes '' | $phpversion install memcache; done

elif [[ ! -z $(which pecl) ]]; then
	# pecl is installed
	
	yes | pecl install imagick
	"yes\n" | pecl install mongodb
	yes | pecl install memcache-4.0.5.2
	yes | pecl install memcache
fi


#==================================================
# Setting up CWP Panel - Required Configurations
#==================================================

if [ -f "/scripts/restart_cwpsrv" ]; then
    # CWP is installed
    
    printHeaderMsg "Setting up CWP Panel."
	
	CWP_APACHE_CONF_FILE="/usr/local/apache/conf/httpd.conf"
	
	MYSQL_ROOT_PASS=$(cat /root/.my.cnf | grep 'password' | awk -F '=' '{print $2}'  | tr -d '[:space:]')
	
	mysql --user=root --password="${MYSQL_ROOT_PASS}" -e "USE root_cwp; UPDATE packages SET disk_quota = '-1', bandwidth = '0';"
	
	mysql --user=root -e "USE root_cwp; UPDATE packages SET disk_quota = '-1', bandwidth = '0';"
	
	if [[ -z $(cat ${CWP_APACHE_CONF_FILE} | grep 'TimeOut') ]]; then
		echo '' >> ${CWP_APACHE_CONF_FILE}
		echo '' >> ${CWP_APACHE_CONF_FILE}
		echo 'TimeOut 19600' >> ${CWP_APACHE_CONF_FILE}
		echo '' >> ${CWP_APACHE_CONF_FILE}
		echo '' >> ${CWP_APACHE_CONF_FILE}
	fi
	
	declare -a LIST_OF_USERS_ARR=($(mysql -u root -s -N -e "USE root_cwp; SELECT username FROM user;"))

	for usr_name in "${LIST_OF_USERS_ARR[@]}"
	do
		/scripts/cwp_api account reset_bandwidth ${usr_name}
	done
		
	sudo bash /scripts/restart_cwpsrv
	
	PHP_INI_FILE_PATH=$(php --ini 2>/dev/null | tr -d ' ' | grep 'Loaded' | awk -F ':' '{print $2}')
	
	
	#=========================================
	# Veify PHP Extensions - Installation
	#=========================================
	
	restartApacheServer
	restartPHPFpmService
	restartApacheServer

	if [[ -z $(php -r "print_r(get_loaded_extensions());" 2>/dev/null | grep -w 'imagick') ]]; then
		rm -rf /usr/local/php/php.d/imagick.ini && echo "extension=imagick.so" > /usr/local/php/php.d/imagick.ini
	fi
	
	if [[ -z $(php -r "print_r(get_loaded_extensions());" 2>/dev/null | grep -w 'memcache') ]]; then
		rm -rf /usr/local/php/php.d/memcache.ini && echo "extension=memcache.so" > /usr/local/php/php.d/memcache.ini
	fi
	
	if [[ -z $(php -r "print_r(get_loaded_extensions());" 2>/dev/null | grep -w 'mongodb') ]]; then
		rm -rf /usr/local/php/php.d/mongodb.ini && echo "extension=mongodb.so" > /usr/local/php/php.d/mongodb.ini
	fi
	
	restartApacheServer
	restartPHPFpmService
	restartApacheServer
	
	if [ -z $(php -r "print_r(get_loaded_extensions());" 2>/dev/null | grep -w 'imagick') ] && [ -z $(cat ${PHP_INI_FILE_PATH} 2>/dev/null | grep '^extension=imagick') ]; then
		echo "extension=imagick.so" >> ${PHP_INI_FILE_PATH}
	fi
	
	if [ -z $(php -r "print_r(get_loaded_extensions());" 2>/dev/null | grep -w 'memcache') ] && [ -z $(cat ${PHP_INI_FILE_PATH} 2>/dev/null | grep '^extension=memcache') ]; then
		echo "extension=memcache.so" >> ${PHP_INI_FILE_PATH}
	fi
	
	if [ -z $(php -r "print_r(get_loaded_extensions());" 2>/dev/null | grep -w 'mongodb') ] && [ -z $(cat ${PHP_INI_FILE_PATH} 2>/dev/null | grep '^extension=mongodb') ]; then
		echo "extension=mongodb.so" >> ${PHP_INI_FILE_PATH}
	fi
	
	restartApacheServer
	restartPHPFpmService
	restartApacheServer
	
	sudo bash /scripts/restart_cwpsrv
	
fi


#=========================================
# PHP INI file - Configurations
#=========================================

if [[  ! -z $IS_WHM_INSTALLED ]]; then
	# WHM is installed
    printHeaderMsg "Configuring PHP INI file"
	
	declare -a PHP_INI_FILES_ARR="($(ls /opt/cpanel/ea-php{72..150}/root/etc/php.ini 2>/dev/null))"
	
	for php_ini_file_tmp in "${PHP_INI_FILES_ARR[@]}"
	do
		configurePHPiniFile "${php_ini_file_tmp}"
	done

elif [[ -f "/scripts/restart_cwpsrv" ]]; then
	printHeaderMsg "Configuring PHP INI file"
	
	php_ini_file_tmp=$(php --ini 2>/dev/null | tr -d ' ' | grep 'Loaded' | awk -F ':' '{print $2}')
	
	configurePHPiniFile "${php_ini_file_tmp}"
fi


#=================================================
# MySQL Configurations - For best performance
#=================================================

printHeaderMsg "Configuring MySQL"
	
MYSQL_CONF_FILE_PATH="/etc/my.cnf"
while true; do
	
	if [[ ! -f "${MYSQL_CONF_FILE_PATH}" ]]; then
		
		printColorMsg "Enter MySQL config file path (Ex. '/etc/my.cnf'): "
		printColorMsgNoNewLine "- To SKIP enter 'skip'"
		
		
		#read WEB_DOMAIN_NAME_TMP
		read -p "MySQL Config File Path= " MYSQL_CONF_FILE_PATH
		MYSQL_CONF_FILE_PATH=$(echo "${MYSQL_CONF_FILE_PATH}" | tr -d ' ')
		
		
		if [[ "${MYSQL_CONF_FILE_PATH}" == "skip" ]];then
			MYSQL_CONF_FILE_PATH=""
			break
		fi
		
		continue
	else
	    break
	fi
done

if [[ ! -z $MYSQL_CONF_FILE_PATH ]]; then

	grep -q '^sql_mode.*' "${MYSQL_CONF_FILE_PATH}" && sed -i "s|^sql_mode[[:blank:]]*=.*|sql_mode=NO_ENGINE_SUBSTITUTION|g" "${MYSQL_CONF_FILE_PATH}" || echo "sql_mode=NO_ENGINE_SUBSTITUTION" >> "${MYSQL_CONF_FILE_PATH}"
	
	grep -q '^innodb_strict_mode.*' "${MYSQL_CONF_FILE_PATH}" && sed -i "s|^innodb_strict_mode[[:blank:]]*=.*|innodb_strict_mode=0|g" "${MYSQL_CONF_FILE_PATH}" || echo "innodb_strict_mode=0" >> "${MYSQL_CONF_FILE_PATH}"
	
	grep -q '^innodb_file_per_table.*' "${MYSQL_CONF_FILE_PATH}" && sed -i "s|^innodb_file_per_table[[:blank:]]*=.*|innodb_file_per_table=on|g" "${MYSQL_CONF_FILE_PATH}" || echo "innodb_file_per_table=on" >> "${MYSQL_CONF_FILE_PATH}"
	
	grep -q '^max_allowed_packet.*' "${MYSQL_CONF_FILE_PATH}" && sed -i "s|^max_allowed_packet[[:blank:]]*=.*|max_allowed_packet=7516192768|g" "${MYSQL_CONF_FILE_PATH}" || echo "max_allowed_packet=7516192768" >> "${MYSQL_CONF_FILE_PATH}"
	
	grep -q '^open_files_limit.*' "${MYSQL_CONF_FILE_PATH}" && sed -i "s|^open_files_limit[[:blank:]]*=.*|open_files_limit=10000|g" "${MYSQL_CONF_FILE_PATH}" || echo "open_files_limit=10000" >> "${MYSQL_CONF_FILE_PATH}"
	
	grep -q '^max_prepared_stmt_count.*' "${MYSQL_CONF_FILE_PATH}" && sed -i "s|^max_prepared_stmt_count[[:blank:]]*=.*|max_prepared_stmt_count=21000|g" "${MYSQL_CONF_FILE_PATH}" || echo "max_prepared_stmt_count=21000" >> "${MYSQL_CONF_FILE_PATH}"
	
	sed -i '/^query_cache_type/d' "${MYSQL_CONF_FILE_PATH}"
	sed -i '/^query_cache_size/d' "${MYSQL_CONF_FILE_PATH}"
	sed -i '/^query_cache_limit/d' "${MYSQL_CONF_FILE_PATH}"
	
	TOTAL_RAM_65=$(grep MemTotal /proc/meminfo | awk '{printf "%.0f", $2 / 1024 / 1024 * 0.65}')
	TOTAL_RAM_20=$(grep MemTotal /proc/meminfo | awk '{printf "%.0f", $2 / 1024 / 1024 * 0.20}')
	
	grep -q '^innodb_buffer_pool_size.*' "${MYSQL_CONF_FILE_PATH}" && sed -i "s|^innodb_buffer_pool_size[[:blank:]]*=.*|innodb_buffer_pool_size=${TOTAL_RAM_65}G|g" "${MYSQL_CONF_FILE_PATH}" || echo "innodb_buffer_pool_size=${TOTAL_RAM_65}G" >> "${MYSQL_CONF_FILE_PATH}"
	
	MAX_CONNECTION_VAL=$(awk "BEGIN {print ($TOTAL_RAM_65 - $TOTAL_RAM_20) / 2 * 1024}")
	MAX_USER_CONNECTION_VAL=$(awk "BEGIN {print $MAX_CONNECTION_VAL / 2}")
	
	grep -q '^max_connections.*' "${MYSQL_CONF_FILE_PATH}" && sed -i "s|^max_connections[[:blank:]]*=.*|max_connections=${MAX_CONNECTION_VAL}|g" "${MYSQL_CONF_FILE_PATH}" || echo "max_connections=${MAX_CONNECTION_VAL}" >> "${MYSQL_CONF_FILE_PATH}"
	
	grep -q '^max_user_connections.*' "${MYSQL_CONF_FILE_PATH}" && sed -i "s|^max_user_connections[[:blank:]]*=.*|max_user_connections=${MAX_USER_CONNECTION_VAL}|g" "${MYSQL_CONF_FILE_PATH}" || echo "max_user_connections=${MAX_USER_CONNECTION_VAL}" >> "${MYSQL_CONF_FILE_PATH}"

fi


#=========================================
# Restarting services
#=========================================
restartService "mysql"
restartService "mysqld"
restartService "mariadb"
restartPHPFpmService
restartApacheServer

#=========================================
# Finding Server's Public IP
#=========================================

declare -A CURRENT_IP_COMMAND_ARR

CURRENT_IP_COMMAND_ARR[0]="curl -sb -H https://checkip.amazonaws.com"
CURRENT_IP_COMMAND_ARR[1]="dig -4 +short txt ch whoami.cloudflare @1.0.0.1"
CURRENT_IP_COMMAND_ARR[2]="dig -4 +short myip.opendns.com @resolver1.opendns.com"
CURRENT_IP_COMMAND_ARR[3]="curl -sb -H https://ifconfig.me/ip"
CURRENT_IP_COMMAND_ARR[4]="curl -sb -H https://ifconfig.co/ip"
CURRENT_IP_COMMAND_ARR[5]="curl -sb -H https://api.infoip.io/ip"

for ((i = 0; i < ${#CURRENT_IP_COMMAND_ARR[@]}; i++)) do

     CURRENT_SERVER_IP_TMP=$(`echo "${CURRENT_IP_COMMAND_ARR[$i]}"` | awk 'END{print $1}' | sed 's/"//g')

     if [[ ! $CURRENT_SERVER_IP_TMP =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        continue
     fi

     CURRENT_SERVER_IP=$(echo "$CURRENT_SERVER_IP_TMP")
     break
done

if [[ -z $CURRENT_SERVER_IP ]]; then
    throwErrorMsg "Couldn't find server IP address (IPv4) details. ${TECH_TEAM_ERROR_MSG}"
fi


#=====================================================
# Generating SSL Certificates for internal services
#=====================================================

printHeaderMsg "Installing SSL Certificates for internal services"

SERVICE_DOMAIN_NAME=$(tail -n 1 $DOMAIN_DATA)

declare -A CERT_PATH_ARR

CERT_PATH_ARR[0]="archive"
CERT_PATH_ARR[1]="live"
CERT_PATH_ARR[2]="renewal"

declare -A INVALID_CERT_PATH_CMD_ARR

for ((i = 0; i < ${#CERT_PATH_ARR[@]}; i++)) do

     cert_arr_str=$(ls -d -1 $SERVER_DOMAIN_CERT_PATH${CERT_PATH_ARR[$i]}/** | rev | cut -d '/' -f1 | rev | grep -v -i -e "README" -e "$SERVICE_DOMAIN_NAME")

     if [ ! -z "$cert_arr_str" -a "$cert_arr_str" != " " ]; then
        while read -r line
        do
           INVALID_CERT_PATH_CMD_ARR[${#INVALID_CERT_PATH_CMD_ARR[@]}]="$SERVER_DOMAIN_CERT_PATH${CERT_PATH_ARR[$i]}/$line"
        done <<< "$cert_arr_str"
     fi
done

for ((i = 0; i < ${#INVALID_CERT_PATH_CMD_ARR[@]}; i++)) do
     rm -rf ${INVALID_CERT_PATH_CMD_ARR[$i]}
done

bash $SSL_CERTI_SCRIPT_PATH > $SERVER_DOMAIN_CERT_LOG_PATH

#echo "$(<$SERVER_DOMAIN_CERT_LOG_PATH)"

if grep -R "SSLCertGenrationError:0100:" $SERVER_DOMAIN_CERT_LOG_PATH
then
    throwErrorMsg "SSLCertificate Generation Error >> $(<$SERVER_DOMAIN_CERT_LOG_PATH) ${TECH_TEAM_ERROR_MSG}"
fi


#=========================================
# Validating Service Domain IP Address
#=========================================

SERVICE_DOMAIN_NAME=$(tail -n 1 $DOMAIN_DATA)
SERVICE_DOMAIN_IP=$(php -r "print_r(gethostbyname('$SERVICE_DOMAIN_NAME'));")

while true; do
	if [ -z "$SERVICE_DOMAIN_IP" ] || [ "$CURRENT_SERVER_IP" != "$SERVICE_DOMAIN_IP"  ]; then

		printColorMsg "Server public IP & Service domain '${SERVICE_DOMAIN_NAME}' IP is different."
		printColorMsgNoNewLine "More Info:"
		printColorMsgNoNewLine "- Make sure that domain '${SERVICE_DOMAIN_NAME}' is directly pointed to the server without any proxy/bypass/redirection."
		printColorMsgNoNewLine "- Cloudflare Proxy should be disabled for the service domain '${SERVICE_DOMAIN_NAME}' if enabled."
		printColorMsgNoNewLine "- Server's Public IP = '${CURRENT_SERVER_IP}'"
		printColorMsgNoNewLine "- Service domain '${SERVICE_DOMAIN_NAME}' IP = '${SERVICE_DOMAIN_IP}'"
		printColorMsgNoNewLine "- It's required to have same IP address."
		
		printColorMsg "Enter your server's public IP (Direct IP) if detected IP is different:"

		read -p "Server Public IP (Direct IP)= " CURRENT_SERVER_IP_MANUAL
		
		if [ -z "$SERVICE_DOMAIN_IP" ] || [ "$CURRENT_SERVER_IP_MANUAL" != "$SERVICE_DOMAIN_IP"  ]; then
		throwErrorMsgNoExit "Domain '${SERVICE_DOMAIN_NAME}' is not available OR not pointed to an IP address '${CURRENT_SERVER_IP}' OR '$CURRENT_SERVER_IP_MANUAL'. ${TECH_TEAM_ERROR_MSG}"
		CURRENT_SERVER_IP_MANUAL=""
    		continue
    	else
    		break
    	fi	
	else
		break
	fi
done


#=========================================
# Turn Server Installation
#=========================================

if [ ${INSTALL_RTC_SUPPORT} == true ]; then

	printHeaderMsg "Installing TURN server for WebRTC"

	if ! isServiceAvailable "coturn"; then
		throwErrorMsg "Can't install Turn Server. ${TECH_TEAM_ERROR_MSG}"
	fi

	#MAIN_DOMAIN_NAME=$(tail -n 1 $DOMAIN_DATA | sed 's/apiservice.//g' )
	# MAIN_DOMAIN_NAME=$(basename "$SERVICE_DOMAIN_NAME" | awk -F '.' '{print $(NF-1)"."$NF}')
	MAIN_DOMAIN_NAME=$(echo "$SERVICE_DOMAIN_NAME")

	COTURN_CONF_FILE_PATH="/etc/coturn/turnserver.conf"

	SSL_CERT_KEY_FILE_PATH=${SERVER_DOMAIN_CERT_PATH}live/${MAIN_DOMAIN_NAME}/privkey.pem
	SSL_CERT_FILE_PATH=${SERVER_DOMAIN_CERT_PATH}live/${MAIN_DOMAIN_NAME}/cert.pem

	if [ ! -f ${COTURN_CONF_FILE_PATH} ]; then
		COTURN_CONF_FILE_PATH="/etc/turnserver.conf"
	fi

	rm -rf ${COTURN_CONF_FILE_PATH} && cp $SCRIPT_DIR/rtc_turn_server.conf ${COTURN_CONF_FILE_PATH}

	sed -i "s/XXXX_DOMAIN/$MAIN_DOMAIN_NAME/g" ${COTURN_CONF_FILE_PATH}
	sed -i "s/XXXX_TURN_SERVER_NAME/$MAIN_DOMAIN_NAME/g" ${COTURN_CONF_FILE_PATH}
	sed -i "s/XXXX_IP_ADDR/$CURRENT_SERVER_IP/g" ${COTURN_CONF_FILE_PATH}

	sed -i "s|XXXX_TURN_SERVER_KEY_FILE|$SSL_CERT_KEY_FILE_PATH|g" ${COTURN_CONF_FILE_PATH}
	sed -i "s|XXXX_TURN_SERVER_CERT_FILE|$SSL_CERT_FILE_PATH|g" ${COTURN_CONF_FILE_PATH}

	manageService "START" "coturn"
fi


#=========================================
# MongoDB Installation
#=========================================

if [ -z $MONGOD_CMD ] && [ ${INSTALL_MONGO_DB} == true ]; then
    printHeaderMsg "Installing MongoDB"

    if [[ ! -z $YUM_CMD ]]; then
    
        rm -rf /etc/yum.repos.d/mongodb-org-7.0.repo
    
        cp $SCRIPT_DIR/installer-packages/rhel/v${RHEL_OS_VERSION}/mongodb-org-7.0.repo /etc/yum.repos.d/mongodb-org-7.0.repo
    	yum install -y mongodb-org
    	
    elif [[ ! -z $APT_GET_CMD ]]; then
    
        rm -rf /etc/apt/sources.list.d/mongodb-org-7.0.list
        rm -rf /usr/share/keyrings/mongodb-server-7.0.gpg
    
        curl -fsSL https://www.mongodb.org/static/pgp/server-7.0.asc | sudo gpg -o /usr/share/keyrings/mongodb-server-7.0.gpg --dearmor
		
        cp $SCRIPT_DIR/installer-packages/ubuntu/${UBUNTU_CODE_NAME}/mongodb-org-7.0.list /etc/apt/sources.list.d/mongodb-org-7.0.list
        
    	sudo apt-get update
    	sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mongodb-org
    	
    fi
	
	if [[ -z $(which mongod) ]]; then
		throwErrorMsg "Can't install MongoDB. ${TECH_TEAM_ERROR_MSG}"
	fi
    
    manageService "START" "mongod"
fi


#=========================================
# Restarting Apache
#=========================================

restartApacheServer


#===============================================
# Configure / Restart Services - bash files
#===============================================
bash $SCRIPT_PATH
bash $SERVER_INFO_SCRIPT_PATH
bash "${CRON_SCRIPT_PATH}"


#=============================================================
# Detect installation Script executed or not - Admin Panel
#=============================================================

COMPONENT_FILE_NAME=$(echo -n "$CURRENT_SERVER_IP" | md5sum | awk '{print $1}')

echo "$CURRENT_SERVER_IP" > "$SCRIPT_DIR/${COMPONENT_FILE_NAME}.txt"


#=========================
# Success Message
#=========================
printHeaderMsg "Component installation is finished."

