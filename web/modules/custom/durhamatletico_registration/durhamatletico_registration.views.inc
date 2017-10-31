<?php

/**
 * Implements hook_views_data_alter().
 */
function durhamatletico_registration_views_data_alter(array &$data) {
  $data['node']['shirt_number'] = array(
    'title' => t('Shirt number for player'),
    'field' => array(
      'title' => t('Shirt number for player'),
      'help' => t('Gets the player shirt number on the registration for this team'),
      'id' => 'shirt_number',
    ),
  );
}