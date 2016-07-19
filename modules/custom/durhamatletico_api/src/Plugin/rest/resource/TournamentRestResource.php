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
      $round_one_query = clone $query;
      $round_one_query->condition('field_cup_round', 1);
      $ids = $round_one_query->execute();

      if (!count($ids)) {
        throw new NotFoundHttpException(t('Unable to load games.'));
      }

      // Load games and results.
      $initial_teams = $games = [];
      $round_one_winners = $round_two_winners = [];
      $round_one_match_nodes = $round_two_match_nodes = [];
      $round_one = $round_two = $round_three = [];
      foreach ($ids as $game_nid) {
        $game_node = Node::load($game_nid);
        // Load team.
        $home_team_node = Node::load($game_node->get('field_home_team')->entity->id());
        $away_team_node = Node::load($game_node->get('field_away_team')->entity->id());
        $initial_teams[] = [
          $game_node->get('field_home_team')->entity->field_abbreviation->value,
          $game_node->get('field_away_team')->entity->field_abbreviation->value,
        ];
        $initial_teams_ids[] = [
          $game_node->get('field_home_team')->entity->id(),
          $game_node->get('field_away_team')->entity->id(),
        ];
        // Round one results.
        $round_one[] = [
          (int) $game_node->field_home_team_score->value,
          (int) $game_node->field_away_team_score->value,
        ];
        // Figure out the winners.
        if ($game_node->field_home_team_score->value > $game_node->field_away_team_score->value) {
          $round_one_winners[$game_node->field_bracket_grouping->value][] = $home_team_node->id();
        }
        else {
          $round_one_winners[$game_node->field_bracket_grouping->value][] = $away_team_node->id();
        }
        $round_one_match_nodes[$game_node->field_bracket_grouping->value][] = $game_node;
      }

      // Sort the winners to match the initial teams listing.

      // Now that we know the winners of round one, get the results for the semi
      // finals.
      // Load game nodes in round two, then filter by team ID.
      $semi_final_results = [];
      $semi_final_results[] = $this->getSemiResults($query, $round_one_winners, 1);
      $semi_final_results[] = $this->getSemiResults($query, $round_one_winners, 2);

      // Now get the final and 3rd/4th consolation match games.
      $final_results = [];

      $response['teams'] = $initial_teams;
      $response['results'] = [
        $round_one,
        $semi_final_results,
        $round_three,
      ];
      return new ResourceResponse($response);
    }

    throw new BadRequestHttpException(t('No tournament ID was provided.'));
  }

  private function getSemiResults($query, $round_one_winners, $group) {
    $semi_final_group_query = clone $query;
    $semi_final_group_query->condition('field_cup_round', 2);
    $semi_final_group = $semi_final_group_query->execute();
    foreach ($semi_final_group as $semi_final_group_result) {
      $node = Node::load($semi_final_group_result);
      if (!count(array_diff($round_one_winners[$group], [
        $node->get('field_home_team')->entity->id(),
        $node->get('field_away_team')->entity->id(),
      ]))) {
        // Get the first team's score.
        $team_one = $round_one_winners[$group][0];
        $team_two = $round_one_winners[$group][1];
        if ($node->get('field_home_team')->entity->id() == $team_one) {
          $team_one_score = $node->field_home_team_score->value;
        }
        else {
          $team_one_score = $node->field_away_team_score->value;
        }
        if ($node->get('field_home_team')->entity->id() == $team_two) {
          $team_two_score = $node->field_home_team_score->value;
        }
        else {
          $team_two_score = $node->field_away_team_score->value;
        }
        // Now sort the score. The score of the winner of match one in round one
        // comes first.
        return [(int) $team_one_score, (int) $team_two_score];
      }
    }
  }

  private function getFinalResults($query, $round_two_winners, $group) {

  }

}
