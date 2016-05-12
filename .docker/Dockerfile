FROM php:5.5.35-apache
MAINTAINER Kosta Harlan <kosta@durhamatletico.com>

RUN sed -i s/httpredir.debian.org/http.us.debian.org/g /etc/apt/sources.list \
    && apt-get clean \
    && apt-get update \
    && DEBIAN_FRONTEND="noninteractive" apt-get install -y --force-yes \
    git \
    unzip \
    php5-mysql \
    php5-json \
    mysql-client \
    libz-dev \
    libmemcached-dev \
    libfreetype6-dev \
    libmcrypt-dev \
    libjpeg62-turbo-dev \
    libpng12-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) mcrypt \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install memcached \
    && pecl install zip \
    && docker-php-ext-enable memcached

RUN a2enmod rewrite session && service apache2 restart

RUN php -r "readfile('http://files.drush.org/drush.phar');" > /tmp/drush \
    && php /tmp/drush core-status \
    && chmod +x /tmp/drush \
    && mv /tmp/drush /usr/local/bin

EXPOSE 80
