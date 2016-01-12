#!/bin/bash

set -e

# Get Pantheon's Command Line Tool, terminus.
sudo curl https://github.com/pantheon-systems/cli/releases/download/0.10.0/terminus.phar -L -o /usr/local/bin/terminus && sudo chmod +x /usr/local/bin/terminus

# Log into terminus.
terminus auth login $PANTHEON_EMAIL --password=$PANTHEON_PASSWORD

# Get a dump from production
terminus site backups get --element=db --site=durham-atletico --env=live --to=database.sql.gz --latest

# Import the DB
echo "CREATE database durhamatletico_circle;" | mysql -uroot
gunzip database.sql.gz
pv database.sql | mysql -u ubuntu circle_test

# TODO: Get files?

# Install Drush
sudo curl https://github.com/drush-ops/drush/releases/download/8.0.1/drush.phar -L -o /usr/local/bin/drush && sudo chmod +x /usr/local/bin/drush

# Overwrite settings.local.php
sudo mv tests/scripts/settings.local.php sites/default/settings.local.php

# Clear cache
drush cr -yv
drush config-import -yv

drush runserver --server=builtin --strict=0 </dev/null &>$HOME/server.log &

# Copy Behat local config.
cp tests/scripts/behat.local.yml tests/behat.local.yml

echo "Ready for testing!"
