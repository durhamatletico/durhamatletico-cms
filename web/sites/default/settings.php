<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Place the config directory outside of the Drupal root.
 */
$config_directories = array(
  CONFIG_SYNC_DIRECTORY => dirname(DRUPAL_ROOT) . '/config',
);

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

/**
 * Always install the 'standard' profile to stop the installer from
 * modifying settings.php.
 *
 * See: tests/installer-features/installer.feature
 */
$settings['install_profile'] = 'standard';
if (file_exists('/conf/settings.php')) {
  include '/conf/settings.php';
}
if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
  // Dev.
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'dev') {
    $domain = 'dev.durhamatletico.com';
    $settings['trusted_host_patterns'] = array(
      '^dev\.durhamatletico\.com$',
      'dev-durham-atletico\.pantheonsite\.io$',
    );
    if ($stripe_file = file_get_contents('sites/default/files/private/stripe.json')) {
      $stripe_config = json_decode($stripe_file, TRUE);
      $config['stripe_checkout.settings'] = $stripe_config['dev'];
    }
    if ($mailgun_file = file_get_contents('sites/default/files/private/mailgun.json')) {
      $mailgun_config = json_decode($mailgun_file, TRUE);
      $config['smtp.settings'] = $mailgun_config['dev'];
    }
  }
  // Test.
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'test') {
    $domain = 'test.durhamatletico.com';
    $settings['trusted_host_patterns'] = array(
      '^test\.durhamatletico\.com$',
      'test-durham-atletico\.pantheonsite\.io$',
    );
    if ($stripe_file = file_get_contents('sites/default/files/private/stripe.json')) {
      $stripe_config = json_decode($stripe_file, TRUE);
      $config['stripe_checkout.settings'] = $stripe_config['test'];
    }
    if ($mailgun_file = file_get_contents('sites/default/files/private/mailgun.json')) {
      $mailgun_config = json_decode($mailgun_file, TRUE);
      $config['smtp.settings'] = $mailgun_config['test'];
    }
  }
  // Live.
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    $domain = 'www.durhamatletico.com';
    $settings['trusted_host_patterns'] = array(
      '^www\.durhamatletico\.com$',
    );
    if ($stripe_file = file_get_contents('sites/default/files/private/stripe.json')) {
      $stripe_config = json_decode($stripe_file, TRUE);
      $config['stripe_checkout.settings'] = $stripe_config['live'];
    }
    if ($mailgun_file = file_get_contents('sites/default/files/private/mailgun.json')) {
      $mailgun_config = json_decode($mailgun_file, TRUE);
      $config['smtp.settings'] = $mailgun_config['live'];
    }
  }
  else {
    $domain = $_SERVER['HTTP_HOST'];
  }
  $base_url = 'https://' . $domain;
}
if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
  // Redirect to https://$primary_domain in the Live environment
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    $primary_domain = 'www.durhamatletico.com';
  }
  else {
    // Redirect to HTTPS on every Pantheon environment.
    $primary_domain = $_SERVER['HTTP_HOST'];
  }
  if ($_SERVER['HTTP_HOST'] != $primary_domain
      || !isset($_SERVER['HTTP_X_SSL'])
      || $_SERVER['HTTP_X_SSL'] != 'ON' ) {
    # Name transaction "redirect" in New Relic for improved reporting (optional)
    if (extension_loaded('newrelic')) {
      newrelic_name_transaction("redirect");
    }
    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://'. $primary_domain . $_SERVER['REQUEST_URI']);
    exit();
  }
  // Drupal 8 Trusted Host Settings
  if (is_array($settings)) {
    $settings['trusted_host_patterns'] = array('^'. preg_quote($primary_domain) .'$');
  }
}
