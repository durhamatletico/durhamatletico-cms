<?php

namespace Drupal\contact_storage\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A subscriber invalidating the entity type definition cache when the settings
 * page is saved.
 */
class ContactStorageSettingsFormSave implements EventSubscriberInterface {

  /**
   * Invalidate the entity type definition cache whenever the settings are
   * modified.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The Event to process.
   */
  public function onSave(ConfigCrudEvent $event) {
    if ($event->getConfig()->getName() === 'contact_storage.settings') {
      \Drupal::entityTypeManager()->clearCachedDefinitions();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onSave'];
    return $events;
  }

}
