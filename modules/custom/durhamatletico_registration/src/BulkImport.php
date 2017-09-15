<?php

namespace Drupal\durhamatletico_registration;

use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

/**
 * Class BulkImport.
 */
class BulkImport implements BulkImportInterface {

  private $file;
  private $parsedCsv;
  private $log;

  public function importUser($data) {
    // Look up existing user.
    $parts = explode(' ', $data->name);
    $lastName = count($parts) > 1 ? array_pop($parts) : 'Unknown';
    $firstName = implode(' ', $parts);
    $result = $this->getUserId($firstName, $lastName);

    if (!count($result)) {
      // Create a new user.
      $values = [
        'field_first_name' => $firstName,
        'field_last_name' => $lastName,
      ];
      $user = User::create($values);
      $user->setUsername($firstName . $lastName);
      $violations = $user->validate();
      if (count($violations)) {
        if (in_array('name', $violations->getFieldNames())) {
          $message = t('A user already exists for username @username. Try editing the existing user and setting their first and last name.',
            [
              '@username' => $firstName . $lastName,
            ]);
          \Drupal::logger('durhamatletico_registration')->error($message);
          $this->log['messages']['duplicate_username'] = $message;
          $this->log['success'] = FALSE;
          $this->log['uid'] = $user->id();
          $this->log['name'] = $firstName . ' ' . $lastName;

          return;
        }
      }
      $user->enforceIsNew(TRUE);
      $user->activate();
      $user->save();
      $message = t('Created a new user account for @name', [
        '@name' => $firstName . ' ' . $lastName,
      ]);
      \Drupal::logger('durhamatletico_registration')->info($message);
      $this->log['success'] = TRUE;
      $this->log['messages']['new_user_created'] = $message;
    }
    else {
      $user = User::load(current($result));
      $this->log['uid'] = $user->id();
      $this->log['name'] = $firstName . ' ' . $lastName;
    }
    // TODO: Logic to handle people with the same name. For now, I don't think
    // we have this condition.
    $data->userName = $user->getAccountName();
    $data->uid = $user->id();
    $this->importRegistration($data);
  }

  /**
   * {@inheritdoc}
   */
  public function getSeasonNid($divisionNid) {
    $result = \Drupal::entityQuery('node')
      ->condition('type', 'season')
      ->condition('field_divisions', [$divisionNid], 'IN')
      ->condition('status', 1)
      ->execute();
    return current($result);
  }

