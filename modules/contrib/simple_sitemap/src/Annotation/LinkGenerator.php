<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\Annotation\LinkGenerator.
 */

namespace Drupal\simplesitemap\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a LinkGenerator item annotation object.
 *
 * @see \Drupal\simplesitemap\Plugin\SimplesitemapManager
 * @see plugin_api
 *
 * @Annotation
 */
class LinkGenerator extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;
}
