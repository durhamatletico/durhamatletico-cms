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
      $url = Url::fromRoute('node.add', ['node_type' => 'registration']);
      $build['registration_node_block']['#markup'] = t('@clickhere for the winter 2016 futsal league.', array('@clickhere' => \Drupal::l(t('Click here to register'), $url)));
    }
    else {
      $node = \Drupal\node\Entity\Node::load(array_shift($registration_node));
      $balance_due = $node->get('field_balance_due')->getValue();
      if ($balance_due[0]['value'] == '6000') {
        // Registration is unpaid.
        drupal_set_message(t('Your registration is not complete! You have a balance due on your registration. Please visit @node to pay.',
          array('@node' => \Drupal::l('this link', Url::fromRoute('entity.node.canonical', ['node' => $node->id()])))), 'warning');
      }
    }
    return $build;
  }

}
