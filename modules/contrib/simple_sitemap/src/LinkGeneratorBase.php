<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\LinkGeneratorBase.
 */

namespace Drupal\simplesitemap;

use Drupal\Component\Plugin\PluginBase;
use \Drupal\user\Entity\User;
use Drupal\Core\Url;

abstract class LinkGeneratorBase extends PluginBase implements LinkGeneratorInterface {

  private $entity_paths = array();
  private $current_entity_type;
  private $anonymous_account;
  protected $languages;
  protected $default_language_id;

  const PLUGIN_ERROR_MESSAGE = "The simplesitemap @plugin plugin has been omitted, as it does not return the required numeric array of path data sets. Each data sets must contain the required path element (relative path string or Drupal\\Core\\Url object) and optionally other elements, like lastmod.";
  const PATH_DOES_NOT_EXIST = "The path @faulty_path has been omitted from the XML sitemap, as it does not exist.";
  const ANONYMOUS_USER_ID = 0;

  function __construct() {
    $this->anonymous_account = User::load(self::ANONYMOUS_USER_ID);
    $this->languages = \Drupal::languageManager()->getLanguages();
    $this->default_language_id = \Drupal::languageManager()->getDefaultLanguage()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function get_entity_paths($entity_type, $bundles) {
    $this->current_entity_type = $entity_type;
    $i = 0;
    foreach($bundles as $bundle => $bundle_settings) {
      if (!$bundle_settings['index'])
        continue;
      $paths = $this->get_paths($bundle);

      if (!is_array($paths)) { // Some error catching.
        $this->register_error(self::PLUGIN_ERROR_MESSAGE, array('@plugin' => $this->current_entity_type));
        return $this->entity_paths;
      }

      foreach($paths as $id => $path) {

        if (isset($path['path_data']) && (isset($path['path_data']['path']) || !$path['path_data'])) {
          if ($path['path_data'] !== FALSE) {
            // If URLs have not been created yet by the plugin, use the 'path' to create the URLs now.
            if (empty($path['path_data']['urls'])) {
              $path['path_data'] = $this->get_multilang_urls_from_user_input($path['path_data']['path']);
            }
          }
          // If the plugin provided path does not exist, skip this path.
          if (!$path['path_data'])
            continue;
        }
        // If the returned data from the plugin is wrong, skip the plugin and register an error.
        else {
          $this->register_error(self::PLUGIN_ERROR_MESSAGE, array('@plugin' => $this->current_entity_type));
          return $this->entity_paths;
        }
        // Adding path, its options and the resulting URLs returned by the plugin.
        $this->entity_paths[$i]['path_data'] = $path['path_data'];

        // Adding priority if returned by the plugin.
        $this->entity_paths[$i]['priority'] = !empty($path['priority'])
          ? $path['priority'] : $bundle_settings['priority'];

        // Adding lastmod if returned by the plugin.
        $this->entity_paths[$i]['lastmod'] = isset($path['lastmod']) && is_numeric($path['lastmod'])
          ? date_iso8601($path['lastmod']) : NULL;
        $i++;
      }
    }
    return $this->entity_paths;
  }

  /**
   * Logs and displays an error.
   *
   * @param $message
   *  Untranslated message.
   * @param array $substitutions (optional)
   *  Substitutions (placeholder => substitution) which will replace placeholders
   *  with strings.
   * @param string $type (optional)
   *  Message type (status/warning/error).
   */
  protected function register_error($message, $substitutions = array(), $type = 'error') {
    $message = strtr(t($message), $substitutions);
    \Drupal::logger('simplesitemap')->notice($message);
    drupal_set_message($message, $type);
  }

  /**
   * Returns an array of all urls and their data of a bundle.
   *
   * @param string $bundle
   *  Machine name of the bundle, eg. 'page'.
   *
   * @return array $paths
   *  A numeric array of Drupal internal path data sets containing the internal
   *  path, url objects for every language (optional), lastmod (optional) and
   *  priority (optional):
   *
   * array(
   *   0 => array(
   *    'path_data' => array(
   *      'path' => '/relative/path/to/drupal/page',
   *      'urls' => array( //optional
   *        'en' => Drupal\Core\Url,
   *        'de' => Drupal\Core\Url,
   *      ),
   *    ),
   *    'priority' => 0.5 // optional
   *    'lastmod' => '1234567890' // optional: content changed unix date
   *   ),
   * )
   *
   * @abstract
   */
  abstract function get_paths($bundle);

  /**
   * Checks if anonymous users have access to a given path.
   *
   * @param \Drupal\Core\Url object
   *
   * @return bool
   *  TRUE if anonymous users have access to path, FALSE if they do not.
   */
  protected function access($url_object) {
    return $url_object->access($this->anonymous_account); //todo: Add error checking.
  }

  /**
   * Wrapper function for Drupal\Core\Url::fromRoute.
   * Returns url data for every language.
   *
   * @param $route_name
   * @param $route_parameters
   * @param $options
   * @see https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Url.php/function/Url%3A%3AfromRoute/8
   *
   * @return array
   *  Returns an array containing the internal path, url objects for every language,
   *  url options and access information.
   */
  protected function get_multilang_urls_from_route($route_name, $route_parameters = array(), $options = array()) {
    $options['absolute'] = empty($options['absolute']) ? TRUE : $options['absolute'];
    $url_object = Url::fromRoute($route_name, $route_parameters, $options);

    $urls = array();
    foreach($this->languages as $language) {
      if ($language->getId() === $this->default_language_id) {
        $urls[$this->default_language_id] = $url_object->toString();
      }
      else {
        $options['language'] = $language;
        $urls[$language->getId()] = Url::fromRoute($route_name, $route_parameters, $options)->toString();
      }
    }
    return array(
      'path' => $url_object->getInternalPath(),
      'urls' => $urls,
      'options' => $url_object->getOptions(),
      'access' => $this->access($url_object),
    );
  }

  /**
   * Wrapper function for Drupal\Core\Url::fromUserInput.
   * Returns url data for every language.
   *
   * @param $user_input
   * @param $options
   * @see https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Url.php/function/Url%3A%3AfromUserInput/8
   *
   * @return array or FALSE
   *  Returns an array containing the internal path, url objects for every language,
   *  url options and access information. Returns FALSE if path does not exist.
   */
  protected function get_multilang_urls_from_user_input($user_input, $options = array()) {
    $user_input = $user_input[0] === '/' ? $user_input : '/' . $user_input;
    if (!\Drupal::service('path.validator')->isValid($user_input)) {
      $this->register_error(self::PATH_DOES_NOT_EXIST, array('@faulty_path' => $user_input), 'warning');
      return FALSE;
    }

    $options['absolute'] = empty($options['absolute']) ? TRUE : $options['absolute'];
    $url_object = Url::fromUserInput($user_input, $options);

    $urls = array();
    foreach($this->languages as $language) {
      if ($language->getId() === $this->default_language_id) {
        $urls[$this->default_language_id] = $url_object->toString();
      }
      else {
        $options['language'] = $language;
        $urls[$language->getId()] = Url::fromUserInput($user_input, $options)->toString();
      }
    }
    return array(
      'path' => $url_object->getInternalPath(),
      'urls' => $urls,
      'options' => $url_object->getOptions(),
      'access' => $this->access($url_object),
    );
  }
}
