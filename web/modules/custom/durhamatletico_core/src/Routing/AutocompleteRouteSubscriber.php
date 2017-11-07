<?php

namespace Drupal\durhamatletico_core\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 *
 */
class AutocompleteRouteSubscriber extends RouteSubscriberBase {

  /**
   *
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('system.entity_autocomplete')) {
      $route->setDefault('_controller', '\Drupal\durhamatletico_core\Controller\EntityAutocompleteController::handleAutocomplete');
    }
  }

}
