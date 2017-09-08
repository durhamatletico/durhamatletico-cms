#!/bin/bash

set -ex

docker-compose down | true
docker volume rm durhamatletico_mysql-data
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
echo "Importing backup"
docker-compose up -d

echo "Waiting for database to import"
while true;
do
  status=`curl -s -k -o /dev/null -Ik -w "%{http_code}" https://local.durhamatletico.com`

  if [ $status -eq "200" ]; then
    docker-compose exec -T php drush cr -yv
    if [ "$CI" = true ] ; then
      docker-compose exec -T php drush config-import -yv
    fi
    docker-compose exec -T php drush updb -yv
    docker-compose exec -T php drush cr -yv
    echo "Ready for testing!"
    break
  else
    printf ".";
    sleep 15;
  fi
done
