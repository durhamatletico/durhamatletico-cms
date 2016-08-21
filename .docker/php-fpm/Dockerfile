# Docker container for a PHP-FPM application server
# http://php.net
#

FROM centos:centos7
MAINTAINER Kosta Harlan <kosta@durhamatletico.com>

ENV REPO http://dl.iuscommunity.org/pub/ius/stable/CentOS/\$releasever/\$basearch/
ENV PHP php56u-xdebug php56u-cli php56u-fpm php56u-gd php56u-ldap php56u-mbstring php56u-mysql php56u-pdo php56u-xml mysql git curl tar which nc
ENV PHPCONF /etc/php-fpm.d/www.conf

RUN echo -e "\
[ius]\n\
name=IUS Community Repository\n\
baseurl=$REPO\n\
enabled=1\n\
gpgcheck=0\n\
priority=1\n\
" >> /etc/yum.repos.d/ius.repo

RUN echo -e "\
[EPEL]\n\
name=Extra Packages for Enterprise Linux \$releasever - \$basearch\n\
#baseurl=http://download.fedoraproject.org/pub/epel/\$releasever/\$basearch\n\
mirrorlist=https://mirrors.fedoraproject.org/metalink?repo=epel-\$releasever&arch=\$basearch\n\
failovermethod=priority\n\
enabled=1\n\
gpgcheck=0\n\
" >> /etc/yum.repos.d/epel.repo

RUN yum install -y $PHP; yum clean all

ADD php.ini /etc/php.ini

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
    && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
    && echo -e "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707" > /etc/php.d/blackfire.ini

# Xdebug settings including OS X fix explained at https://forums.docker.com/t/ip-address-for-xdebug/10460/5.
RUN echo -e "\
xdebug.remote_enable=1\n\
xdebug.idekey=PHPSTORM\n\
xdebug.remote_host=\"10.254.254.254\"\n\
xdebug.remote_connect_back=0\n\
" >> /etc/php.d/15-xdebug.ini

# Drush
RUN php -r "readfile('http://files.drush.org/drush.phar');" > /tmp/drush \
    && chmod +x /tmp/drush \
    && mv /tmp/drush /usr/local/bin

# Drupal Console
RUN curl https://drupalconsole.com/installer -L -o drupal.phar \
    && chmod +x drupal.phar \
    && mv drupal.phar /usr/local/bin/drupal

# Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Terminus
RUN curl https://github.com/pantheon-systems/cli/releases/download/0.11.1/terminus.phar -L -o /usr/local/bin/terminus && chmod +x /usr/local/bin/terminus

ADD durhamatletico.aliases.drushrc.php /etc/drush/
ADD settings.php /conf/dtdrupal_db.php

RUN useradd -u 48 -U --home /var/www -M --shell /sbin/nologin apache
RUN sed -i '/^listen\ =\ 127.0.0.1/c\listen = 9000' $PHPCONF
RUN sed -i '/^listen.allowed_clients/c\; listen.allowed_clients = ' $PHPCONF
RUN sed -i '/^expose_php = On/c\expose_php = Off' $PHPCONF
RUN sed -i '/^user = php-fpm/c\user = apache' $PHPCONF
RUN sed -i '/^group = php-fpm/c\group = apache' $PHPCONF
RUN sed -i '/^;access.log = log\/$pool.access.log/c\access.log = /var/log/php-fpm/access.log' $PHPCONF
ADD run-phpfpm.sh /run-phpfpm.sh

EXPOSE 9000

WORKDIR /var/www/html

ENTRYPOINT [ "/run-phpfpm.sh" ]
