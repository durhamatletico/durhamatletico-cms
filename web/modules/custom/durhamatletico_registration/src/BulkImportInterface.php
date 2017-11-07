<?php

namespace Drupal\durhamatletico_registration;

/**
 * Interface BulkImportInterface.
 */
interface BulkImportInterface {

  /**
   *
   */
  public function validateCsv($file);

  /**
   *
   */
  public function checkIfCsv($data);

  /**
   *
   */
  public function loadCsv($file);

  /**
   *
   */
  public function getLog();

  /**
   *
   */
  public function validateColumnHeaders();

  /**
   *
   */
  public function checkRequiredFields();

  /**
   *
   */
  public function importUser($data);

  /**
   *
   */
  public function importRegistration($registration);

  /**
   *
   */
  public function getTeamNid($team_name, $division);

  /**
   *
   */
  public function getUserId($firstName, $lastName);

  /**
   *
   */
  public function getDivisionNid($division);

  /**
   *
   */
  public function getSeasonNid($divisionNid);

}
