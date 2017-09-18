<?php

namespace Drupal\durhamatletico_registration\Plugin\views\field;

use Drupal\durhamatletico_registration\RegistrationService;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("shirt_number")
 */
class ShirtNumber extends FieldPluginBase implements ContainerFactoryPluginInterface {

  protected $registration_service;

  /**
   * @{inheritdoc}
   */
  public function query() {}

  /**
   * @{inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('durhamatletico_registration.registration')
    );
  }

  /**
   * @{inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RegistrationService $registration_service) {
    $this->registration_service= $registration_service;
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $uid = $values->users_field_data_node__field_players_uid;
    $registration_nid = $this->registration_service->getRegistrationNodeForUserOnTeam($uid, $values->nid);
    $registration_node = \Drupal::entityTypeManager()->getStorage('node')->load($registration_nid);
    return $registration_node->field_registration_shirt_number->value;
  }

}