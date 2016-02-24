<?php
/**
 * @file
 * Drush integration.
 */

use Drupal\simplesitemap\Simplesitemap;

/**
 * Implements hook_drush_command().
 */
function simplesitemap_drush_command() {
  $items['simplesitemap-generate'] = array(
    'description' => 'Regenerate XML sitemaps for all languages according to the module settings.',
    'callback' => 'drush_simplesitemap_generate',
    'drupal dependencies' => array('simplesitemap'),
  );
  return $items;
}

/**
 * Callback function for hook_drush_command().
 *
 * Regenerate sitemap for all languages.
 */
function drush_simplesitemap_generate() {
  $sitemap = new Simplesitemap;
  $sitemap->generate_sitemap();
}
