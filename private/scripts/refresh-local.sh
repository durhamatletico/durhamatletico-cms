#!/bin/bash

set -e

echo "Creating backup"
terminus site backups create --element=database --site=durham-atletico --env=live
echo "Downloading backup"
terminus site backups get --element=db --site=durham-atletico --env=live --to=database.sql.gz --latest
rm database.sql
echo "y" | gunzip database.sql.gz
echo "Importing backup"
vagrant ssh -c "cd /var/www/durhamatletico; pv database.sql | $(drush sql-connect)"
