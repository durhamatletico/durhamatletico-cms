<?php

/**
 * @file
 * Contains \Drupal\durhamatletico_registration\RegistrationService.
 */

namespace Drupal\durhamatletico_registration;

use Drupal\Core\Url;
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
    return \Drupal\Core\Access\AccessResult::neutral();
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
   * Check if a user can create a new registration.
   *
   * Users are only allowed to create one registration node. An exception is
   * granted to admins, but even they shouldn't abuse this rule!
   *
   * @return bool
   */
  public function canCreateNewRegistration(\Drupal\user\UserInterface $user) {
    if (\Drupal::currentUser()->hasPermission('administer content')) {
      return TRUE;
    }
    $nids = $this->getRegistrationNodeForUser($user);
    if (count($nids)) {
      $response = new RedirectResponse('/user');
      $response->send();
      drupal_set_message(t('You have already created a registration in the system!'), 'error');
    }
    return FALSE;
  }

}
