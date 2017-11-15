<?php

declare(strict_types = 1);

/**
 * @file
 * Contains \Drupal\durhamatletico_core\GoldenBoot.
 */

namespace Drupal\durhamatletico_core;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Class GoldenBoot.
 *
 * @package Drupal\durhamatletico_core
 */
class GoldenBoot implements GoldenBootInterface {

  /**
   * Constructor.
   */
  public function __construct() {
  }

  /**
   * Get an HTML table for a division.
   *
   * @param int $division_nid
   *   The division to get golden boot stats for.
   *
   * @return array
   *   An array of data to render as an HTML table.
   */
  public function getGoldenBootForDivision(int $division_nid) :array {
    $goals_for_division = $this->getGoalsForDivision($division_nid);
    $rows = [];
    foreach ($goals_for_division as $goal) {
      $player_display_name = $this->getPlayerDisplayName($goal);
      $player_uid = $this->getPlayerUid($goal);
      if (!$player_display_name) {
        continue;
      }
      if (isset($rows[$player_uid])) {
        $rows[$player_uid]['goals'] = $rows[$player_uid]['goals'] + 1;
      }
      else {
        $rows[$player_uid] = [
          'player' => $player_display_name,
          'goals' => 1,
          'team' => $this->getPlayerTeam($player_uid, $division_nid),
        ];
      }
    }
    $headers = [
      'player' => 'Player',
      'goals' => 'Goals',
      'team' => 'Team',
    ];

    usort($rows, function ($first, $second) {
      return $second['goals'] - $first['goals'];
    });

    return [
      '#type' => 'table',
      '#cache' => [
        'tags' => ['node_list'],
      ],
      '#header' => $headers,
      '#rows' => array_slice($rows, 0, 10),
    ];
  }

  /**
   * Get the user ID of a player who scored a goal.
   *
   * @param \Drupal\node\Entity\Node $goal
   *   The goal node.
   *
   * @return int
   *   The user ID of the player who scored a goal.
   */
  private function getPlayerUid(Node $goal) :int {
    return (int) $goal->get('field_player_who_scored')->getString();
  }

  /**
   * Get the display name of a player who scored a goal.
   *
   * @param \Drupal\node\Entity\Node $goal
   *   The goal node.
   *
   * @return bool|string
   *   False if anonymous or we can't find their jersey number from a
   *   registration, a string with the display name otherwise.
   */
  private function getPlayerDisplayName(Node $goal) {
    $player_uid = (int) $goal->get('field_player_who_scored')->getString();
    $player = User::load($player_uid);
    if ($player_uid == 0) {
      // Anonymous, keep going.
      return FALSE;
    }
    $jersey_number = $this->getPlayerJerseyNumber($player_uid, $goal);
    if (!$jersey_number) {
      return FALSE;
    }
    return sprintf('#%d %s %s.',
      $jersey_number,
      ucwords($player->get('field_first_name')->getString()),
      substr(ucwords($player->get('field_last_name')->getString()), 0, 1)
    );
  }

  /**
   * Get the jersey number for a player.
   *
   * @param int $player_uid
   *   The uid to use in the lookup.
   * @param \Drupal\node\Entity\Node $goal
   *   The goal node.
   *
   * @return bool|int
   *   Return the player jersey number.
   */
  private function getPlayerJerseyNumber(int $player_uid, Node $goal) {
    // Get the teams from the goal node.
    /** @var \Drupal\node\Entity\Node $game */
    $game = $goal->field_game->entity;
    $home_team_nid = $game->field_home_team->entity->id();
    $away_team_nid = $game->field_away_team->entity->id();
    $query = \Drupal::entityQuery('node');
    $group = $query->orConditionGroup()
      ->condition('field_registration_teams.target_id', $home_team_nid)
      ->condition('field_registration_teams.target_id', $away_team_nid);
    $reg_nid = $query->condition('type', 'registration')
      ->condition('status', 1)
      ->condition($group)
      ->condition('uid', $player_uid)
      ->addMetaData('uid', 1)
      ->execute();
    if (!$reg_nid) {
      return FALSE;
    }
    /** @var \Drupal\node\Entity\Node $reg_node */
    $reg_node = Node::load(current($reg_nid));
    if ($reg_node) {
      return (int) $reg_node->get('field_registration_shirt_number')->getString();
    }
    else {
      return FALSE;
    }
  }

  /**
   * Get an array of all goals for the division.
   *
   * @param int $division_nid
   *   The division node ID.
   *
   * @return array
   *   An array of goals for the division.
   */
  private function getGoalsForDivision(int $division_nid) :array {
    $goals_for_division = [];
    $goal_nids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'goal')
      ->addMetaData('uid', 1)
      ->execute();
    $goal_nodes = Node::loadMultiple($goal_nids);
    foreach ($goal_nodes as $goal) {
      // Load the game.
      // Determine if team is in div one or div two.
      $game = Node::load($goal->get('field_game')->getString());
      if ($game->get('field_division')->getString() == $division_nid) {
        $goals_for_division[] = $goal;
      }
    }
    return $goals_for_division;
  }

  /**
   * Get the abbreviation of the player's team.
   *
   * @param int $player_uid
   *   The player user ID.
   * @param int $division_nid
   *   The division node ID.
   *
   * @return bool|string
   *   Return FALSE if not found, or the abbreviated team name otherwise.
   */
  private function getPlayerTeam(int $player_uid, int $division_nid) {
    // Get the player's team.
    $team_nid = \Drupal::entityQuery('node')
      ->condition('type', 'team')
      ->condition('status', 1)
      ->condition('field_players.target_id', $player_uid)
      ->addMetaData('uid', 1)
      ->execute();
    if ($team_nid) {
      // Check if the team nid is in the division nid handed to us.
      $team_node = FALSE;
      $division_node = Node::load($division_nid);
      foreach ($team_nid as $nid) {
        $teams = array_column($division_node->field_teams->getValue(), 'target_id');
        if (in_array($nid, $teams)) {
          $team_node = Node::load($nid);
          break;
        }
      }
      if ($team_node) {
        return $team_node->get('field_abbreviation')->getString();
      }
    }
    return FALSE;
  }

}
