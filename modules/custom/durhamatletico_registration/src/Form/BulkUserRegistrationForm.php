<?php

namespace Drupal\durhamatletico_registration\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\durhamatletico_registration\BulkImport;

/**
 * Implements an example form.
 */
class BulkUserRegistrationForm extends FormBase {

  protected $data;
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bulk_user_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['csv'] = [
      '#type' => 'managed_file',
      '#required' => TRUE,
      '#upload_location' => 'private://csv-registrations/',
      '#title' => $this->t('CSV'),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Import',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!count($form_state->getValue('csv'))) {
      $form_state->setErrorByName('csv', $this->t('Please upload a CSV first.'));
      return;
    }
    $file = File::load($form_state->getValue('csv')[0]);
    $bulk_import = new BulkImport($file);
    if (!$bulk_import->validateCsv()) {
      $form_state->setErrorByName('csv', $this->t('CSV did not pass validation.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = File::load($form_state->getValue('csv')[0]);
    $bulk_import = new BulkImport($file);
    try {
      $bulk_import->import();
      drupal_set_message('Success!');
    }
    catch (Exception $e) {
      drupal_set_message('Error, sorry.', 'error');
    }
  }

}