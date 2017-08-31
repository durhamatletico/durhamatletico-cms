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
docker-compose run --rm terminus backup:get durham-atletico.live -n --element=db --to=/terminus/cache/database.sql.gz
docker-compose run --rm --entrypoint=gunzip terminus /terminus/cache/database.sql.gz
echo "Importing backup"
docker-compose up -d

echo "\033[33mWebsite will auto-open when its up. Checking status...\033[0m";

while true;
do
  status=`curl -s -k -o /dev/null -Ik -w "%{http_code}" https://local.durhamatletico.com`

  if [ $status -eq "200" ]; then
    echo "\033[32mYuhoo! Website is up!\033[0m"
    docker-compose exec php drush cr -yv
    docker-compose exec php drush config-import -yv
    docker-compose exec php drush updb -yv
    docker-compose exec php drush cr -yv

    echo "Ready for testing!"

    break;
  else
    printf ".";
    sleep 4;
  fi
done
