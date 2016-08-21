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

      // Get the quarter-final, semi-final, and final games if set.
      $quarter_final_nodes = [];
      $qf_results = [];
      if (isset($node->field_quarter_final_games->entity)) {
        $quarter_finals = $node->field_quarter_final_games;
        foreach ($quarter_finals as $quarter_final) {
          $quarter_final_nodes[] = Node::load($quarter_final->entity->id());
        }
      }
      if (!count($quarter_final_nodes)) {
        return new ResourceResponse([]);
      }

      if (!count($qf_results)) {
        $qf_results = [[], [], [], []];
      }

      $semi_final_nodes = [];
      $sf_results = [];
      if (isset($node->field_semi_final_games->entity)) {
        $semi_finals = $node->field_semi_final_games;
        foreach ($semi_finals as $semi_final_node) {
          $semi_final_nodes[] = Node::load($semi_final_node->entity->id());
        }
        // Get SF node scores.
        foreach ($semi_final_nodes as $sf_node) {
          if ($sf_node->get('field_game_status')->getString() == 'Played' && $sf_node->field_home_team_score->value && $sf_node->field_away_team_score->value) {
            $sf_results[] = [
              (int) $sf_node->field_home_team_score->value,
              (int) $sf_node->field_away_team_score->value,
            ];
          }
        }
      }

      $finals = [];
      $final_results = [];
      if (isset($node->field_final_game->entity)) {
        $finals = $node->field_final_game;
        foreach ($finals as $final_node) {
          $final_nodes[] = Node::load($final_node->entity->id());
        }
        foreach ($final_nodes as $f_node) {
          if ($f_node->get('field_game_status')->getString() == 'Played' && $f_node->field_home_team_score->value && $f_node->field_away_team_score->value) {
            $final_results[] = [
              (int) $f_node->field_home_team_score->value,
              (int) $f_node->field_away_team_score->value,
            ];
          }
        }
      }

      // Get the team names from the quarter-final entity references.
      $qf_results = [];
      $team_names = [];
      foreach ($quarter_final_nodes as $qf_node) {
        $team_names[] = [
          $qf_node->get('field_home_team')->entity->field_abbreviation->value,
          $qf_node->get('field_away_team')->entity->field_abbreviation->value,
        ];
        // Get the QF game scores from each match.
        $qf_results[] = [
          (int) $qf_node->field_home_team_score->value,
          (int) $qf_node->field_away_team_score->value,
        ];
      }

      $response['teams'] = $team_names;

      if (count($qf_results)) {
        $response['results'][] = $qf_results;
      }
      if (count($sf_results)) {
        $response['results'][] = $sf_results;
      }
      if (count($final_results)) {
        $response['results'][] = $final_results;
      }
      return new ResourceResponse($response);
    }

    throw new BadRequestHttpException(t('No tournament ID was provided.'));
  }

}
