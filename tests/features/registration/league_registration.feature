Feature: Allow registered users to sign up for a leauge

  Scenario: Anonymous users may not create a registration node
    Given I am an anonymous user
    When I go to "node/add/registration"
    And I should get a 403 HTTP response

    @api
  Scenario: Authenticated users may create a registration node
    Given I am logged in as a user with the "authenticated user" role
    When I go to "node/add/registration"
    Then I should see "Registration"
    And I should get a 200 HTTP response

    @api
  Scenario: Users may only view their own registrations, not anyone else's
    Given users:
    | name     | mail            | status |
    | testuser | testuser@example.com | 1      |
    Given "registration" content:
    | title                     | author | path | status |
    | testuser | testuser      | /testregistration | 1 |
    Given I am logged in as a user with the "authenticated user" role
    When I go to "/testregistration"
    Then I should get a 403 HTTP response
    When I go to "node/add/registration"
    Given I select "F" from "Shirt Type"
    And I select "M" from "Shirt Size"
    And for "Shirt Number" I enter "4"
    And I select "I would like to be placed on a team" from "Team"
    And I select "[Futsal] Spring 2018 - Division 1" from "Registration for"
    And I press "Save"
    Then I should see "Submitted by"
    And I should get a 200 HTTP response

    @api
  Scenario: Users must enter an NC address on account creation
    Given I am on "/user/register"
    And I fill in "Email address" with a random e-mail address
    And I fill in "Username" with a random string
    And I fill in "Password" with "password"
    And I fill in "Confirm password" with "password"
    And I fill in "First Name" with "behat"
    And I fill in "Last Name" with "tester"
    And I fill in "Phone" with "919-123-4567"
    And I select "English" from "Preferred Language"
    And I fill in "Street address" with "123"
    And I fill in "City" with "Durham"
    And I select "California" from "State"
    And I fill in "Zip code" with "90210"
    And I wait 6 seconds
    And I press "Create new account"
    Then I should see "Please enter a valid NC address"
