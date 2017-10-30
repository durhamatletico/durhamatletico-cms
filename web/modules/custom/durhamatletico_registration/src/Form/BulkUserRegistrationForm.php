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

    if (isset($_SESSION['bulk_import_results'])) {
      $table_headers = ['User', 'Messages', 'Success / Failure'];
      $form['results'] = [
        '#type' => 'table',
        '#rows' => $_SESSION['bulk_import_results'],
        '#header' => $table_headers,
      ];
    }

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
    $bulk_import = new BulkImport();
    if (!$bulk_import->validateCsv($file)) {
      $form_state->setErrorByName('csv', $this->t('CSV did not pass validation.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = File::load($form_state->getValue('csv')[0]);
    $batch = [
      'title' => t('Importing users and registrations'),
      'operations' =>
        [
          ['durhamatletico_registration_import', [$file->id()]],
        ],
      'finished' => 'durhamatletico_registration_import_finished'
    ];
    batch_set($batch);
  }

}