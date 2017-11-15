<?php

declare(strict_types = 1);

/**
 * @file
 * Contains \Drupal\durhamatletico_core\GoldenBoot.
 */

namespace Drupal\durhamatletico_core;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;

/**
 * Class GoldenBoot.
 *
 * @package Drupal\durhamatletico_core
 */
class GoldenBoot {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get an HTML table for a division.
   *
   * @param int $divisionNid
   *   The division to get golden boot stats for.
   *
   * @return array
   *   An array of data to render as an HTML table.
   */
  public function getGoldenBootForDivision(int $divisionNid) :array {
    $goalsForDivision = $this->getGoalsForDivision($divisionNid);
    $rows = [];
    foreach ($goalsForDivision as $goal) {
      $playerDisplayName = $this->getPlayerDisplayName($goal);
      $playerUid = $this->getPlayerUid($goal);
      if (!$playerDisplayName) {
        continue;
      }
      if (isset($rows[$playerUid])) {
        $rows[$playerUid]['goals'] = $rows[$playerUid]['goals'] + 1;
        continue;
      }
      $rows[$playerUid] = [
        'player' => $playerDisplayName,
        'goals' => 1,
        'team' => $this->getPlayerTeam($playerUid, $divisionNid),
      ];
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
  public function getPlayerDisplayName(Node $goal) {
    $playerUid = (int) $goal->get('field_player_who_scored')->getString();
    /** @var \Drupal\user\Entity\User $player */
    $player = $this->entityTypeManager->getStorage('user')->load($playerUid);
    if ($playerUid == 0) {
      // Anonymous, keep going.
      return FALSE;
    }
    $jerseyNumber = $this->getPlayerJerseyNumber($playerUid, $goal);
    if (!$jerseyNumber) {
      return FALSE;
    }
    return sprintf('#%d %s %s.',
      $jerseyNumber,
      ucwords($player->get('field_first_name')->getString()),
      substr(ucwords($player->get('field_last_name')->getString()), 0, 1)
    );
  }

  /**
   * Get the jersey number for a player.
   *
   * @param int $playerUid
   *   The uid to use in the lookup.
   * @param \Drupal\node\Entity\Node $goal
   *   The goal node.
   *
   * @return bool|int
   *   Return the player jersey number.
   */
  public function getPlayerJerseyNumber(int $playerUid, Node $goal) {
    // Get the teams from the goal node.
    /** @var \Drupal\node\Entity\Node $game */
    $game = $goal->field_game->entity;
    $homeTeamNid = $game->field_home_team->entity->id();
    $awayTeamNid = $game->field_away_team->entity->id();
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $group = $query->orConditionGroup()
      ->condition('field_registration_teams.target_id', $homeTeamNid)
      ->condition('field_registration_teams.target_id', $awayTeamNid);
    $regNid = $query->condition('type', 'registration')
      ->condition('status', 1)
      ->condition($group)
      ->condition('uid', $playerUid)
      ->addMetaData('uid', 1)
      ->execute();
    if (!$regNid) {
      return FALSE;
    }
    /** @var \Drupal\node\Entity\Node $regNode */
    $regNode = $this->entityTypeManager->getStorage('node')->load(current($regNid));
    return ($regNode) ? (int) $regNode->get('field_registration_shirt_number')->getString() : FALSE;
  }

  /**
   * Get an array of all goals for the division.
   *
   * @param int $divisionNid
   *   The division node ID.
   *
   * @return array
   *   An array of goals for the division.
   */
  public function getGoalsForDivision(int $divisionNid) {
    // Get node IDs of all games in the division.
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $gameNids = $query
      ->condition('type', 'game')
      ->condition('field_division.target_id', $divisionNid)
      ->addMetaData('uid', 1)
      ->execute();
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $goalNids = $query->condition('status', 1)
      ->condition('type', 'goal')
      ->condition('field_game.target_id', $gameNids, 'IN')
      ->addMetaData('uid', 1)
      ->execute();
    return $this->entityTypeManager->getStorage('node')->loadMultiple($goalNids);
  }

  /**
   * Get the abbreviation of the player's team.
   *
   * @param int $playerUid
   *   The player user ID.
   * @param int $divisionNid
   *   The division node ID.
   *
   * @return bool|string
   *   Return FALSE if not found, or the abbreviated team name otherwise.
   */
  public function getPlayerTeam(int $playerUid, int $divisionNid) {
    // Get the player's team.
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $teamNid = $query
      ->condition('type', 'team')
      ->condition('status', 1)
      ->condition('field_players.target_id', $playerUid)
      ->addMetaData('uid', 1)
      ->execute();
    if ($teamNid) {
      // Check if the team nid is in the division nid handed to us.
      $divisionNode = $this->entityTypeManager->getStorage('node')->load($divisionNid);
      foreach ($teamNid as $nid) {
        $teams = array_column($divisionNode->field_teams->getValue(), 'target_id');
        if (in_array($nid, $teams)) {
          return $this->entityTypeManager->getStorage('node')->load($nid)->get('field_abbreviation')->getString();
        }
      }
    }
    return FALSE;
  }

}
