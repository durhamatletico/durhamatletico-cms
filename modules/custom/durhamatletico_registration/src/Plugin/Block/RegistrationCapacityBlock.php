<?php

/**
 * @file
 * Contains \Drupal\durhamatletico_registration\Plugin\Block\RegistrationCapacityBlock.
 */

namespace Drupal\durhamatletico_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'RegistrationCapacityBlock' block.
 *
 * @Block(
 *  id = "registration_capacity_block",
 *  admin_label = @Translation("Registration capacity"),
 * )
 */
class RegistrationCapacityBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['capacity'] = array(
      '#type' => 'number',
      '#title' => $this->t('Capacity'),
      '#description' => $this->t('Number of registrations available'),
      '#default_value' => isset($this->configuration['capacity']) ? $this->configuration['capacity'] : '120',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['capacity'] = $form_state->getValue('capacity');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $registration_node_count = count(\Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'registration')
      ->execute());
    $registration_node_count = 30;
    $registration_percentage = (int) round((($registration_node_count / $this->configuration['capacity']) * 100));
    $message = '<br /><p>There are <strong>' . ((int) $this->configuration['capacity'] - $registration_node_count) . '</strong> registrations still available for the winter league. Please don\'t delay <a href="/user/register">in registering</a> -- we will exceed capacity and don\'t want you to be left out!</p>';
    $progress_markup = array(
      '#theme' => 'progress_bar',
      '#percentage' => $registration_percentage,
      '#message' => array('#markup' => $registration_node_count . ' of ' . $this->configuration['capacity']),
    );
    $markup = \Drupal::theme()->render('progress_bar', $progress_markup);
    $markup .= $message;
    $build['registration_capacity_block_capacity']['#markup'] = $markup;
    $build['#allowed_attributes']['exact'] = array('div' => array('exact' => 'style'));
    return $build;
  }

}
