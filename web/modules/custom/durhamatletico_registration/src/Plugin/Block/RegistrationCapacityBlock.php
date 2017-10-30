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
    $team_nodes = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'team')
      ->execute();
    $rows = [];
    foreach ($team_nodes as $team_nid) {
      $team_node = \Drupal\node\Entity\Node::load($team_nid);
      $string = str_replace('- Winter 2016', '', $team_node->getTitle());
      if ($team_node->get('field_team_jersey_color')->value) {
        $string .= '(' . $team_node->get('field_team_jersey_color')->value . ')';
      }
      $rows[] = array($string);
    }

    asort($rows);

    $team_markup = array(
      'header' => array('Team'),
      'rows' => $rows,
    );
    $markup = '<br /><p>There are <strong>' . count($team_nodes) . '</strong> teams signed up, and ';
    $markup .= '<strong>' . ((int) $this->configuration['capacity'] - $registration_node_count) . '</strong> registrations are still available for the winter league.';
    $markup .= ' Please don\'t delay <a href="/user/register">in registering</a> -- we will exceed capacity and don\'t want you to be left out!</p>';
    $markup .= \Drupal::theme()->render('table', $team_markup);
    $build['registration_capacity_block_capacity']['#markup'] = $markup;
    $build['#allowed_attributes']['exact'] = array('div' => array('exact' => 'style'));
    return $build;
  }

}
