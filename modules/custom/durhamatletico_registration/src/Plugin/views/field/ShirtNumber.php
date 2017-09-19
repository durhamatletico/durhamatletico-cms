<?php

namespace Drupal\durhamatletico_registration\Plugin\views\field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
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
  protected $entity_type_manager;

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
      $container->get('durhamatletico_registration.registration'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * @{inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RegistrationService $registration_service, EntityTypeManagerInterface $entity_type_manager) {
    $this->registration_service = $registration_service;
    $this->entity_type_manager = $entity_type_manager;
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    return $this->getShirtNumber($values->users_field_data_node__field_players_uid, $values->nid);
  }

  /**
   * Get the shirt number by user ID and team node.
   */
  public function getShirtNumber($uid, $team_nid) {
    $registration_node = $this->getRegistrationNode($uid, $team_nid);
    return $registration_node->get('field_registration_shirt_number')->getString();
  }

  /**
   * Look up the registration node for a user on a team.
   */
  public function getRegistrationNode($uid, $team_nid) {
    $registration_nid = $this->registration_service->getRegistrationNodeForUserOnTeam($uid, $team_nid);
    return $this->entity_type_manager->getStorage('node')->load($registration_nid);
  }

}