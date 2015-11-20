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
    And I press "Save"
    Then I should see "Submitted by"
    And I should get a 200 HTTP response
    Given I am logged in as "testuser"
    When I go to "/testregistration"
    # This currently doesn't work because the node author isn't properly
    # set to `testuser` by DrupalExtension.
    # Then I should get a 200 HTTP response
    # And I should see "Submitted by testuser"

  @api
  Scenario: Users may only create one registration node per league
    Given I am logged in as a user with the "authenticated user" role
    When I go to "node/add/registration"
    And I press "Save"
    Then I should see "Submitted by"
    And I should get a 200 HTTP response
    When I go to "node/add/registration"
    Then I should see "Member for"
   

    @api
  Scenario: Users should see a link to register for the league
      Given I am logged in as a user with the "authenticated user" role
      When I go to "/user"
      Then I should see the link "Click here to register"
      When I follow "Click here to register"
      And I press "Save"
      Then I should see "Submitted by"
      And I should get a 200 HTTP response
      When I go to "/user"
      Then I should not see the link "Click here to register"

  Scenario: Users may pay for an individual registration

  Scenario: Users may pay for a team registration
