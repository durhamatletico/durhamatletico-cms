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
   * Helper to auto-generate node titles.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function setTitle(\Drupal\Core\Entity\EntityInterface $entity) {
    if ($entity->getType() === 'game') {
      $home_team = $entity->get('field_home_team')->getValue();
      $away_team = $entity->get('field_away_team')->getValue();
      if ($home_team && $away_team) {
        $entity->setTitle(
            sprintf('%s v %s (%s)',
                $entity->get('field_home_team')->entity->get('field_abbreviation')->getString(),
                $entity->get('field_away_team')->entity->get('field_abbreviation')->getString(),
                $entity->get('field_game_date')->getString())
        );
      }
      else {
        $entity->setTitle('TBD v TBD');
      }
    }
    if ($entity->getType() === 'goal') {
      $player = User::load($entity->get('field_player_who_scored')->entity->id());
      $game = Node::load($entity->get('field_game')->entity->id());
      $entity->setTitle(
        sprintf('%s in %s', $player->get('field_first_name')->getString(), $game->getTitle())
      );
    }
  }

}