  /**
   * {@inheritdoc}
   */
  public function importRegistration($registration) {
    // Check to see if existing registrations exist for this person for
    // this team.
    $teamNid = $this->getTeamNid($registration->teamName, $registration->division);
    // If the user is a captain, update the team node and set them as a
    // a captain.
    if ($registration->isCaptain) {
      $teamNode = Node::load($teamNid);
      $captain_nids = array_merge(
        $teamNode->get('field_captains')->getValue(),
        [['target_id' => $registration->uid]]
      );
      $captain_nids = array_map("unserialize", array_unique(array_map("serialize", $captain_nids)));
      $teamNode->get('field_captains')->setValue($captain_nids);
      $teamNode->save();
    }
    $divisionNid = $this->getDivisionNid($registration->division);
    $seasonNid = $this->getSeasonNid($divisionNid);
    $user = User::load($registration->uid);
    $result = \Drupal::entityQuery('node')
      ->condition('field_registration_teams', $teamNid)
      ->condition('type', 'registration')
      ->condition('uid', $registration->uid)
      ->execute();
    if (count($result)) {
      // Already a reg, don't do anything.
      $message = t('A registration already exists for @user on team @team.', [
        '@user' => $registration->userName,
        '@team' => $registration->teamName,
      ]);
      \Drupal::logger('durhamatletico_registration')->warning($message);
      $this->log['messages']['registration_exists'] = $message;
      $this->log['success'] = TRUE;
      // Update balance due, if numeric and doesn't match existing reg.
      if (is_numeric($registration->balanceDue)) {
        $existingRegistration = Node::load(current($result));
        $existingBalanceDue = (int) $existingRegistration->get('field_balance_due')->value;
        $newBalanceDue = (int) $registration->balanceDue * 100;
        if ($existingBalanceDue !== $newBalanceDue) {
          // Update the balance due.
          $existingRegistration->set('field_balance_due', $newBalanceDue);
          $existingRegistration->save();
          $message = t('Updated balance due from @old to @new for @user',
            [
              '@old' => $existingBalanceDue,
              '@new' => $newBalanceDue,
              '@user' => $user->getAccountName()
            ]
          );
          \Drupal::logger('durhamatletico_registration')->warning($message);
          $this->log['messages']['updated_balance'] = $message;
        }
      }
      return;
    }
    // Create a new registration.
    $values = [
      'type' => 'registration',
      'field_registration_teams' => $teamNid,
      'field_balance_due' => $registration->balanceDue,
      'field_registration_for' => $divisionNid,
      'field_registration_season' => $seasonNid,
      'field_registration_shirt_number' => 99,
      'field_admin_comments' => 'Generated by importer.',
    ];
    $registrationNode = Node::create($values);
    $registrationNode->setRevisionLogMessage('Generated by importer.');
    $registrationNode->setOwner($user);
    $registrationNode->enforceIsNew();
    $registrationNode->save();
    $message = t('Created a new registration for user @user on team @team',
      [
        '@user' => $user->getAccountName(),
        '@team' => $registration->teamName,
      ]
    );
    $this->log['messages']['new_registration'] = $message;
    \Drupal::logger('durhamatletico_registration')->info($message);
  }

  /**
   * {@inheritdoc}
   */
  public function getUserId($firstName, $lastName) {
    return \Drupal::entityQuery('user')
      ->condition('field_first_name', $firstName, 'CONTAINS')
      ->condition('field_last_name', $lastName, 'CONTAINS')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getDivisionNid($division) {
    $result = \Drupal::entityQuery('node')
      ->condition('type', 'league')
      ->condition('title', $division, 'CONTAINS')
      ->execute();
    return current($result);
  }

  /**
   * {@inheritdoc}
   */
  public function validateCsv($file) {
    $data = $this->loadCsv($file);
    try {
      $this->checkIfCsv($data);
      $this->validateColumnHeaders();
    }
    catch (\Exception $e) {
      \Drupal::logger('durhamatletico_registration')->error($e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function loadCsv($file) {
    $file_uri = $file->getFileUri();
    return file_get_contents($file_uri);
  }

  /**
   * {@inheritdoc}
   */
  public function import($file, &$context) {
  }

  /**
   * {@inheritdoc}
   */
  public function getLog() {
    return $this->log;
  }

  /**
   * {@inheritdoc}
   */
  public function getTeamNid($team_name, $division) {
    $divisionNid = $this->getDivisionNid($division);
    $divisionNode = Node::load($divisionNid);
    $divisionTeams = $divisionNode->get('field_teams')->referencedEntities();
    foreach ($divisionTeams as $team) {
      if (strpos($team->getTitle(), $team_name) !== FALSE) {
        return $team->id();
      }
    }
    // If we made it this far, we didn't find the team. Throw an error.
    throw new \Exception('No team found.');
  }

  /**
   * {@inheritdoc}
   */
  public function checkIfCsv($data) {
    $this->parsedCsv = str_getcsv($data, "\n");
    if (!is_array($this->parsedCsv)) {
      throw new \Exception('Does not appear that data is in CSV format.');
    }
    return $this->parsedCsv;
  }

  /**
   * {@inheritdoc}
   */
  public function validateColumnHeaders() {
    $actualHeaders = current($this->parsedCsv);
    $expectedHeaders = 'Division,Team,Name,Shirt Number,Balance Due,Is Captain';
    if ($actualHeaders !== $expectedHeaders) {
      throw new \Exception('Did not find expected headers in CSV file.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequiredFields() {
    // TODO: Make sure every required cell is populated.
  }

}
