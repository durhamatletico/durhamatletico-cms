<?php

/**
 * @file
 * Contains \Drupal\durhamatletico_registration\Plugin\Block\RegistrationNodeBlock.
 */

namespace Drupal\durhamatletico_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a 'RegistrationNodeBlock' block.
 *
 * @Block(
 *  id = "registration_node_block",
 *  admin_label = @Translation("Register for the league"),
 * )
 */
class RegistrationNodeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Look up if user already has a registration node. If so, don't show this
    // block.
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $registration_node = \Drupal::service('durhamatletico_registration.registration')->getRegistrationNodeForUser($user);
    $build = [];
    if (!count($registration_node)) {
      $url = Url::fromRoute('node.add_page');
      $build['registration_node_block']['#markup'] = t('@clickhere for the winter 2016 futsal league.', array('@clickhere' => \Drupal::l(t('Click here to register'), $url)));
    }
    // TODO: Show the user what leagues they are registered for.
    return $build;
  }

}
