<?php
/**
 * @file
 * Contains Drupal\mollom\Controller\FormListBuilder.
 */

namespace Drupal\mollom\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\mollom\Entity\FormInterface;
use Drupal\mollom\Utility\MollomUtilities;

/**
 * Provides a listing of mollom_form entities.
 *
 * @package Drupal\mollom\Controller
 *
 * @ingroup mollom
 */
class FormListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    MollomUtilities::getAdminAPIKeyStatus();
    MollomUtilities::displayMollomTestModeWarning();

    $header['label'] = $this->t('Form');
    $header['machine_name'] = $this->t('Machine name');
    $header['protection_mode'] = $this->t('Protection mode');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['machine_name'] = $entity->id();
    $row['protection_mode'] = $entity->mode == FormInterface::MOLLOM_MODE_ANALYSIS ? t('Textual analysis') : 'CAPTCHA only';

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritDoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $operations['delete']['title'] = t('Unprotect');
    return $operations;
  }

}
