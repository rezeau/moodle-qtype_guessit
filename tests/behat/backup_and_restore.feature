@qtype @qtype_guessit
Feature: Test duplicating a quiz containing guessit and wordle questions
  As a teacher
  In order to re-use my courses containing guessit and wordle questions
  I need to be able to backup and restore them

  Background:
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype   | name  | template     |
      | Test questions   | guessit | guessit-001 | toomanycooks |
      | Test questions   | guessit | wordle-001 | pizza |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |
    And quiz "Test quiz" contains the following questions:
      | guessit-001 | 1 |
      | wordle-001  | 1 |
    And the following config values are set as admin:
      | enableasyncbackup | 0 |

  @javascript
  Scenario: Backup and restore a course containing guessit and wordle questions
    When I am on the "Course 1" course page logged in as admin
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 2 |
      | Schema | Course short name | C2       |
    And I am on the "Course 2" "core_question > course question bank" page
    Then I should see "guessit-001"
    And I should see "wordle-001"
    And I choose "Edit question" action for "guessit-001" in the question bank
    Then the following fields match these values:
      | Question name                     | guessit-001                       |
      | Instructions                      | <p>Guess this English proverb</p> |
      | Guessit word(s)                   | Too many cooks spoil the broth.   |
    And I press "Cancel"
    And I choose "Edit question" action for "wordle-001" in the question bank
    And I wait "2" seconds
    Then the following fields match these values:
      | Question name                     | wordle-001                     |
      | Instructions                      | <p>Guess this Italian dish</p> |
      | Guessit word(s)                   | PIZZA                          |
