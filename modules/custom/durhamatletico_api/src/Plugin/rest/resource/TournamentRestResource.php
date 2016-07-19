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

      // Load all game referencing this competition.
      $query = \Drupal::entityQuery('node');
      $query->condition('type', 'game');
      $query->condition('field_division', $nid);
      $ids = $query->execute();

      if (!count($ids)) {
        throw new NotFoundHttpException(t('Unable to load games.'));
      }

      // Load games and results.
      $initial_teams = $games = [];
      $round_one = $round_two = $round_three = [];
      foreach ($ids as $game_nid) {
        $game_node = Node::load($game_nid);
        // Load team.
        $home_team_node = Node::load($game_node->get('field_home_team')->entity->id());
        $away_team_node = Node::load($game_node->get('field_away_team')->entity->id());
        switch ($game_node->field_cup_round->value) {
          case 1:
            $initial_teams[] = [
              $home_team_node->field_abbreviation->value,
              $away_team_node->field_abbreviation->value,
            ];
            // Round one results.
            $round_one[] = [$game_node->field_home_team_score->value, $game_node->field_away_team_score->value];
            break;

          case 2:
            if ($game_node->field_bracket_grouping->value == 1) {
              $round_two[] = [$game_node->field_home_team_score->value, $game_node->field_away_team_score->value];
            }
            break;

          case 3:
            $round_three[] = [$game_node->field_home_team_score->value, $game_node->field_away_team_score->value];
            break;
        }
      }
      $response['teams'] = $initial_teams;
      $response['results'] = [
        $round_one,
        $round_two,
        $round_three,
      ];
      return new ResourceResponse($response);
    }

    throw new BadRequestHttpException(t('No tournament ID was provided.'));
  }

}
