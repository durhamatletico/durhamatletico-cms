<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\LinkGenerator\TaxonomyVocabulary.
 *
 * Plugin for taxonomy term entity link generation.
 */

namespace Drupal\simplesitemap\Plugin\LinkGenerator;

use Drupal\simplesitemap\Annotation\LinkGenerator;
use Drupal\simplesitemap\LinkGeneratorBase;

/**
 * TaxonomyVocabulary class.
 *
 * @LinkGenerator(
 *   id = "taxonomy_vocabulary"
 * )
 */
class TaxonomyVocabulary extends LinkGeneratorBase {

  /**
   * {@inheritdoc}
   */
  function get_paths($bundle) {
    $results = db_query("SELECT tid, changed FROM {taxonomy_term_field_data} WHERE vid = :vid", array(':vid' => $bundle))
      ->fetchAllAssoc('tid');

    $paths = array();
    foreach ($results as $id => $data) {
      $paths[$id]['path_data'] = $this->get_multilang_urls_from_route("entity.taxonomy_term.canonical", array('taxonomy_term' => $id));
      $paths[$id]['lastmod'] = $data->changed;
    }
    return $paths;
  }
}
