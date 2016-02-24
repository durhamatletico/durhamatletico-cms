<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\SitemapGenerator.
 *
 * Generates a sitemap for entities and custom links.
 */

namespace Drupal\simplesitemap;

use \XMLWriter;

/**
 * SitemapGenerator class.
 */
class SitemapGenerator {

  const PRIORITY_DEFAULT = 0.5;
  const PRIORITY_HIGHEST = 10;
  const PRIORITY_DIVIDER = 10;
  const XML_VERSION = '1.0';
  const ENCODING = 'UTF-8';
  const XMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';
  const XMLNS_XHTML = 'http://www.w3.org/1999/xhtml';

  private $entity_types;
  private $custom;
  private $links;
  private $default_language_id;

  function __construct() {
    $this->default_language_id = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $this->links = array();
  }

  /**
   * Gets the values needed to display the priority dropdown setting.
   *
   * @return array $options
   */
  public static function get_priority_select_values() {
    $options = array();
    foreach(range(0, self::PRIORITY_HIGHEST) as $value) {
      $value = $value / self::PRIORITY_DIVIDER;
      $options[(string)$value] = (string)$value;
    }
    return $options;
  }

  public function set_entity_types($entity_types) {
    $this->entity_types = is_array($entity_types) ? $entity_types : array();
  }

  public function set_custom_links($custom) {
    $this->custom = is_array($custom) ? $custom : array();
  }

  /**
   * Generates and returns the sitemap.
   *
   * @param int $max_links
   *  This number dictates how many sitemap chunks are to be created.
   *
   * @return array $sitemaps.
   */
  public function generate_sitemap($max_links = NULL) {

    $this->generate_custom_paths();
    $this->generate_entity_paths();

    $sitemaps = array();

    // Create sitemap chunks according to the max_links setting.
    if (!empty($max_links) && count($this->links) > 0) {
      foreach(array_chunk($this->links, $max_links) as $sitemap_id => $sitemap_links) {
        $sitemaps[] = (object)[
          'sitemap_string' => $this->generate_sitemap_chunk($sitemap_links),
          'sitemap_created' => REQUEST_TIME,
        ];
      }
    }
    // If max_link setting is not set, create just one sitemap.
    else {
      $sitemaps[] = (object)[
        'sitemap_string' => $this->generate_sitemap_chunk($this->links),
        'sitemap_created' => REQUEST_TIME,
      ];
    }
    return $sitemaps;
  }

  /**
   * Generates and returns the sitemap index.
   *
   * @param array $sitemap
   *  All sitemap chunks keyed by the chunk ID.
   *
   * @return string sitemap index
   */
  public function generate_sitemap_index($sitemap) {
    $writer = new XMLWriter();
    $writer->openMemory();
    $writer->setIndent(TRUE);
    $writer->startDocument(self::XML_VERSION, self::ENCODING);
    $writer->startElement('sitemapindex');
    $writer->writeAttribute('xmlns', self::XMLNS);

    foreach ($sitemap as $chunk_id => $chunk_data) {
      $writer->startElement('sitemap');
      $writer->writeElement('loc', $GLOBALS['base_url'] . '/sitemaps/'
        . $chunk_id . '/' . 'sitemap.xml');
      $writer->writeElement('lastmod', date_iso8601($chunk_data->sitemap_created));
      $writer->endElement();
    }
    $writer->endElement();
    $writer->endDocument();
    return $writer->outputMemory();
  }

  /**
   * Generates and returns a sitemap chunk.
   *
   * @param array $sitemap_links
   *  All links with their translation and settings.
   *
   * @return string sitemap chunk
   */
  private function generate_sitemap_chunk($sitemap_links) {

    $writer = new XMLWriter();
    $writer->openMemory();
    $writer->setIndent(TRUE);
    $writer->startDocument(self::XML_VERSION, self::ENCODING);
    $writer->startElement('urlset');
    $writer->writeAttribute('xmlns', self::XMLNS);
    $writer->writeAttribute('xmlns:xhtml', self::XMLNS_XHTML);

    foreach ($sitemap_links as $link) {
      $writer->startElement('url');

      // Adding url to standard language.
      $writer->writeElement('loc', $link['path_data']['urls'][$this->default_language_id]);

      // Adding alternate urls (other languages) if any.
      if (count($link['path_data']['urls']) > 1) {
        foreach($link['path_data']['urls'] as $language_id => $localised_url) {
          $writer->startElement('xhtml:link');
          $writer->writeAttribute('rel', 'alternate');
          $writer->writeAttribute('hreflang', $language_id);
          $writer->writeAttribute('href', $localised_url);
          $writer->endElement();
        }
      }

      // Add priority if any.
      if (!is_null($link['priority'])) {
        $writer->writeElement('priority', $link['priority']);
      }

      // Add lastmod if any.
      if (!is_null($link['lastmod'])) {
        $writer->writeElement('lastmod', $link['lastmod']);
      }
      $writer->endElement();
    }
    $writer->endDocument();
    return $writer->outputMemory();
  }

  /**
   * Generates custom internal paths.
   */
  private function generate_custom_paths() {
    $link_generator = new CustomLinkGenerator();
    $links = $link_generator->get_paths($this->custom);
    $this->add_created_paths($links);
  }

  /**
   * Lets all simple sitemap plugins add their paths to the sitemap.
   */
  private function generate_entity_paths() {

    $manager = \Drupal::service('plugin.manager.simplesitemap');
    $plugins = $manager->getDefinitions();

    // Let all simplesitemap plugins generate add their links to the sitemap.
    foreach ($plugins as $link_generator_plugin) {
      if (isset($this->entity_types[$link_generator_plugin['id']])) {
        $instance = $manager->createInstance($link_generator_plugin['id']);
        $links = $instance->get_entity_paths($link_generator_plugin['id'],
          $this->entity_types[$link_generator_plugin['id']]);
        $this->add_created_paths($links);
      }
    }
  }

  /**
   * Adds Drupal internal paths generated by a plugin while removing duplicates.
   *
   * @param array $paths
   *  Drupal internal paths generated by a plugin.
   */
  private function add_created_paths($paths) {

    // Do not include path if anonymous users have no access to it.
    foreach($paths as $i => $path) {
      if (!$path['path_data']['access']) {
        unset($paths[$i]);
        continue;
      }
      // Do not include path if it is a duplicate.
      foreach($this->links as $existing_path) {
        if ($path['path_data']['path'] === $existing_path['path_data']['path']) {
          unset($paths[$i]);
          break;
        }
      }
    }
    $this->links = array_merge($this->links, $paths);
  }
}
