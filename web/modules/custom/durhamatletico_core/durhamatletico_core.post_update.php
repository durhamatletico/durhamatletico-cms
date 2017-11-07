<?php

/**
 * @file
 * Update hooks.
 */

use Drupal\node\Entity\Node;

/**
 * Migrate HTML color field to new Color Field.
 */
function durhamatletico_core_post_update_color_field_migration() {
  $query = \Drupal::entityQuery('node')
    ->condition('status', 1)
    ->condition('type', 'team');
  $nids = $query->execute();
  foreach ($nids as $nid) {
    $team_node = Node::load($nid);
    $legacy_color = $team_node->get('field_jersey_color_html_')->getString();
    $team_node->set('field_jersey_html_color', [
      'color' => $legacy_color,
      'opacity' => NULL,
    ]);
    $team_node->save();
    \Drupal::logger('durhamatletico_core')->info(t('Migrating jersey color to new field for node %nid', ['%nid' => $nid]));
  }
}
