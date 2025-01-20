<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * guessit question definition class. Mainly about runtime
 *
 * @package    qtype_guessit
 * @subpackage guessit
 * @copyright  2024 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * guessit question definition class.
 *
 * @package    qtype_guessit
 * @subpackage guessit
 * @copyright  2024 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_guessit_question extends question_graded_automatically_with_countback {

    /**
     * Apparently not used
     *
     * @var string
     */
    public $answer;

    /**
     * Set the size of every gap to the size of the larges so students do not
     * get an idea of the correct answer from gap sizes
     *
     * @var bool
     */
    public $gapsizedisplay;

    /**
     * The number of tries before getting help
     * @var int
     */
    public $nbtriesbeforehelp;

    /**
     * The number of tries to guess the word (Wordle option)
     * @var int
     */
    public $nbmaxtrieswordle;

    /**
     * True if the number of tries reaches nbmaxtrieswordle (Wordle option)
     * @var int
     */
    public $maxreached;

    /**
     * The size of the biggest gap (used when fixedgapsize is true
     * @var int
     */
    public $maxgapsize;

    /**
     * its a whole number, it's only called fraction because it is referred to that in core
     * code
     * @var int
     */
    public $fraction;

    /**
     * How many gaps in this question
     * @var number
     */
    public $gapcount;

    /**
     * The question gaps
     * @var string
     */
    public $guessitgaps;

    /**
     * Remove specific feedback when all gaps have been correctly filled in.
     * @var bool
     */
    public $removespecificfeedback;

    /**
     * Wordle Option: Guess a word.
     * @var bool
     */
    public $wordle;

    /**
     * array of strings as correct question answers
     * @var array
     */
    public $answers = [];

    /**
     * @var array place number => group number of the places in the question
     * text where choices can be put. Places are numbered from 1.
     */
    public $places = [];

    /**
     * get the length of the correct answer
     * @param string $rightanswer
     * @return number
     */
    public function get_size($rightanswer) {
        $rightanswer = htmlspecialchars_decode($rightanswer);
        return strlen($rightanswer);
    }

    /**
     * returns string of place key value prepended with p, i.e. p0 or p1 etc
     * @param int $place stem number
     * @return string the question-type variable name.
     */
    public function field($place) {
        return 'p' . $place;
    }
    /**
     * get expected data types (?)
     * @return array
     */
    public function get_expected_data() {
        $data = [];
        foreach (array_keys($this->places) as $key) {
            $data['p' . $key] = PARAM_RAW_TRIMMED;
        }
        return $data;
    }

    /**
     * Value returned will be written to responsesummary field of
     * the question_attempts table
     *
     * @param array $response
     * @return string
     */
    public function summarise_response(array $response) {
        $summary = "";
        foreach ($response as $value) {
            $summary .= " " . $value . " ";
        }
        return $summary;
    }

    /**
     * Has the user put something in every gap?
     * @param array $response
     * @return boolean
     * Not used. Replaced by check_complete_answer() in the renderer.
     */
    public function is_complete_response(array $response) {
        return;
    }

    /**
     * Returns prompt asking for answer. Called from renderer
     * if question state is invalid.
     *
     * @param array $response
     * @return string
     */
    public function get_validation_error(array $response) {
        return; // Not used by guessit.
    }

    /**
     * What is the correct value for the field
     *
     * @param number $place
     * @return number
     */
    public function get_right_choice_for($place) {
        return $this->places[$place];
    }

    /**
     *
     * @param array $prevresponse
     * @param array $newresponse
     * @return boolean
     *
     * Don't change answer if it is the same
     */
    public function is_same_response(array $prevresponse, array $newresponse) {
        /* if you are moving from viewing one question to another this will
         * discard the processing if the answer has not changed. If you don't
         * use this method it will constantantly generate new question steps and
         * the question will be repeatedly set to incomplete. This is a comparison of
         * the equality of two arrays.
         */
        if ($prevresponse == $newresponse) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * A question is gradable if at least one gap response is not blank
     *
     * @param array $response
     * @return boolean
     */
    public function is_gradable_response(array $response) {
        foreach ($response as $studentanswer) {
            if (($studentanswer !== "")) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return array containing answers that would get full marks
     *
     * @return array
     */
    public function get_correct_response() {
        $response = [];
        foreach ($this->places as $place => $rightanswer) {
            $response[$this->field($place)] = $rightanswer;
        }
        return $response;
    }

    /**
     *
     * @param array $response Passed in from the submitted form
     * @return array
     *
     * Find count of correct answers, used for displaying marks
     * for question. Compares answergiven with right/correct answer
     */
    public function get_num_parts_right(array $response) {
        $numright = 0;
        foreach (array_keys($this->places) as $place) {
            $rightanswer = $this->get_right_choice_for($place);
            if (!isset($response[$this->field($place)])) {
                continue;
            }
            $studentanswer = $response[$this->field($place)];
            if (!array_key_exists($this->field($place), $response)) {
                continue;
            }
            if ($studentanswer === $rightanswer) {
                $numright++;
            }
        }
        return [$numright, $this->gapcount];
    }

    /**
     * Calculate grade and returns an array in the form
     * array(2) (
     * [0] => (int) 1
     * [1] => question_state_gradedright object etc etc etc
     *
     * @param array $response
     * @return array
     */
    public function grade_response(array $response) {
        $right = $this->get_num_parts_right($response)[0];
        $this->fraction = $right / $this->gapcount;
        $grade = [$this->fraction, question_state::graded_state_for_fraction($this->fraction)];
        return $grade;
    }

    /**
     * Required by the interface question_automatically_gradable_with_countback.
     *
     * @param array $responses
     * @param array $totaltries
     * @return number
     */
    public function compute_final_grade($responses, $totaltries) {
        // Grade is not used in this question type.
        return;
    }

    /**
     * Create the appropriate behaviour for an attempt at this question,
     * given the desired (archetypal) behaviour.
     *
     * @param question_attempt $qa the attempt we are creating a behaviour for.
     * @param string $preferredbehaviour the requested type of behaviour.
     * @return question_behaviour the new behaviour object.
     */
    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        GLOBAL $CFG;
        // If guessit behaviour has been installed, make all behaviours default to guessit.
        if (file_exists($CFG->dirroot.'/question/behaviour/guessit/')) {
            return question_engine::make_behaviour('guessit', $qa, 'adaptive');
        }
        return question_engine::make_archetypal_behaviour($preferredbehaviour, $qa);
    }
}
