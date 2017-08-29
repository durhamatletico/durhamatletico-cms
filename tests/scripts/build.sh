#!/bin/bash

set -ex
# Log into terminus.
docker volume create --name=durhamatletico_terminus_data
docker-compose run --rm --entrypoint="sh -c" terminus "mkdir -p /terminus/cache/tokens"
docker-compose run --rm terminus auth:login --machine-token=$PANTHEON_TOKEN
docker-compose run --rm terminus site:info durham-atletico

echo "Creating backup"
docker-compose run --rm terminus backup:create durham-atletico.live -n --element=db --keep-for=1
echo "Downloading backup"
docker-compose run --rm terminus backup:get durham-atletico.live -n --element=db --to=database.sql.gz
echo "y" | gunzip database.sql.gz
echo "Importing backup"
docker-compose exec -T mariadb mysql -uroot -proot durhamatletico_docker < database.sql

# Clear cache
docker-compose exec php drush cr -yv
docker-compose exec php drush config-import -yv
docker-compose exec php drush updb -yv
docker-compose exec php drush cr -yv

echo "Ready for testing!"
