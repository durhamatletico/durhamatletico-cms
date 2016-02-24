<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\Plugin\LinkGenerator\User.
 *
 * Plugin for user link generation.
 */

namespace Drupal\simplesitemap\Plugin\LinkGenerator;

use Drupal\simplesitemap\Annotation\LinkGenerator;
use Drupal\simplesitemap\LinkGeneratorBase;

/**
 * User class.
 *
 * @LinkGenerator(
 *   id = "user",
 *   form_id = "user_admin_settings"
 * )
 */
class User extends LinkGeneratorBase {

  /**
   * {@inheritdoc}
   */
  function get_paths($bundle) {
    $results = db_query("SELECT uid, changed FROM {users_field_data} WHERE status = 1")
      ->fetchAllAssoc('uid');

    $paths = array();
    foreach ($results as $id => $data) {
      $paths[$id]['path_data'] = $this->get_multilang_urls_from_route("entity.user.canonical", array('user' => $id));
      $paths[$id]['lastmod'] = $data->changed;
    }
    return $paths;
  }
}
