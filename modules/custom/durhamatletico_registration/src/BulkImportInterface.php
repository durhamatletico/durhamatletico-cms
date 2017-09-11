<?php

namespace Drupal\durhamatletico_registration;

/**
 * Interface BulkImportInterface.
 */
interface BulkImportInterface {


  public function validateCsv($data);
  public function checkIfCsv($data);
  public function validateColumnHeaders();
  public function checkRequiredFields();

  public function import();
  public function importUser($name);
  public function importRegistration($registration);
  public function getTeamNid($team_name);
  public function getUserId($name);
  public function getDivisionNid($division);
}
