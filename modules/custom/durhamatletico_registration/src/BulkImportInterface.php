<?php

namespace Drupal\durhamatletico_registration;

/**
 * Interface BulkImportInterface.
 */
interface BulkImportInterface {


  public function validateCsv($data);
  public function checkIfCsv($data);
  public function validateColumnHeaders($data);
}
