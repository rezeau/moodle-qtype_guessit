@qtype @qtype_guessit
Feature: Test all the basic functionality of this guessit (wordle) question type
    In order to evaluate students' responses, as a teacher I need to
  create and preview guessit questions with wordle option.

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
  Scenario: Create, edit and preview a wordle question
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "Guess It" question filling the form with:
      | Question name                     | guessit-001                    |
      | Instructions                      | Guess this dish name.          |
      | Guessit word(s)                   | PIZZA                          |
      | General feedback                  | Enjoy your pizza!              |
      | Wordle Option: Guess a word       | 1                              |
      | Maximum number of tries           | 6                              |
    Then I should see "guessit-001"

    # Edit to test lowercase error.
    And I choose "Edit question" action for "guessit-001" in the question bank
    And I set the field "Guessit word(s)" to "pizza"
    And I press "id_submitbutton"
    Then I should see " ERROR! In the Wordle option, gaps must consist only of CAPITAL LETTERS (A-Z) and no accents"
    And I set the field "Guessit word(s)" to "PIZZA"
    And I press "id_submitbutton"
    Then I should see "guessit-001"

    # Preview a wordle guessit question.
    And I choose "Preview" action for "guessit-001" in the question bank
    Then I should see "Guess this dish name."

    # Enter partially correct answer.
    And I set the field with xpath "//input[contains(@id, '1_p1')]" to "i"
    And I set the field with xpath "//input[contains(@id, '1_p2')]" to "p"
    And I set the field with xpath "//input[contains(@id, '1_p3')]" to "z"
    And I set the field with xpath "//input[contains(@id, '1_p4')]" to "z"
    And I set the field with xpath "//input[contains(@id, '1_p5')]" to "e"
    And I press "Check"
    Then I should see "You've got 2 correctly placed letters and 2 misplaced letters."
    And I should see "5 tries left"
    And I should see "Partially correct"
    And I should see "Marks for this submission: 2.00/5.00."
    And I press "Start again"

    # Enter correct answer.
    And I set the field with xpath "//input[contains(@id, '1_p1')]" to "p"
    And I set the field with xpath "//input[contains(@id, '1_p2')]" to "i"
    And I set the field with xpath "//input[contains(@id, '1_p3')]" to "z"
    And I set the field with xpath "//input[contains(@id, '1_p4')]" to "z"
    And I set the field with xpath "//input[contains(@id, '1_p5')]" to "a"
    And I press "Check"
    Then I should see "Word found in 1 try: PIZZA"
    And I should see "Correct"
    And I should see "Marks for this submission: 5.00/5.00."
    And I press "Submit and finish"
    And I should see "Enjoy your pizza!"
    And I log out
