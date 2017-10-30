<?php

/**
 * @file
 * Contains \Drupal\durhamatletico_registration\Plugin\Block\RegistrationInstructionBlock.
 */

namespace Drupal\durhamatletico_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'RegistrationInstructionBlock' block.
 *
 * @Block(
 *  id = "registration_instruction_block",
 *  admin_label = @Translation("Registration instruction block"),
 * )
 */
class RegistrationInstructionBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['registration_instruction_block']['#prefix'] = '<div class="messages messages--warning">';
    $build['registration_instruction_block']['#markup'] = t('<p>Please note, there are three steps to register for the winter league.</p>
<ol><li>Create a user account and log in</li><li><a href="/node/add/registration">Fill out a registration form</a> for the league</li><li>Pay for the registration</li></ol>');
    $build['registration_instruction_block']['#suffix'] = '</div>';
    return $build;
  }

}
