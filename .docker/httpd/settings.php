<?php

$databases['default']['default'] = [
  'database' => 'durhamatletico_docker',
  'username' => 'root',
  'password' => 'root',
  'host' => 'db',
  'port' => '',
  'driver' => 'mysql',
  'prefix' => '',
];

// Docker specific
$conf['drupal_http_request_fails'] = FALSE;

// Don't auto-run cron.
$conf['cron_safe_threshold'] = 0;

// File settings.
$conf['file_temporary_path'] = '/tmp';
$conf['file_private_path'] = 'sites/default/files/private';
