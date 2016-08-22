<?php

/**
 * Load services definition file.
 */

$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * N.B. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to insure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

if (file_exists('/conf/settings.php')) {
  include '/conf/settings.php';
}

$settings['install_profile'] = 'standard';
if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {

  // Dev.
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'dev') {
    $domain = 'dev.durhamatletico.com';
    $settings['trusted_host_patterns'] = array(
      '^dev\.durhamatletico\.com$',
      'dev-durham-atletico\.pantheon\.io$',
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
      'test-durham-atletico\.pantheon\.io$',
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
