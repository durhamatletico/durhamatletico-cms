<?php

namespace Drupal\durhamatletico_core;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;

/**
 * Class StandingsService.
 *
 * @package Drupal\durhamatletico_core
 */
class StandingsService implements StandingsServiceInterface {

  protected $entity;

  /**
   * Constructor.
   */
  public function __construct() {
  }

  /**
   * Update the standings for teams in the given game entity.
   *
   * Given a 'game' node containing two teams, analyze the results and update
   * the wins/draws/losses/points/goals for both teams.
   *
   * The data is stored directly in each team node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function updateStandings(EntityInterface $entity) {
    $this->entity = $entity;
    // Update standings for both teams.
    $home_team = $entity->get('field_home_team')->getValue();
    $away_team = $entity->get('field_away_team')->getValue();
    $teams = [$home_team, $away_team];
    foreach ($teams as $team) {
      $team_nid = $team[0]['target_id'];
      // Get all the games this team has played.
      $team_games = $this->getGamesForTeam($team_nid);
      // Get all the for/against goals for this team.
      list($team_goals_for, $team_goals_against) = $this->getGoalsForTeam($team_nid);
      // Get all the wins/draws/losses based on analyzing game scores.
      list($wins, $draws, $losses) = $this->analyzeGames($team_nid, $team_games);
      // Update the team node.
      $this->updateTeamStats($team_nid, [
        $wins,
        $draws,
        $losses,
        count($team_games),
        $team_goals_for,
        $team_goals_against,
      ]);
    }
  }

  /**
   *
   */
  protected function getGamesForTeam($team_nid) {
    $team_node = Node::load($team_nid);
    $query = \Drupal::entityQuery('node');
    $group = $query->orConditionGroup()
      ->condition('field_home_team', $team_node->id())
      ->condition('field_away_team', $team_node->id());
    $games = Node::loadMultiple(
      $query->condition('status', 1)
        ->condition('type', 'game')
        ->condition($group)
        ->condition('field_game_status', 'Played')
        ->execute()
    );
    // Now filter out only games that are part of the original entity's competition and also filter out games where
    // field_cup_round is set.
    $current_division = $this->entity->get('field_division')->entity->id();
    return array_filter($games, function ($game) use ($current_division) {
      if (($game->get('field_division')->entity->id() == $current_division) &&
          ($game->get('field_cup_round')->getValue() == NULL)) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    });
  }

  /**
   *
   */
  protected function getGoalsForTeam($team_nid) {
    // We're not querying 'goal' nodes for now.
    $goals_for_count = 0;
    $goals_against_count = 0;
    $games = $this->getGamesForTeam($team_nid);
    foreach ($games as $game) {
      $home_team = $game->get('field_home_team')->getValue();
      $home_team = $home_team[0]['target_id'];

      $away_team = $game->get('field_away_team')->getValue();
      $away_team = $away_team[0]['target_id'];

      $home_team_score = $game->get('field_home_team_score')->getValue();
      $home_team_score = $home_team_score[0]['value'];

      $away_team_score = $game->get('field_away_team_score')->getValue();
      $away_team_score = $away_team_score[0]['value'];

      if ($home_team == $team_nid) {
        $goals_for_count += $home_team_score;
      }
      else {
        $goals_against_count += $home_team_score;
      }
      if ($away_team == $team_nid) {
        $goals_for_count += $away_team_score;
      }
      else {
        $goals_against_count += $away_team_score;
      }
    }
    return [
      $goals_for_count,
      $goals_against_count,
    ];
  }

  /**
   *
   */
  protected function analyzeGames($team_nid, array $games) {
    $wins = 0;
    $losses = 0;
    $draws = 0;
    foreach ($games as $game) {
      $home_team = $game->get('field_home_team')->getValue();
      $home_team = $home_team[0]['target_id'];

      $away_team = $game->get('field_away_team')->getValue();
      $away_team = $away_team[0]['target_id'];

      $home_team_score = $game->get('field_home_team_score')->getValue();
      $home_team_score = $home_team_score[0]['value'];

      $away_team_score = $game->get('field_away_team_score')->getValue();
      $away_team_score = $away_team_score[0]['value'];

      if ($home_team == $team_nid) {
        if ($home_team_score > $away_team_score) {
          $wins++;
        }
        if ($home_team_score == $away_team_score) {
          $draws++;
        }
        if ($home_team_score < $away_team_score) {
          $losses++;
        }
      }
      if ($away_team == $team_nid) {
        if ($away_team_score > $home_team_score) {
          $wins++;
        }
        if ($away_team_score == $home_team_score) {
          $draws++;
        }
        if ($away_team_score < $home_team_score) {
          $losses++;
        }
      }
    }
    return [
      $wins,
      $draws,
      $losses,
    ];
  }

  /**
   *
   */
  protected function updateTeamStats($team_nid, array $stats) {
    list($wins, $draws, $losses, $games_played, $goals_for, $goals_against) = $stats;
    $team_node = Node::load($team_nid);
    $team_node->get('field_team_goals_for')->setValue($goals_for);
    $team_node->get('field_team_goals_against')->setValue($goals_against);
    $team_node->get('field_team_wins')->setValue($wins);
    $team_node->get('field_team_draws')->setValue($draws);
    $team_node->get('field_team_losses')->setValue($losses);
    $team_node->get('field_team_games_played')->setValue($games_played);
    $team_node->get('field_team_goal_difference')->setValue($goals_for - $goals_against);
    $team_node->get('field_team_points')->setValue(($wins * 3) + ($draws));
    $team_node->save();
  }

}
