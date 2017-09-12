<?php

namespace Drupal\durhamatletico_registration;

use Drupal\file\Entity\File;
/**
 * Interface BulkImportInterface.
 */
interface BulkImportInterface {


  public function __construct(File $file);
  public function validateCsv();
  public function checkIfCsv($data);
  public function loadCsv();
  public function validateColumnHeaders();
  public function checkRequiredFields();

  public function import();
  public function importUser($data);
  public function importRegistration($registration);
  public function getTeamNid($team_name, $division);
  public function getUserId($name);
  public function getDivisionNid($division);
  public function getSeasonNid($divisionNid);
}
