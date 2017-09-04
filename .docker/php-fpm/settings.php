<?php

// Set your local database array.
$databases['default']['default'] = array(
  'database' => 'durhamatletico_docker',
  'username' => 'root',
  'password' => 'root',
  'prefix' => '',
  'host' => 'mariadb',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$settings['hash_salt'] = 'abcdefg';

$settings['trusted_host_patterns'] = array(
  '^localhost$',
  '^nginx$',
  '^0.0.0.0$',
  '^local.durhamatletico.com$',
);

$base_url = 'https://local.durhamatletico.com';
