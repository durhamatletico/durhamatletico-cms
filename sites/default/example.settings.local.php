<?php

// Set your local database array.
$databases['default']['default'] = array(
  'database' => 'databasename',
  'username' => 'root',
  'password' => 'root',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$settings['hash_salt'] = 'abcdefg';
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
$settings['cache']['bins']['render'] = 'cache.backend.null';
$base_url = 'http://local.durhamatletico.com';
// Get this file from Pantheon.
if ($stripe_file = file_get_contents('sites/default/files/private/stripe.json')) {
  $stripe_config = json_decode($stripe_file, TRUE);
  $config['stripe_checkout.settings'] = $stripe_config['dev'];
}
