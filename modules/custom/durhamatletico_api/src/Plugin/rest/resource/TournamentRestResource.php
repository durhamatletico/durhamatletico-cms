<?php

namespace Drupal\durhamatletico_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\node\Entity\Node;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "tournament_rest_resource",
 *   label = @Translation("Tournaments for bracket display"),
 *   uri_paths = {
 *     "canonical" = "/api/tournaments/{nid}"
 *   }
 * )
 */
class TournamentRestResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * Returns formatted output for use with bracket JS.
   *
   * @param int $nid
   *   The node ID of the tournament, e.g. 1655.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the log entry.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown when the tournament entry was not found.
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when no tournament entry was provided.
   */
  public function get($nid = NULL) {
    if ($nid) {
      // Look up node.
      $node = Node::load($nid);
      if (!$node || $node->getType() !== 'league') {
        throw new NotFoundHttpException(t('Tournament not found.'));
      }

      return new ResourceResponse($node);
    }

    throw new BadRequestHttpException(t('No tournament ID was provided.'));
  }

}
