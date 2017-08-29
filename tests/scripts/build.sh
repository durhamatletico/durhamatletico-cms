#!/bin/bash

# Log into terminus.
docker-compose exec php terminus auth login $PANTHEON_EMAIL --password=$PANTHEON_PASSWORD

echo "Creating backup"
docker-compose exec php terminus site backups create --element=database --site=durham-atletico --env=live
echo "Downloading backup"
docker-compose exec php terminus site backups get --element=db --site=durham-atletico --env=live --to=database.sql.gz --latest
rm database.sql
echo "y" | gunzip database.sql.gz
echo "Importing backup"
docker-compose exec -T mariadb mysql -uroot -proot durhamatletico_docker < database.sql

# Clear cache
docker-compose exec php drush cr -yv
docker-compose exec php drush config-import -yv
docker-compose exec php drush updb -yv
docker-compose exec php drush cr -yv

echo "Ready for testing!"
