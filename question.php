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
     * The size of the biggest gap (used when fixedgapsize is true
     * @var int
     */
    public $maxgapsize;

    /**
     * Feedback when the response is entirely correct
     * @var string
     */
    public $correctfeedback = '';
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
     * By default Cat is treated the same as cat. Setting it to 1 will make it case sensitive
     * @var bool
     */
    public $casesensitive;

    /**
     * array of strings as correct question answers
     * @var rray
     */
    public $answers = [];


    /**
     * @var array place number => group number of the places in the question
     * text where choices can be put. Places are numbered from 1.
     */
    public $places = [];

    /**
     * @var array of strings, one longer than $places, which is achieved by
     * indexing from 0. The bits of question text that go between the placeholders.
     */
    public $textfragments;

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
     */
    public function is_complete_response(array $response) {
        $gapsfilled = 0;
        $iscomplete = true;
        foreach ($this->answers as $rightanswer) {
            $studentanswer = array_shift($response);
            if (!($studentanswer == "") ) {
                $gapsfilled++;
            }
        }

        if ($gapsfilled < $this->gapcount) {
            $iscomplete = false;
        }
        return $iscomplete;
    }

    /**
     * Returns prompt asking for answer. Called from renderer
     * if question state is invalid.
     *
     * @param array $response
     * @return string
     */
    public function get_validation_error(array $response) {
        return get_string('pleaseenterananswer', 'qtype_guessit');
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
     * called from within renderer in interactive mode
     *
     * @param string $studentanswer
     * @param string $rightanswer
     * @return boolean
     */
    public function is_correct_response($studentanswer, $rightanswer) {
        if (!$this->casesensitive == 1) {
            $studentanswer = core_text::strtolower($studentanswer, 'UTF-8');
            $rightanswer = core_text::strtolower($rightanswer, 'UTF-8');
        }

        if ($this->compare_response_with_answer($studentanswer, $rightanswer)) {
            return true;
        } else if (($studentanswer == "") ) {
            return true;
        } else {
            return false;
        }
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
            if (!$this->casesensitive == 1) {
                $studentanswer = core_text::strtolower($studentanswer, 'UTF-8');
                $rightanswer = core_text::strtolower($rightanswer, 'UTF-8');
            }
            if ($this->compare_response_with_answer($studentanswer, $rightanswer)) {
                $numright++;
            }
        }
        return [$numright, $this->gapcount];
    }

    /**
     * Given a response, reset the parts that are wrong to a blank string.
     * Relevent when usinginteractive with multiple tries behaviour
     * @param array $response a response
     * @return array a cleaned up response with the wrong bits reset.
     */
    public function clear_wrong_from_response(array $response) {
        foreach (array_keys($this->places) as $place) {
            if (!array_key_exists($this->field($place), $response)) {
                continue;
            }
            $studentanswer = $response[$this->field($place)];
            $rightanswer = $this->get_right_choice_for($place);
            if (!$this->casesensitive == 1) {
                $studentanswer = core_text::strtolower($studentanswer);
                $rightanswer = core_text::strtolower($rightanswer);
            }
            if (!$this->compare_response_with_answer($studentanswer, $rightanswer)) {
                $response[$this->field($place)] = '';
            }
        }
        return $response;
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
        $totalscore = 0;
        foreach (array_keys($this->places) as $place) {
            $fieldname = $this->field($place);
            $lastwrongindex = -1;
            $finallyright = false;
            foreach ($responses as $i => $response) {
                $rcfp = $this->get_right_choice_for($place);
                /* break out the loop if response does not contain the key */
                if (!array_key_exists($fieldname, $response)) {
                    continue;
                }
                $resp = $response[$fieldname];
                if (!$this->compare_response_with_answer($resp, $rcfp)) {
                    $lastwrongindex = $i;
                    $finallyright = false;
                } else {
                    $finallyright = true;
                }
            }

            if ($finallyright) {
                $totalscore += max(0, 1 - ($lastwrongindex + 1) * $this->penalty);
            }
        }
        return $totalscore / $this->gapcount;
    }

    /**
     * Compare the answer given with the correct answer, does it match?
     * To normalise white spaces add
     * $studentanswer = preg_replace('/\s+/', ' ', $studentanswer);
     *  before if($disableregex etc etc
     *
     * @param string $studentanswer
     * @param string $rightanswer
     * @param boolean $disableregex
     * @return boolean
     */
    public function compare_response_with_answer($studentanswer, $rightanswer) {
        $correctanswer = $this->special_string_comparison($studentanswer, $rightanswer);
        return $correctanswer;
    }
    /**
     * get an array with information about marking of gap in the form
     * array(1) (  [p1] => array(3)(
     * [value] => (string) 0
     *  [fraction] => (int) 1
     * ))
     *
     * @param question_attempt $qa
     * @param question_display_options $options
     * @return array
     */
    public function get_markedgaps(question_attempt $qa, question_display_options $options) {
        $markedgaps = [];
        $question = $qa->get_question();
        $correctgaps = [];
        foreach (array_keys($question->textfragments) as $place) {
            if ($place < 1) {
                continue;
            }
            $fieldname = $question->field($place);
            $rightanswer = $question->get_right_choice_for($place);
            if (($options->correctness) || ( $options->numpartscorrect)) {
                $response = $qa->get_last_qt_data();

                if (array_key_exists($fieldname, $response)) {
                    if ($question->is_correct_response($response[$fieldname], $rightanswer)) {
                        $markedgaps[$fieldname]['value'] = $response[$fieldname];
                        $markedgaps[$fieldname]['fraction'] = 1;
                        $correctgaps[] = $response[$fieldname];
                    } else {
                        $markedgaps[$fieldname]['value'] = $response[$fieldname];
                        $markedgaps[$fieldname]['fraction'] = 0;
                    }
                }
            }
        }
        return $markedgaps;
    }

    /**
     * Compares two strings to check if they are exactly equal, including special characters like periods.
     *
     * @param string $studentAnswer The student's answer to be compared.
     * @param string $rightAnswer The correct answer to be compared against.
     *
     * @return bool.
     */
    public function special_string_comparison($studentanswer, $rightanswer) {
        // Escape the period in $studentanswer so it matches it as a literal punctuation mark, not as a regex wildcard.
        $pattern = '/^' . preg_quote($studentanswer, '/') . '$/';
        // If $rightanswer matches the literal pattern of $studentanswer, return true (strings are equal according to the rule).
        return preg_match($pattern, $rightanswer) === 1;
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
