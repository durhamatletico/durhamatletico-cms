<?php

/**
 * @file
 * Contains \Drupal\durhamatletico_core\Plugin\Block\GoldenBootWinter2016.
 */

namespace Drupal\durhamatletico_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Provides a 'GoldenBootWinter2016' block.
 *
 * @Block(
 *  id = "golden_boot_winter2016",
 *  admin_label = @Translation("Golden boot winter2016"),
 * )
 */
class GoldenBootWinter2016 extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {
    $goal_nids = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'goal')
        ->addMetaData('uid', 1)
        ->execute();
    $goal_nodes = Node::loadMultiple($goal_nids);
    $div_one_goals = $div_two_goals = [];
    foreach ($goal_nodes as $goal) {
      // Load the game.
      // Determine if team is in div one or div two.
      $game = Node::load($goal->get('field_game')->getString());
      if ($game->get('field_division')->getString() == 177) {
        // Division 1 = 177.
        $div_one_goals[] = $goal;
      }
      else {
        // Division 2 = 178.
        $div_two_goals[] = $goal;
      }
    }

    $div_one_table = [];
    // Assign goal tallies to players.
    foreach ($div_one_goals as $goal) {
      $player_uid = $goal->get('field_player_who_scored')->getString();
      $player = User::load($player_uid);
      if ($player_uid == 0) {
        // Anonymous, keep going.
        continue;
      }
      $player_display_name = $player->get('field_first_name')->getString() . ' ' . substr($player->get('field_last_name')->getString(), 0, 1) . '.';
      // Get the player's team.
      try {
        $team_nid = \Drupal::entityQuery('node')
            ->condition('type', 'team')
            ->condition('status', 1)
            ->condition('field_players.target_id', $player_uid)
            ->addMetaData('uid', 1)
            ->execute();
        if (!$team_nid) {
          // TODO: Logging.
          continue;
        }
        $team_node = Node::load(current($team_nid));
        $team_abbr = $team_node->get('field_abbreviation')->getString();
      }
      catch (Exception $e) {
        // Keep going.
        // TODO: Log errors.
        continue;
      }
      // Get the player's jersey number.
      try {
        $reg_nid = \Drupal::entityQuery('node')
            ->condition('type', 'registration')
            ->condition('status', 1)
            ->condition('uid', $player_uid)
            ->addMetaData('uid', 1)
            ->execute();
        $reg_node = Node::load(current($reg_nid));
        $jersey_number = $reg_node->get('field_registration_shirt_number')->getString();
      }
      catch (Exception $e) {
        // Keep going.
        // TODO: Log errors.
        continue;
      }
      // Build tables.
      if (isset($div_one_table[$player_uid])) {
        $div_one_table[$player_uid]['goals'] = $div_one_table[$player_uid]['goals'] + 1;
      }
      else {
        $div_one_table[$player_uid] = [
          'player' => $player_display_name . ' (#' . $jersey_number . ')',
          'goals' => 1,
          'team' => $team_abbr,
        ];
      }
    }
    $headers = [
        'player' => 'Player',
        'goals' => 'Goals',
        'team' => 'Team',
    ];

    usort($div_one_table, function($a, $b) {
      return $b['goals'] - $a['goals'];
    });

    return [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $div_one_table,
    ];
  }

}
