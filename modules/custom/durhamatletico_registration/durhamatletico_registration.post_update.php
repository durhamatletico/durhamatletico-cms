<?php

/**
 * @file
 * Update hooks.
 */

use Drupal\node\Entity\Node;

/**
 * Update free agent teams and fall 2016 seasons.
 */
function durhamatletico_registration_post_update_enable_teams_reg(&$sandbox) {

  $team_nids = [1718, 1719];
  foreach ($team_nids as $nid) {
    $node = Node::load($nid);
    $node->set('field_accepting_registrations', TRUE);
    $node->save();
  }

  $competition_nids = [1721, 1722, 1723];
  foreach ($competition_nids as $nid) {
    $node = Node::load($nid);
    $node->set('field_registration_status', TRUE);
    $node->save();
  }
  $result = t('Updated registration and team nodes.');

  return $result;
}
