@qtype @qtype_guessit
Feature: Test all the basic functionality of this guessit question type
    In order to evaluate students' responses, as a teacher I need to
  create and preview guessit questions.

  Background:
    Given the following "users" exist:
        | username | firstname | lastname | email               |
        | teacher  | Mark      | Allright | teacher@example.com |
    And the following "courses" exist:
        | fullname | shortname | category |
        | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
        | user    | course | role           |
        | teacher | C1     | editingteacher |

  @javascript
  Scenario: Create and preview a guessit question
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "Guess It" question filling the form with:
      | Question name                     | guessit-001                    |
      | Instructions                      | Guess this English proverb.    |
      | Guessit word(s)                   | Too many cooks spoil the broth.|
      | General feedback                  | Well-done!                     |
      | How many tries before giving help | 6                              |
    Then I should see "guessit-001"

    # Preview it.
    And I choose "Preview" action for "guessit-001" in the question bank
    Then I should see "Guess this English proverb."
    And I set the field with xpath "//input[contains(@id, '1_p1')]" to "too"
    And I set the field with xpath "//input[contains(@id, '1_p2')]" to "much"
    And I set the field with xpath "//input[contains(@id, '1_p3')]" to "cooks"
    And I set the field with xpath "//input[contains(@id, '1_p4')]" to "spoil"
    And I set the field with xpath "//input[contains(@id, '1_p5')]" to "the"
    And I set the field with xpath "//input[contains(@id, '1_p6')]" to "broth"
    And I press "Check"
    Then I should see "You found 3 words out of 6."
    And I should see "Partially correct"
    And I should see "Marks for this submission: 3.00/6.00."

    # Trying the Get Help feature
    And I click on "Get help" "button"
    Then I should see "Help will be available after 5 more tries!"
    And I set the field with xpath "//input[contains(@id, '1_p1')]" to "Too"
    And I set the field with xpath "//input[contains(@id, '1_p2')]" to "many"
    And I press "Check"
    Then I should see "You found 5 words out of 6."
    And I set the field with xpath "//input[contains(@id, '1_p6')]" to "brother"
    And I press "Check"
    And I set the field with xpath "//input[contains(@id, '1_p6')]" to "brotherly"
    And I press "Check"
    And I set the field with xpath "//input[contains(@id, '1_p6')]" to "brotherhood"
    And I press "Check"
    And I click on "Get help" "button"
    Then I should see "Help will be available after 1 more try!"
    And I set the field with xpath "//input[contains(@id, '1_p6')]" to "boat"
    And I press "Check"
    Then I should see "You found 5 words out of 6."
    And I click on "Get help" "button"

    # Completing the gaps and submitting
    Then I should see "Too many cooks spoil the broth."
    And I set the field with xpath "//input[contains(@id, '1_p6')]" to "broth."
    And I press "Check"    
    Then I should see "All words found in 7 tries."
    And I press "Submit and finish"
    And I should see "Well-done!"
    And I should see "Correct"
    And I should see "Marks for this submission: 6.00/6.00."
    And I log out
