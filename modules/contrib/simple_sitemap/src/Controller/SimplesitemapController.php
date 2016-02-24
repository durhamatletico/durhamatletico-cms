<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\Controller\SimplesitemapController.
 */

namespace Drupal\simplesitemap\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\simplesitemap\Simplesitemap;

/**
 * SimplesitemapController.
 */
class SimplesitemapController {

  /**
   * Returns the whole sitemap, a requested sitemap chunk, or the sitemap index file.
   *
   * @param int $sitemap_id
   *  Id of the sitemap chunk.
   *
   * @return object Response
   *  Returns an XML response.
   */
  public function get_sitemap($sitemap_id = NULL) {

    $sitemap = new Simplesitemap;
    $output = $sitemap->get_sitemap($sitemap_id);

    // Display sitemap with correct xml header.
    return new Response($output, Response::HTTP_OK, array('content-type' => 'application/xml'));
  }
}
