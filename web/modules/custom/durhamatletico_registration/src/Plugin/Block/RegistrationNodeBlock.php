<?php

namespace Drupal\durhamatletico_registration\Plugin\Block;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
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
    list($base, $uid) = explode('/', trim(\Drupal::service('path.current')->getPath(), '/'));
    if ($base !== 'user') {
      return [];
    }
    // If anonymous, don't show the block.
    if ($uid <= 0) {
      return [];
    }
    // If the user ID in the path doesn't match the current user, don't show
    // this.
    if ($uid !== \Drupal::currentUser()->id()) {
      return [];
    }
    $user = User::load((int) $uid);
    $registration_node = \Drupal::service('durhamatletico_registration.registration')->getRegistrationNodeForUser($user);
    $build = [];
    if (!count($registration_node)) {
      // TODO: Rework this. @debt.
      $url = Url::fromRoute('node.add', ['node_type' => 'registration']);
      $build['registration_node_block']['#markup'] = t('@clickhere for the spring 2016 futsal league.', ['@clickhere' => \Drupal::l(t('Click here to register'), $url)]);
      $build['registration_node_block']['#prefix'] = '<div class="messages messages--status">';
      $build['registration_node_block']['#suffix'] = '</div>';
    }
    else {
      $node = Node::load(array_shift($registration_node));
      $balance_due = $node->get('field_balance_due')->getValue();
      if ($balance_due[0]['value'] > 0) {
        // Registration is unpaid.
        drupal_set_message(t('Your registration is not complete! You have a balance of $@amount due on your registration. Please visit @node to pay.',
          ['@amount' => $balance_due[0]['value'] / 100, '@node' => \Drupal::l('this link', Url::fromRoute('entity.node.canonical', ['node' => $node->id()]))]), 'warning');
      }
    }
    return $build;
  }

}
