<?php

namespace Drupal\durhamatletico_registration;

use Drupal\Core\File\File;


/**
 * Class BulkImport.
 */
class BulkImport implements BulkImportInterface {

  private $parsedCsv;

  public function importUser($name) {
    // TODO: Implement importUser() method.
  }

  public function importRegistration($registration) {
    // TODO: Implement importRegistration() method.
  }

  public function getUserId($name) {
    // TODO: Implement getUserId() method.
  }

  public function getDivisionNid($division) {
    // TODO: Implement getDivisionNid() method.
  }

  /**
   * Constructs a new BulkImport object.
   */
  public function __construct() {
  }

  public function validateCsv($data) {
    try {
      $this->checkIfCsv($data);
      $this->validateColumnHeaders($data);
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      return FALSE;
    }
    return TRUE;
  }

  public function import() {
    // Iterate over all rows, except the header.
    //
  }

  public function getTeamNid($team_name) {

  }

  public function checkIfCsv($data) {
    $this->parsedCsv = str_getcsv($data);
    if (!is_array($this->parsedCsv)) {
      throw new \Exception('Does not appear that data is in CSV format.');
    }
  }

  public function validateColumnHeaders() {
    $actualHeaders = array_slice($this->parsedCsv, 0, 5);
    $expectedHeaders = ['Division', 'Team', 'Name', 'Shirt Number', 'Balance Due', 'Is Captain'];
    if (count(array_diff($actualHeaders, $expectedHeaders))) {
      throw new \Exception('Did not find expected headers in CSV file.');
    }
  }

  public function checkRequiredFields() {
    // TODO:
  }

}
