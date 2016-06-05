FROM durhamatletico/durhamatletico
MAINTAINER Kosta Harlan <kosta@durhamatletico.com>

COPY . /var/www/html
RUN chown -R www-data:www-data /var/www
RUN chmod -R 777 /var/www
