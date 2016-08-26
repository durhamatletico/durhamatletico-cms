Feature: Allow registered users to sign up for a leauge

  Scenario: Anonymous users may not register
    Given I am an anonymous user
    When I go to the homepage
    Then I should not see "My Account"

  Scenario: Anonymous users may not create a registration node
    Given I am an anonymous user
    When I go to "node/add/registration"
    Then I should not see "Registration"
    And I should get a 403 HTTP response

    @api
  Scenario: Administrative users may create a registration node
    Given I am logged in as a user with the "administrator" role
    When I go to "node/add/registration"
    Then I should see "Registration"
    And I should get a 200 HTTP response

    @api
  Scenario: Authenticated users may create a registration node
    Given I am logged in as a user with the "authenticated user" role
    When I go to "node/add/registration"
    Then I should see "Registration"
    And I should get a 200 HTTP response

    @api
  Scenario: Player users may create a registration node
    Given I am logged in as a user with the "player" role
    When I go to "node/add/registration"
    Then I should see "Registration"
    And I should get a 200 HTTP response

    @api
  Scenario: Captain users may create a registration node
    Given I am logged in as a user with the "team_captain" role
    When I go to "node/add/registration"
    Then I should see "Registration"
    And I should get a 200 HTTP response


  @api
  Scenario: Users should not see the node title
    Given I am logged in as a user with the "authenticated user" role
    When I go to "node/add/registration"
    Then I should not see "Player name"
    And I should not see "Revision information"
    Given I select "F" from "Shirt Type"
    And I select "M" from "Shirt Size"
    And I select "[Futsal] Free Agents" from "Team"
    And I select "[Futsal] Fall 2016 - Division 2" from "Registration for"
    And for "Shirt Number" I enter "4"
    When I press "Save"
    Then I should see "has been created"

    @api @needswork
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
    And I select "[Futsal] Free Agents" from "Team"
    And I select "[Futsal] Fall 2016 - Division 1" from "Registration for"
    And I press "Save"
    Then I should see "Submitted by"
    And I should get a 200 HTTP response

    @api
  Scenario: Users should see a link to register for the league
      Given I am logged in as a user with the "authenticated user" role
      When I go to "/user"
      Then I should see the link "click here to create a registration"
      When I follow "click here to create a registration"
      And I select "[Futsal] Fall 2016 - Division 1" from "Registration for"
      Given I select "F" from "Shirt Type"
      And I select "M" from "Shirt Size"
      And for "Shirt Number" I enter "4"
      And I select "[Soccer] Free Agents" from "Team"
      And I press "Save"
      Then I should see "Submitted by"
      And I should get a 200 HTTP response
      When I go to "/user"
      Then I should not see the text "Paid. Thank you"

    @api
  Scenario: Users are redirected to node/add/registration after creating an account
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
    And I select "North Carolina" from "State"
    And I fill in "Zip code" with "27701"
    And I wait 6 seconds
    And I press "Create new account"
    Then I should be on "/node/add/registration"

