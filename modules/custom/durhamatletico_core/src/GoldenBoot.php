<?php

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

  private $goals_for_division;

  /**
   * Constructor.
   */
  public function __construct() {
  }

  /**
   * Get an HTML table for a division.
   *
   * @param $division_nid
   */
  public function getGoldenBootForDivision($division_nid) {
    $this->getGoalsForDivision($division_nid);
    $rows = [];
    foreach ($this->goals_for_division as $goal) {
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
          'team' => $this->getPlayerTeam($player_uid),
        ];
      }
    }
    $headers = [
        'player' => 'Player',
        'goals' => 'Goals',
        'team' => 'Team',
    ];

    usort($rows, function($a, $b) {
      return $b['goals'] - $a['goals'];
    });

    return [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
    ];
  }

  private function getPlayerUid($goal) {
    return $goal->get('field_player_who_scored')->getString();
  }

  private function getPlayerDisplayName($goal) {
    $player_uid = $goal->get('field_player_who_scored')->getString();
      $player = User::load($player_uid);
      if ($player_uid == 0) {
        // Anonymous, keep going.
        return FALSE;
      }
    $jersey_number = $this->getPlayerJerseyNumber($player_uid);
    if (!$jersey_number) {
      return FALSE;
    }
    return sprintf('%s %s. (%d)',
        $player->get('field_first_name')->getString(),
        substr($player->get('field_last_name')->getString(), 0, 1),
        $jersey_number
    );
  }

  private function getPlayerJerseyNumber($player_uid) {
    // Get the player's jersey number.
    $reg_nid = \Drupal::entityQuery('node')
        ->condition('type', 'registration')
        ->condition('status', 1)
        ->condition('uid', $player_uid)
        ->addMetaData('uid', 1)
        ->execute();
    if (!$reg_nid) {
      return FALSE;
    }
    $reg_node = Node::load(current($reg_nid));
    if ($reg_node) {
      $jersey_number = $reg_node->get('field_registration_shirt_number')->getString();
      return $jersey_number;
    }
    else {
      return FALSE;
    }
  }

  private function getGoalsForDivision($division_nid) {
    $this->goals_for_division = [];
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
        $this->goals_for_division[] = $goal;
      }
    }
  }

  private function getPlayerTeam($player_uid) {
    // Get the player's team.
    $team_nid = \Drupal::entityQuery('node')
        ->condition('type', 'team')
        ->condition('status', 1)
        ->condition('field_players.target_id', $player_uid)
        ->addMetaData('uid', 1)
        ->execute();
    if (!$team_nid) {
      // TODO: Logging.
      return FALSE;
    }
    $team_node = Node::load(current($team_nid));
    return $team_node->get('field_abbreviation')->getString();
  }
}
