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

echo "Waiting for database to import"
sleep 120