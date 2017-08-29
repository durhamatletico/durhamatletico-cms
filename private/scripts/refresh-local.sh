#!/bin/bash

set -e

echo "Creating backup"
terminus backup:create --element=database durham-atletico.live
echo "Downloading backup"
DB_URL=$(terminus backup:get --element=db durham-atletico.live)
curl -L -o database.sql.gz $DB_URL
rm database.sql
# Drop local DB
docker exec -i durhamatletico_php drush sql-drop -y
echo "y" | gunzip database.sql.gz
echo "Importing backup"
pv database.sql | docker exec -i durhamatletico_db mysql -uroot -proot durhamatletico_docker
