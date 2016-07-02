#!/bin/bash

set -x

check_file_exists () {
  local file="$1"

  if [[ ! -f $file ]] ; then
    return 1
  fi

return 0

}

error () {
  local message="$1"
  local date="$(date '+%Y-%m-%dT%H:%M')"

  echo "${date} - ${message}"
  exit 1
}

copy_includes() {
  local include_file='/conf/httpd-includes'

  if check_file_exists $include_file ; then
    cp $include_file /etc/httpd/conf.d/
  fi

  return 0
}

create_log_dir () {
  local log_dir="/var/log/httpd"

  if [[ ! -d ${log_dir} ]] ; then
    mkdir -p ${log_dir}
    chown -R apache.root ${log_dir}
    chmod 0755 ${log_dir}
  fi

  return 0
}


main () {

  copy_includes \
  && create_log_dir

  if [ -f /etc/sysconfig/httpd ]; then
    . /etc/sysconfig/httpd
  fi

  # Pid is managed by runit
  HTTPD_PID='/var/run/httpd/httpd.pid'
  if [[ -f $HTTPD_PID ]]
    then rm $HTTPD_PID
  fi

  # Set the MPM.  Fallback to prefork if it's unset or not one of "event" or "worker"
  # The "x"s are a cheap way to validate a little bit
  if [[ ${HTTPDMPM}x == "eventx" ]] || [[ ${HTTPDMPM}x == "workerx" ]]
    then echo "LoadModule mpm_${HTTPDMPM}_module modules/mod_mpm_${HTTPDMPM}.so" > $MPMCONF
  else
    HTTPDMPM="prefork"
    echo "LoadModule mpm_${HTTPDMPM}_module modules/mod_mpm_${HTTPDMPM}.so" > $MPMCONF
  fi

  if [[ -z $SITENAME ]]
    then echo "SITENAME not specified; continuing with best guess."
  else
    echo '  ServerName ${SITENAME}' > /etc/httpd/conf.d/sitename.conf
  fi

  if [[ -z $SERVER_ALIASES ]]
    then echo "SERVER_ALIASES not specificed."
  else
    echo '  ServerAlias ${SERVER_ALIASES}'  > /etc/httpd/conf.d/serveraliases.includes
  fi

  exec /usr/sbin/httpd -DFOREGROUND

}

main
