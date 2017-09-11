<?php

namespace Drupal\durhamatletico_registration;

/**
 * Class BulkImport.
 */
class BulkImport implements BulkImportInterface {

  /**
   * Constructs a new BulkImport object.
   */
  public function __construct() {

  }

  public function validateCsv() {
    return FALSE;
  }

}
