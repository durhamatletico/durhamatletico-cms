<?php

/**
 * @file
 * Contains \Drupal\durhamatletico_core\ContentEntry.
 */

namespace Drupal\durhamatletico_core;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Class ContentEntry.
 *
 * @package Drupal\durhamatletico_core
 */
class ContentEntry implements ContentEntryInterface {
  /**
   * Constructor.
   */
  public function __construct() {
  }

  /**
   * Auto-generate node titles.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function setTitle(\Drupal\Core\Entity\EntityInterface $entity) {
    if ($entity->getType() === 'game') {
      $home_team = $entity->get('field_home_team')->getValue();
      $away_team = $entity->get('field_away_team')->getValue();
      $home_team = Node::load($home_team[0]['target_id']);
      $away_team = Node::load($away_team[0]['target_id']);
      $home_team = $home_team->get('field_abbreviation')->getValue();
      $away_team = $away_team->get('field_abbreviation')->getValue();
      $entity->setTitle(
        sprintf('%s v %s', $home_team[0]['value'], $away_team[0]['value'])
      );
    }
    if ($entity->getType() === 'goal') {
      $player = $entity->get('field_player_who_scored')->getValue();
      $player = User::load($player[0]['target_id']);
      $game = $entity->get('field_game')->getValue();
      $game = Node::load($game[0]['target_id']);
      $entity->setTitle(
        sprintf('%s in %s', $player->get('field_first_name')->getString(), $game->getTitle())
      );
    }
  }

}
