<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\LinkGenerators\CustomLinkGenerator.
 *
 * Generates custom sitemap paths provided by the user.
 */

namespace Drupal\simplesitemap;

/**
 * CustomLinkGenerator class.
 */
class CustomLinkGenerator extends LinkGeneratorBase {

  /**
   * Returns an array of all urls of the custom paths.
   *
   * @param array $custom_paths
   *
   * @return array $urls
   *
   */
  public function get_paths($custom_paths) {
    $paths = array();
    foreach($custom_paths as $i => $custom_path) {
      if (FALSE !== $path_data = $this->get_multilang_urls_from_user_input($custom_path['path'])) {
        $paths[$i]['path_data'] = $path_data;
        $paths[$i]['priority'] = isset($custom_path['priority']) ? $custom_path['priority'] : NULL;
        $paths[$i]['lastmod'] = NULL; //todo: implement lastmod
      }
    }
    return $paths;
  }
}
