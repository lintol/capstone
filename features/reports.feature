Feature: Lintol Capstone API
  In order to manage reports
  As a user
  I want to be able to use the REST API

  Scenario: reports.index
    Given I am logged in as "coordinator@report.com", who is an administrator
    And I have an API token
    And I already have a Report, with known ID:
    """DB
    {
      "owner_id": {KNOWN_ID:User},
      "name": "Foo report",
      "errors": 34,
      "warnings": 23,
      "passes": 1,
      "quality_score": 38,
      "content": []
    }
    """
    And I want to list Reports through the API
    When I send a request
    Then the response should be successful
    And print last response
    And the response should contain JSON:
    """
    {
      "id": {KNOWN_ID:Report},
      "attributes": {
        "name": "Foo report",
        "errors": 34,
        "warnings": 23,
        "passes": 1,
        "qualityScore": 38,
        "content": []
      }
    }
    """

  Scenario: reports.show
    Given I am logged in as "coordinator@report.com", who is an administrator
    And I have an API token
    And I already have a Report, with known ID:
    """DB
    {
      "owner_id": {KNOWN_ID:User},
      "name": "Foo report",
      "errors": 34,
      "warnings": 23,
      "passes": 1,
      "quality_score": 38,
      "content": []
    }
    """
    And I want to show this Report through the API
    And I send a request
    Then the response should be successful
    And the response should contain JSON:
    """
    {
      "id": {KNOWN_ID:Report},
      "attributes": {
        "name": "Foo report",
        "errors": 34,
        "warnings": 23,
        "passes": 1,
        "qualityScore": 38,
        "content": []
      }
    }
    """
