<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\LinkGenerator\Menu.
 *
 * Plugin for menu entity link generation.
 */

namespace Drupal\simplesitemap\Plugin\LinkGenerator;

use Drupal\simplesitemap\Annotation\LinkGenerator;
use Drupal\simplesitemap\LinkGeneratorBase;

/**
 * Menu class.
 *
 * @LinkGenerator(
 *   id = "menu"
 * )
 */
class Menu extends LinkGeneratorBase {

  /**
   * {@inheritdoc}
   */
  function get_paths($bundle) {
    $routes = db_query("SELECT mlid, route_name, route_parameters, options FROM {menu_tree} WHERE menu_name = :menu_name and enabled = 1", array(':menu_name' => $bundle))
      ->fetchAllAssoc('mlid');

    $paths = array();
    foreach ($routes as $id => $entity) {
      if (empty($entity->route_name))
        continue;

      //todo: There may be a better way to do this.
      $options = !empty($options = unserialize($entity->options)) ? $options : array();
      $route_parameters = !empty($route_parameters = unserialize($entity->route_parameters))
        ? array(key($route_parameters) => $route_parameters[key($route_parameters)]) : array();

      $paths[$id]['path_data'] = $this->get_multilang_urls_from_route($entity->route_name, $route_parameters, $options);
      //todo: Implement lastmod for menu items.
    }
    return $paths;
  }
}
