<?php

/**
 * @file
 * Contains \Drupal\durhamatletico_registration\RegistrationService.
 */

namespace Drupal\durhamatletico_registration;

use Drupal\Core\Url;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RegistrationService.
 *
 * @package Drupal\durhamatletico_registration
 */
class RegistrationService {

  /**
   * Constructor.
   */
  public function __construct() {
  }

  /**
   * Node access callback.
   *
   * @param \Drupal\node\NodeInterface $node
   * @param $op
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return \Drupal\Core\Access\AccessResult
   */
  public function nodeAccessRegistration(\Drupal\node\NodeInterface $node, $op, \Drupal\Core\Session\AccountInterface $account) {
    if ($op == 'view') {
      return $this->canViewRegistration($node, $account);
    }
    return AccessResult::neutral();
  }

  /**
   * Determine if a user can view a given registration node.
   *
   * The user may view the node if they are the creator of it, or if they are
   * an admin.
   *
   * @param \Drupal\node\NodeInterface $node
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return \Drupal\Core\Access\AccessResult
   */
  public function canViewRegistration(\Drupal\node\NodeInterface $node, \Drupal\Core\Session\AccountInterface $account) {
    if ($account->hasPermission('administer content')) {
      return AccessResult::allowed();
    }
    if ($node->getOwner()->getAccountName() === $account->getAccountName()) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

  /**
   * Get registration node(s) for user, if any.
   *
   * @param \Drupal\user\UserInterface $user
   * @return array
   */
  public function getRegistrationNodeForUser(\Drupal\user\UserInterface $user) {
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'registration')
      ->condition('uid', $user->id());
    $nids = $query->execute();
    return $nids;
  }

  /**
   * Assign a player to a team.
   *
   * Incoming entity will be a registration node with a reference
   * to a team. When that's updated, load the team node and make
   * sure the player is assigned/removed from the team node.
   *
   * TODO: This does not update the previous revision, so manual
   * clean up is needed if the player is switched to a different
   * team.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function assignPlayerToTeam(\Drupal\Core\Entity\EntityInterface $entity) {
    $team_nid = $entity->get('field_registration_teams')->getValue();
    if (isset($team_nid[0]['target_id'])) {
      $team_node = \Drupal\node\Entity\Node::load($team_nid[0]['target_id']);
      $player_nids = array_merge(
        $team_node->get('field_players')->getValue(),
        array(array('target_id' => $entity->getOwnerId()))
      );
      $player_nids = array_map("unserialize", array_unique(array_map("serialize", $player_nids)));
      $team_node->get('field_players')->setValue($player_nids);
      $team_node->save();
    }
  }

  /**
   * Check if a user can create a new registration.
   *
   * Users are only allowed to create one registration node. An exception is
   * granted to admins, but even they shouldn't abuse this rule!
   *
   * @return bool
   */
  public function canCreateNewRegistration(\Drupal\user\UserInterface $user) {
    // Let admins do they want.
    if (\Drupal::currentUser()->hasPermission('administer content')) {
      return TRUE;
    }
    if (in_array('league_administrator', \Drupal::currentUser()->getRoles(TRUE))) {
      // Allow league admins to create/edit.
      return TRUE;
    }
    return TRUE;
  }

}
