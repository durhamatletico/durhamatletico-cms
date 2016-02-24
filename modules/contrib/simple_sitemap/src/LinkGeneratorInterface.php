<?php
/**
 * @file
 * Contains Drupal\simplesitemap\LinkGeneratorInterface.
 */

namespace Drupal\simplesitemap;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for simplesitemap plugins.
 */

interface LinkGeneratorInterface extends PluginInspectionInterface {

  /**
   * @param string $entity_type
   *  E.g. 'node_type', 'taxonomy_vocabulary'.
   * @param array $bundles
   *  E.g. 'page'.
   *
   * @return array $paths
   */
  public function get_entity_paths($entity_type, $bundles);
}
