#!/bin/bash

set -e

echo "Creating backup"
terminus site backups create --element=database --site=durham-atletico --env=live
echo "Downloading backup"
terminus site backups get --element=db --site=durham-atletico --env=live --to=database.sql.gz --latest
rm database.sql
# Drop local DB
docker exec -i durhamatletico_php drush sql-drop -y
echo "y" | gunzip database.sql.gz
echo "Importing backup"
pv database.sql | docker exec -i durhamatletico_db mysql -uroot -proot durhamatletico_docker
