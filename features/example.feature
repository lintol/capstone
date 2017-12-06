Feature: Lintol Capstone API
  In order to manage profiles
  As a user
  I want to be able to use the REST API

  Scenario: profile.store
    Given I am logged in as "coordinator@example.com", who is an administrator
    And I have an API token
    And I want to store a Profile through the API
    And its properties will be:
    """DB
    {
      "creatorId": {KNOWN_ID:User},
      "version": "2",
      "name": "My Profil",
      "description": "Foo Profil",
      "uniqueTag": "fdsa"
    }
    """
    When I send a request
    Then the response should be successful
    And the response should have an "id" property, which is a uuid
    And the response should contain JSON:
    """
    {
      "creatorId": {KNOWN_ID:User},
      "version": "2",
      "name": "My Profil",
      "description": "Foo Profil",
      "uniqueTag": "fdsa"
    }
    """

  Scenario: profiles.index
    Given I am logged in as "coordinator@profile.com", who is an administrator
    And I have an API token
    And I already have a Profile, with known ID:
    """DB
    {
      "creator_id": {KNOWN_ID:User},
      "name": "My Profil",
      "description": "Foo Profil"
    }
    """
    And I have this Profile as one of my profiles
    And I want to list Profiles through the API
    When I send a request
    Then the response should be successful
    And print last response
    And the response should contain JSON:
    """
    {
      "id": {KNOWN_ID:Profile},
      "field_1": "Value",
      "compound_field_name": "Subthing"
    }
    """

    Given I want to list Profile through the API
    When I send a request with query:
    """JSON
    {
      "relatedToProfileId": "notanid"
    }
    """
    Then the response should be unprocessable because "uuid" has problem "Invalid Profile UUID"

  Scenario: profiles.show
    Given I am logged in as "coordinator@profile.com", who is an administrator
    And I have an API token
    And I already have an Profile, with known ID:
    """DB
    {
      "id": {KNOWN_ID:Profile},
      "field_1": "Value",
      "compound_field_name": "Subthing"
    }
    """
    And I have this Profile as one of my profiles
    And I want to show this Profile through the API
    And I send a request
    Then the response should be successful
    And the response should contain JSON:
    """JSON
    {
      "otherProfileId": {KNOWN_ID:Profile},
      "fieldInJson": "Value2",
      "compoundField": {
        "name": "Superthing"
      }
    }
    """

  Scenario: profiles.destroy
    Given I am logged in as "coordinator@profile.com", who is an administrator
    And I have an API token
    And I already have an Profile, with known ID:
    """DB
    {
      "field_1": "Value",
      "compound_field_name": "Subthing"
    }
    """
    And I have this Profile as one of my profiles
    And I want to destroy this Profile through the API
    And I send a request
    Then the response should be successful

    Given I want to show this Profile through the API
    And I send a request
    Then the response should be missing

  Scenario: profiles.update
    Given I am logged in as "coordinator@profile.com", who is an administrator
    And I have an API token
    And I already have an Profile, with known ID:
    """DB
    {
      "field_1": "Value",
      "compound_field_name": "Subthing"
    }
    """
    And I have this Profile as one of my profiles
    And I want to update this Profile through the API
    And its properties will be:
    """JSON
    {
      "fieldAsJson": {KNOWN_ID:Profile}
    }
    """
    When I send a request
    Then the response should be successful
    And the response should contain JSON:
    """JSON
    {
      "fieldAsJson": {KNOWN_ID:Profile},
      "compoundField": {
        "name": "Subthing
      }
    }
    """

    Given I want to show this Profile through the API
    And I send a request
    Then the response should be successful
    And the response should contain JSON:
    """
    {
      "fieldAsJson": {KNOWN_ID:Profile},
      "compoundField": {
        "name": "Subthing
      }
    }
    """
