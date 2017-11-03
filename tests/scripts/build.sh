#!/bin/bash

set -ex

DRUSH="docker-compose exec --user 82 -T php vendor/bin/drush -r /var/www/html/web"
docker-compose down | true
docker volume rm durhamatletico_mysql-data | true
docker volume create --name=durhamatletico_terminus_data
docker-compose run --rm --entrypoint="sh -c" terminus "mkdir -p /terminus/cache/tokens"
docker-compose run --rm terminus auth:login --machine-token=$PANTHEON_TOKEN
docker-compose run --rm terminus site:info durham-atletico
docker-compose run --rm --entrypoint=rm terminus '/terminus/cache/database.sql.gz' | true
docker-compose run --rm --entrypoint=rm terminus '/terminus/cache/database.sql' | true
echo "Creating backup"
docker-compose run --rm terminus backup:create durham-atletico.live -n --element=db --keep-for=1
echo "Downloading backup"
docker-compose run --rm terminus backup:get durham-atletico.live -n --element=db --to=/terminus/cache/database.sql.gz
docker-compose run --rm --entrypoint=gunzip terminus /terminus/cache/database.sql.gz
docker-compose up -d
docker-compose exec --user 82 php composer install -n --prefer-dist
echo "Waiting for database to import"
sleep 120
$DRUSH cr
$DRUSH updb -yv
if [ "$CI" = true ] ; then
    $DRUSH cim -yv
fi
$DRUSH cr
$DRUSH uli
