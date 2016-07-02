#!/bin/bash

LOGDIR='/var/log/php-fpm'
THREADYAML='/conf/threads.yaml'
HOSTCONF='/etc/php-fpm.d/www.conf'

f_set_threads () {
  if [[ $1 == '--default' ]] ; then
    MAX_CHILD='20'
    MIN_SPARE='5'
    MAX_SPARE='10'
    MAX_REQUESTS='500'
  else
    MAX_CHILD="$(awk '/fpm_maxchild/ {print $2}' $THREADYAML)"
    MIN_SPARE="$(awk '/fpm_minspare/ {print $2}' $THREADYAML)"
    MAX_SPARE="$(awk '/fpm_maxspare/ {print $2}' $THREADYAML)"
    MAX_REQUESTS="$(awk '/fpm_maxreqperchild/ {print $2}' $THREADYAML)"
  fi
  sed -i 's/^pm.start_servers/;pm.start_servers/' $HOSTCONF
  sed -i "s/.*pm.max_children\ =\ .*/pm.max_children = $MAX_CHILD/" $HOSTCONF
  sed -i "s/.*pm.min_spare_servers\ =\ .*/pm.min_spare_servers = $MIN_SPARE/" $HOSTCONF
  sed -i "s/.*pm.max_spare_servers\ =\ .*/pm.max_spare_servers = $MAX_SPARE/" $HOSTCONF
  sed -i "s/.*pm.max_requests\ =\ .*/pm.max_requests = $MAX_REQUESTS/" $HOSTCONF
}

# Make the log dirs (in case we mount volumes from elsewhere)
if [[ ! -d $LOGDIR ]] ; then
  mkdir -p $LOGDIR
fi

for file in error.log access.log
  do if [[ ! -f ${LOGDIR}/${file} ]]
    then touch ${LOGDIR}/${file}
  fi
done

chown -R apache.root $LOGDIR

# Setup the thread count
if [[ -f $THREADYAML ]] ; then
  f_set_threads
else
  f_set_threads --default
fi

exec /usr/sbin/php-fpm -F
