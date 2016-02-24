<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\Simplesitemap.
 */

namespace Drupal\simplesitemap;

/**
 * Simplesitemap class.
 *
 * Main module class.
 */
class Simplesitemap {

  private $config;
  private $sitemap;

  function __construct() {
    $this->initialize();
  }

  private function initialize() {
    $this->get_config_from_db();
    $this->get_sitemap_from_db();
  }

  /**
   * Gets the entity_type_id and bundle_name of the form object if available and only
   * if the sitemap supports this entity type through an existing plugin.
   *
   * @param object $form_state
   * @param string $form_id
   *
   * @return array containing the entity_type_id and the bundle_name of the
   *  form object or FALSE if none found or not supported by an existing plugin.
   */
  public static function get_sitemap_form_entity_data($form_state, $form_id) {

    // Get all simplesitemap plugins.
    $manager = \Drupal::service('plugin.manager.simplesitemap');
    $plugins = $manager->getDefinitions();

    // Go through simplesitemap plugins and check if one of them declares usage
    // of this particular form. If that's the case, get entity type id of the
    // plugin definition and assume the bundle to be of the same name as the
    // entity type id.
    foreach($plugins as $plugin) {
      if (isset($plugin['form_id']) && $plugin['form_id'] === $form_id) {
        return array(
          'entity_type_id' => $plugin['id'],
          'bundle_name' => $plugin['id'],
        );
      }
    }

    // Else get entity type id and bundle name from the form if available and only
    // if a simplesitemap plugin of the same entity type exists.
    $form_entity = self::get_form_entity($form_state);
    if ($form_entity !== FALSE) {
      $form_entity_type_id = $form_entity->getEntityTypeId();
      if (isset($plugins[$form_entity_type_id])) {
        if (!isset($plugins[$form_entity_type_id]['form_id'])
          || $plugins[$form_entity_type_id]['form_id'] === $form_id) {
          return array(
            'entity_type_id' => $form_entity_type_id,
            'bundle_name' => $form_entity->Id(),
          );
        }
      }
    }

    // If both methods of getting simplesitemap entity data for this form
    // failed, return FALSE.
    return FALSE;
  }

  /**
   * Gets the object entity of the form if available.
   *
   * @param object $form_state
   *
   * @return object $entity or FALSE if non-existent or if form operation is
   *  'delete'.
   */
  private static function get_form_entity($form_state) {
    $form_object = $form_state->getFormObject();
    if (!is_null($form_object)
      && method_exists($form_state->getFormObject(), 'getEntity')
      && $form_object->getOperation() !== 'delete') {
      $entity = $form_state->getFormObject()->getEntity();
      return $entity;
    }
    return FALSE;
  }

  /**
   * Gets sitemap from db.
   */
  private function get_sitemap_from_db() {
    $this->sitemap = db_query("SELECT * FROM {simplesitemap}")->fetchAllAssoc('id');
  }

  /**
   * Gets sitemap settings from the configuration storage.
   */
  private function get_config_from_db() {
    $this->config = \Drupal::config('simplesitemap.settings');
  }

  /**
   * Gets a specific sitemap configuration from the configuration storage.
   *
   * @return mixed
   *  The requested configuration.
   */
  public function get_config($key) {
    return $this->config->get($key);
  }

  /**
   * Saves a specific sitemap configuration to db.
   *
   * @param string $key
   *  Configuration key, like 'entity_links'.
   * @param mixed $value
   *  The configuration to be saved.
   */
  public function save_config($key, $value) {
    \Drupal::service('config.factory')->getEditable('simplesitemap.settings')
      ->set($key, $value)->save();
    $this->initialize();
  }

  /**
   * Returns the whole sitemap, a requested sitemap chunk,
   * or the sitemap index file.
   *
   * @param int $sitemap_id
   *
   * @return string $sitemap
   *  If no sitemap id provided, either a sitemap index is returned, or the
   *  whole sitemap, if the amount of links does not exceed the max links setting.
   *  If a sitemap id is provided, a sitemap chunk is returned.
   */
  public function get_sitemap($sitemap_id = NULL) {
    if (empty($this->sitemap)) {
      $this->generate_sitemap();
    }

    if (is_null($sitemap_id) || !isset($this->sitemap[$sitemap_id])) {

      // Return sitemap index, if there are multiple sitemap chunks.
      if (count($this->sitemap) > 1) {
        return $this->get_sitemap_index();
      }

      // Return sitemap if there is only one chunk.
      else {
        return $this->sitemap[0]->sitemap_string;
      }
    }

    // Return specific sitemap chunk.
    else {
      return $this->sitemap[$sitemap_id]->sitemap_string;
    }
  }

  /**
   * Generates the sitemap for all languages and saves it to the db.
   */
  public function generate_sitemap() {
    $generator = new SitemapGenerator();
    $generator->set_custom_links($this->get_config('custom'));
    $generator->set_entity_types($this->get_config('entity_types'));
    $this->sitemap = $generator->generate_sitemap($this->get_setting('max_links'));
    $this->save_sitemap();
    drupal_set_message(t("The <a href='@url' target='_blank'>XML sitemap</a> has been regenerated for all languages.",
      array('@url' => $GLOBALS['base_url'] . '/sitemap.xml')));
  }

  /**
   * Saves the sitemap to the db.
   */
  private function save_sitemap() {
    db_truncate('simplesitemap')->execute();
    $values = array();
    foreach($this->sitemap as $chunk_id => $chunk_data) {
      $values[] = array(
        'id' => $chunk_id,
        'sitemap_string' => $chunk_data->sitemap_string,
        'sitemap_created' =>  $chunk_data->sitemap_created,
      );
    }

    $query = db_insert('simplesitemap')
      ->fields(array('id', 'sitemap_string', 'sitemap_created'));
    foreach ($values as $record) {
      $query->values($record);
    }
    $query->execute();
  }

  /**
   * Generates and returns the sitemap index as string.
   *
   * @return string
   *  The sitemap index.
   */
  private function get_sitemap_index() {
    $generator = new SitemapGenerator();
    return $generator->generate_sitemap_index($this->sitemap);
  }

  /**
   * Gets a specific sitemap setting.
   *
   * @param string $name
   *  Name of the setting, like 'max_links'.
   *
   * @return mixed
   *  The current setting from db or FALSE if setting does not exist.
   */
  public function get_setting($name) {
    $settings = $this->get_config('settings');
    return isset($settings[$name]) ? $settings[$name] : FALSE;
  }

  /**
   * Saves a specific sitemap setting to db.
   *
   * @param $name
   *  Setting name, like 'max_links'.
   * @param $setting
   *  The setting to be saved.
   */
  public function save_setting($name, $setting) {
    $settings = $this->get_config('settings');
    $settings[$name] = $setting;
    $this->save_config('settings', $settings);
  }

  /**
   * Returns a 'time ago' string of last timestamp generation.
   *
   * @return mixed
   *  Formatted timestamp of last sitemap generation, otherwise FALSE.
   */
  public function get_generated_ago() {
    if (isset($this->sitemap[0]->sitemap_created)) {
      return \Drupal::service('date.formatter')
        ->formatInterval(REQUEST_TIME - $this->sitemap[0]->sitemap_created);
    }
    return FALSE;
  }
}
