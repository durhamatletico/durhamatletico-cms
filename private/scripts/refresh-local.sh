#!/bin/bash

set -e

echo "Creating backup"
terminus backup:create --element=database durham-atletico.live
echo "Downloading backup"
DB_URL=$(terminus backup:get --element=db durham-atletico.live)
curl -L -o database.sql.gz $DB_URL
if [ -f database.sql ]; then rm database.sql; fi
# Drop local DB
docker exec -i durhamatletico_php_1 drush -r /var/www/html/web sql-drop -y
echo "y" | gunzip database.sql.gz
echo "Importing backup"
pv database.sql | docker exec -i durhamatletico_mariadb_1 mysql -uroot -proot durhamatletico_docker
