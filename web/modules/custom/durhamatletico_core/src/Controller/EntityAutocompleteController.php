<?php

namespace Drupal\durhamatletico_core\Controller;

use Drupal\system\Controller\EntityAutocompleteController as BaseController;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\durhamatletico_core\EntityAutocompleteMatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class EntityAutocompleteController extends BaseController {

  /**
   * The autocomplete matcher for entity references.
   */
  protected $matcher;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityAutocompleteMatcher $matcher, KeyValueStoreInterface $key_value) {
    $this->matcher = $matcher;
    $this->keyValue = $key_value;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('durhamatletico_core.autocomplete_matcher'),
      $container->get('keyvalue')->get('entity_autocomplete')
    );
  }

}
