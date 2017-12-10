Feature: Lintol Capstone API
  In order to manage processors
  As a user
  I want to be able to use the REST API

  Scenario: processor.store
    Given I am logged in as "coordinator@example.com", who is an administrator
    And I have an API token
    And I want to store a Processor through the API
    And its properties will be:
    """JSON
    {
      "creatorId": {KNOWN_ID:User},
      "name": "behat processor [test]",
      "description": "processes csv files",
      "uniqueTag": "csv-101",
      "module": "csv_processor",
      "content": "import foobar"
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
        "name": "behat processor [test]",
        "description": "processes csv files",
        "uniqueTag": "csv-101",
        "module": "csv_processor",
        "content": "import foobar"
      }
    }
    """

  Scenario: processors.index
    Given I am logged in as "coordinator@processor.com", who is an administrator
    And I have an API token
    And I already have a Processor, with known ID:
    """DB
    {
      "creator_id": {KNOWN_ID:User},
      "name": "behat processor [test]",
      "description": "processes csv files",
      "unique_tag": "csv-101",
      "module": "csv_processor",
      "content": "import foobar"
    }
    """
    And I want to list Processors through the API
    When I send a request
    Then the response should be successful
    And print last response
    And the response should contain JSON:
    """
    {
      "id": {KNOWN_ID:Processor},
      "attributes": {
        "creatorId": {KNOWN_ID:User},
        "name": "behat processor [test]",
        "description": "processes csv files",
        "uniqueTag": "csv-101",
        "module": "csv_processor",
        "content": "import foobar"
      }
    }
    """
