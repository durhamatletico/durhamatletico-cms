<?php

/**
 * @file
 * Contains \Drupal\durhamatletico_registration\RegistrationService.
 */

namespace Drupal\durhamatletico_registration;

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
      return \Drupal\Core\Access\AccessResult::allowed();
    }
    if ($node->getOwner()->getAccountName() === $account->getAccountName()) {
      return \Drupal\Core\Access\AccessResult::allowed();
    }
    return \Drupal\Core\Access\AccessResult::forbidden();
  }

}
