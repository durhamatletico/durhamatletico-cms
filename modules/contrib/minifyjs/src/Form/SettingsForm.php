<?php

namespace Drupal\minifyjs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the module.
 */
class SettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form  = [];

    $form['exclusion_list'] = [
      '#title'         => t('Exclusion List'),
      '#description'   => t(
        'Some files cannot be minified, for whatever reason. This list allows the administrator to exclude these files from the %title page and stops the site from using the minified version of the file (if applicable). Allows wildcards (*) and other Drupal path conventions.',
        [
          '%title' => 'Manage Javascript Files',
        ]
      ),
      '#type'          => 'textarea',
      '#default_value' => \Drupal::config('minifyjs.config')->get('exclusion_list'),
    ];

    $form['save'] = [
      '#type'          => 'submit',
      '#value'         => t('Save settings'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'minifyjs_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::service('config.factory')->getEditable('minifyjs.config')
      ->set('exclusion_list', $form_state->getValue('exclusion_list'))
      ->save();

    // Clear the cache
    \Drupal::cache()->delete(MINIFYJS_CACHE_CID);
  }
}