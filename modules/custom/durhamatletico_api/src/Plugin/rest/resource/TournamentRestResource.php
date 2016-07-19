<?php

namespace Drupal\durhamatletico_api\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "tournament_rest_resource",
 *   label = @Translation("Tournaments for bracket display"),
 *   uri_paths = {
 *     "canonical" = "/api/tournaments/{id}"
 *   }
 * )
 */
class TournamentRestResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * Returns formatted output for use with bracket JS.
   *
   * @param string $id
   *   The path of the tournament, e.g. node/1655.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the log entry.
   *j
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown when the tournament entry was not found.
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when no tournament entry was provided.
   */
  public function get($id = NULL) {
    if ($id) {
      if (!$id) {
        throw new NotFoundHttpException(t('Tournament not found.'));
      }

      return new ResourceResponse('test');
    }

    throw new BadRequestHttpException(t('No tournament ID was provided.'));
  }

}
