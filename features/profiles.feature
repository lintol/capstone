Feature: Lintol Capstone API
  In order to manage profiles
  As a user
  I want to be able to use the REST API

  Scenario: profile.store
    Given I am logged in as "coordinator@example.com", who is an administrator
    And I have an API token
    And I want to store a Profile through the API
    And its properties will be:
    """JSON
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
    """JSON
    {
      "attributes": {
        "creatorId": {KNOWN_ID:User},
        "version": "2",
        "name": "My Profil",
        "description": "Foo Profil",
        "uniqueTag": "fdsa"
      }
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
      "description": "Foo Profil",
      "unique_tag": "foobar",
      "version": "3"
    }
    """
    And I want to list Profiles through the API
    When I send a request
    Then the response should be successful
    And print last response
    And the response should contain JSON:
    """
    {
      "id": {KNOWN_ID:Profile},
      "attributes": {
        "name": "My Profil",
        "description": "Foo Profil",
        "uniqueTag": "foobar",
        "version": "3"
      }
    }
    """

  Scenario: profiles.show
    Given I am logged in as "coordinator@profile.com", who is an administrator
    And I have an API token
    And I already have a Profile, with known ID:
    """DB
    {
      "creator_id": {KNOWN_ID:User},
      "name": "My Profil",
      "description": "Foo Profil",
      "unique_tag": "foobar",
      "version": "3"
    }
    """
    And I want to show this Profile through the API
    And I send a request
    Then the response should be successful
    And the response should contain JSON:
    """
    {
      "id": {KNOWN_ID:Profile},
      "attributes": {
        "name": "My Profil",
        "description": "Foo Profil",
        "uniqueTag": "foobar",
        "version": "3"
      }
    }
    """

  Scenario: profiles.update
    Given I am logged in as "coordinator@profile.com", who is an administrator
    And I have an API token
    And I already have an Profile, with known ID:
    """DB
    {
      "creator_id": {KNOWN_ID:User},
      "name": "My Profil",
      "description": "Foo Profil",
      "unique_tag": "foobar",
      "version": "3"
    }
    """
    And I want to update this Profile through the API
    And its properties will be:
    """JSON
    {
      "name": "No Profil",
      "description": "Bar Profil"
    }
    """
    When I send a request
    Then the response should be successful
    And the response should contain JSON:
    """JSON
    {
      "attributes": {
        "creatorId": {KNOWN_ID:User},
        "version": "3",
        "name": "No Profil",
        "description": "Bar Profil",
        "uniqueTag": "foobar"
      }
    }
    """

    Given I want to show this Profile through the API
    And I send a request
    Then the response should be successful
    And the response should contain JSON:
    """JSON
    {
      "attributes": {
        "creatorId": {KNOWN_ID:User},
        "version": "3",
        "name": "No Profil",
        "description": "Bar Profil",
        "uniqueTag": "foobar"
      }
    }
    """
