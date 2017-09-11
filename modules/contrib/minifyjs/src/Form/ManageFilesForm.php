<?php

namespace Drupal\minifyjs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Patchwork;

/**
 * Displays a list of detected javascript files and allows actions to be
 * performed on them
 */
class ManageFilesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $files = minifyjs_load_all_files();
    $form  = [];

    // Statistics
    $number_of_files = 0;
    $minified_files  = 0;
    $unminified_size = 0;
    $minified_size   = 0;
    $saved_size      = 0;

    // pager init
    $limit = 100;
    $start = 0;
    if (isset($_REQUEST['page'])) {
      $start = $_REQUEST['page'] * $limit;
    }
    $total = count($files);
    pager_default_initialize($total, $limit);

    // Build the rows of the table.
    $rows  = [];
    if ($total) {

      // statistics for all files
      foreach ($files as $fid => $file) {
        $number_of_files++;
        $unminified_size += $file->size;
        $minified_size   += $file->minified_size;
        if ($file->minified_uri) {
          $saved_size    += $file->size - $file->minified_size;
          $minified_files++;
        }
      }

      // build table rows
      $files_subset = array_slice($files, $start, $limit, TRUE);
      foreach ($files_subset as $fid => $file) {
        $operations = ['#type'  => 'operations', '#links' => $this->operations($file)];

        $rows[$fid] = [
          Link::fromTextAndUrl($file->uri, Url::fromUri('base:' . $file->uri, ['attributes' => ['target' => '_blank']])),
          date('Y-m-d', $file->modified),
          $this->format_filesize($file->size),
          $this->minified_filesize($file),
          $this->precentage($file),
          $this->minified_date($file),
          $this->minified_file($file),
          \Drupal::service('renderer')->render($operations),
        ];
      }
    }

    // report on statistics
    drupal_set_message(
      t(
        '@files javascript files (@min_files minified). The size of all original files is @size and the size of all of the minified files is @minified for a savings of @diff (@percent% smaller overall)',
        [
          '@files'     => $number_of_files,
          '@min_files' => $minified_files,
          '@size'      => $this->format_filesize($unminified_size),
          '@minified'  => ($minified_size) ? $this->format_filesize($minified_size) : 0,
          '@diff'      => ($minified_size) ? $this->format_filesize($saved_size) : 0,
          '@percent'   => ($minified_size) ? round($saved_size / $unminified_size * 100, 2) : 0,
        ]
      ),
      'status'
    );

    // The table.
    $form['files'] = [
      '#type'    => 'tableselect',
      '#header'  => [
        t('Original File'),
        t('Last Modified'),
        t('Original Size'),
        t('Minified Size'),
        t('Savings'),
        t('Last Minified'),
        t('Minified File'),
        t('Operations'),
      ],
      '#options' => $rows,
      '#empty'   => t('No files have been found. Please scan using the action link above.'),
    ];

    $form['pager'] = ['#type' => 'pager'];

    // Bulk minify button.
    if ($total) {
      $form['actions'] = [
        '#type'       => 'container',
        '#attributes' => [
          'class' => ['container-inline'],
        ],
      ];
      $form['actions']['action'] = [
        '#type'       => 'select',
        '#options'    => [
          'minify'      => t('Minify (and re-minify)'),
          'minify_skip' => t('Minify (and skip minified)'),
          'restore'     => t('Restore'),
        ],
      ];
      $form['actions']['scope'] = [
        '#type'       => 'select',
        '#options'    => [
          'selected' => t('Selected files'),
          'all'      => t('All files'),
        ],
      ];
      $form['actions']['go'] = [
        '#type'       => 'submit',
        '#value'      => t('Perform action'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'minifyjs_manage_files';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    if (count($form_state->getValue('files'))) {
      $files = minifyjs_load_all_files();

      // get the files to process
      $selected_files = [];
      if ($form_state->getValue('scope') == 'selected') {
        foreach ($form_state->getValue('files') as $fid => $selected) {
          if ($selected) {
            $selected_files[] = $fid;
          }
        }
      }
      else {
        $selected_files = array_keys($files);
      }

      // Build operations
      $operations = array();
      foreach ($selected_files as $fid) {
        switch ($form_state->getValue('action')) {

          // minify all files.
          case 'minify':
            $operations[] = array('minifyjs_batch_minify_file_operation', array($fid));
            break;

          // minify files that have not yet been minified.
          case 'minify_skip':
            $file = $files[$fid];
            if (!$file->minified_uri) {
              $operations[] = array('minifyjs_batch_minify_file_operation', array($fid));
            }
            break;

          // restore un-minified version of a file.
          case 'restore':
            $operations[] = array('minifyjs_batch_remove_minified_file_operation', array($fid));
            break;
        }
      }

      // Build the batch.
      $batch = array(
        'operations'    => $operations,
        'file'          => drupal_get_path('module', 'minifyjs') . '/minifyjs.module',
        'error_message' => t('There was an unexpected error while processing the batch.'),
        'finished'      => 'minifyjs_batch_finished',
      );
      switch ($form_state->getValue('action')) {
        case 'minify':
          $batch['title']        = t('Minifying Javascript Files.');
          $batch['init_message'] = t('Initializing minify javascript files batch.');
          break;

        case 'restore':
          $batch['title']        = t('Restoring Un-Minified Javascript Files.');
          $batch['init_message'] = t('Initializing restore un-minified javascript files batch.');
          break;

      }

      // Start the batch.
      batch_set($batch);
    }
  }

  /**
   * Helper function to format the filesize.
   *
   * @param int $size
   */
  private function format_filesize($size) {
    if ($size) {
      $suffixes   = array('', 'k', 'M', 'G', 'T');
      $base       = log($size) / log(1024);
      $base_floor = floor($base);

      return round(pow(1024, $base - $base_floor), 2) . $suffixes[$base_floor];
    }

    return 0;
  }

  /**
   * Helper function to format date.
   *
   * @param stdClass $file
   */
  private function minified_date($file) {
    if ($file->minified_modified > 0) {
      return date('Y-m-d', $file->minified_modified);
    }

    return '-';
  }

  /**
   * Helper function to format the minified filesize.
   *
   * @param stdClass $file
   */
  private function minified_filesize($file) {
    if ($file->minified_uri) {
      if ($file->minified_size > 0) {
        return $this->format_filesize($file->minified_size);
      }

      return 0;
    }

    return '-';
  }

  /**
   * Helper function to format the file url.
   *
   * @param stdClass $file
   */
  private function minified_file($file) {
    if (!empty($file->minified_uri)) {
      return Link::fromTextAndUrl(basename($file->minified_uri), Url::fromUri(file_create_url($file->minified_uri), ['attributes' => ['target' => '_blank']]));
    }

    return '-';
  }

  /**
   * Helper function to format the savings percentage.
   *
   * @param stdClass $file
   */
  private function precentage($file) {
    if ($file->minified_uri) {
      if ($file->minified_size > 0) {
        return round(($file->size - $file->minified_size) / $file->size * 100, 2) . '%';
      }

      return 0 . '%';
    }

    return '-';
  }

  /**
   * Helper function to return the operations available for the file.
   *
   * @param stdClass $file
   */
  private function operations($file) {
    $operations = [];

    if (empty($file->minified_uri)) {
      $operations['minify'] = [
        'title' => t('Minify'),
        'url'   => Url::fromUri('base:/admin/config/development/performance/js/' . $file->fid . '/minify'),
      ];
    }
    else {
      $operations['reminify'] = [
        'title' => t('Re-Minify'),
        'url'   => Url::fromUri('base:/admin/config/development/performance/js/' . $file->fid . '/minify'),
      ];
      $operations['restore'] = [
        'title' => t('Restore'),
        'url'   => Url::fromUri('base:/admin/config/development/performance/js/' . $file->fid . '/restore'),
      ];
    }

    return $operations;
  }
}