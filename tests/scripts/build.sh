#!/bin/bash

set -e

# Get Pantheon's Command Line Tool, terminus.
sudo curl https://github.com/pantheon-systems/cli/releases/download/0.11.1/terminus.phar -L -o /usr/local/bin/terminus && sudo chmod +x /usr/local/bin/terminus

# Log into terminus.
terminus auth login $PANTHEON_EMAIL --password=$PANTHEON_PASSWORD

echo "Creating backup"
terminus site backups create --element=database --site=durham-atletico --env=live
echo "Downloading backup"
terminus site backups get --element=db --site=durham-atletico --env=live --to=database.sql.gz --latest
rm database.sql
echo "y" | gunzip database.sql.gz
echo "Importing backup"
docker exec -i durhamatletico_db mysql -uroot -proot durhamatletico_docker < database.sql

# Clear cache
docker-compose exec php drush cr -yv
docker-compose exec php drush config-import -yv

echo "Ready for testing!"
