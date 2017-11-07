<?php

namespace Drupal\durhamatletico_core;

use Drupal\Core\Entity\EntityAutocompleteMatcher;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;

/**
 *
 */
class EntityAutocompleteMatcher extends EntityAutocompleteMatcher {

  /**
   * Gets matched labels based on a given search string.
   */
  public function getMatches($target_type, $selection_handler, $selection_settings, $string = '') {

    $matches = [];

    $options = [
      'target_type'      => $target_type,
      'handler'          => $selection_handler,
      'handler_settings' => $selection_settings,
    ];

    $handler = $this->selectionManager->getInstance($options);

    if (isset($string)) {
      // Get an array of matching entities.
      $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
      $entity_labels = $handler->getReferenceableEntities($string, $match_operator, 10);

      // Loop through the entities and convert them into autocomplete output.
      foreach ($entity_labels as $values) {
        foreach ($values as $entity_id => $label) {

          $entity = \Drupal::entityTypeManager()->getStorage($target_type)->load($entity_id);
          $entity = \Drupal::entityManager()->getTranslationFromContext($entity);

          $type = !empty($entity->type->entity) ? $entity->type->entity->label() : $entity->bundle();

          if ($entity->getEntityType()->id() == 'user' && $entity->id() > 0) {
            if ($entity->field_first_name->value && $entity->field_last_name->value) {
              $teams = [];
              $label .= ' (' . Html::escape($entity->field_first_name->value) . ' ' . Html::escape($entity->field_last_name->value) . ')';
              $registrations = \Drupal::entityQuery('node')
                ->condition('type', 'registration')
                ->condition('uid', $entity->id())
                ->condition('status', TRUE)
                ->sort('created', 'DESC')
                ->execute();
              $registrations = array_slice($registrations, 0, 2);
              foreach ($registrations as $reg_nid) {
                $node = \Drupal::entityTypeManager()
                  ->getStorage('node')
                  ->load($reg_nid);
                $team = $node->field_registration_teams->entity;
                if (method_exists($team, 'getTitle')) {
                  $teams[] = $team->getTitle();
                }
              }
              if (count($teams)) {
                $label .= ' [' . implode(', ', $teams) . ']';
              }

            }
          }

          $key = $label . ' (' . $entity_id . ')';
          // Strip things like starting/trailing white spaces, line breaks and tags.
          $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
          // Names containing commas or quotes must be wrapped in quotes.
          $key = Tags::encode($key);
          $label = $label . ' (' . $entity_id . ')';
          $matches[] = ['value' => $key, 'label' => $label];
        }
      }
    }

    return $matches;
  }

}
