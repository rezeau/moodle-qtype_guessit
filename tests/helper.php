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
 * Contains the helper class for the select missing words question type tests.
 *
 * @package    qtype_guessit
 * @copyright  2024 Joseph Rézeau <moodle@rezeau.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
/**
 * utilities used by the other test classes
 *
 * @package    qtype_guessit
 * @copyright  2024 Joseph Rézeau <moodle@rezeau.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_guessit_test_helper extends question_test_helper {

    /**
     *  must be implemented or class made abstract
     *
     * @return string
     */
    public function get_test_questions() {
        return ['toomanycooks', 'pizza'];
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public static function get_guessit_question_form_data_toomanycooks() {
        $answerwords = ['Too', 'many', 'cooks', 'spoil', 'the', 'broth.'];
        $answers = [];
        $id = 1;
        foreach ($answerwords as $key => $answer) {
            $id++;
            $answers[$key] = (object) array(
                'question' => '163',
                'answer' => $answer,
                'fraction' => '1',
                'feedback' => 'Feedback text',
                'feedbackformat' => '1',
                'id' => $id,
            );
        }

        $fromform = (object) [
            'idnumber' => '1',
            'category' => '2',
            'contextid' => '1',
            'parent' => '0',
            'name' => 'Generic guessit Question',
            'questiontext' => [
                'text' => 'Guess this English proverb',
                'format' => FORMAT_HTML,
            ],
            'guessitgaps' => 'Too many cooks spoil the broth.',
            'wordle' => '0',
            'gapsizedisplay' => 'gapsizegrow',
            'nbtriesbeforehelp' => '0',
            'nbmaxtrieswordle' => '0',
            'removespecificfeedback' => '0',
            'qtype' => 'guessit',
            'length' => '1',
            'stamp' => 'tjh238.vledev.open.ac.uk+100708154547+JrHygi',
            'version' => 'tjh238.vledev.open.ac.uk+100708154548+a3zh8v',
            'hidden' => '0',
            'timecreated' => '1278603947',
            'timemodified' => '1278603947',
            'createdby' => '3',
            'modifiedby' => '3',
            'defaultmark' => '1.0000000',
            'maxmark' => '1.00000',
            'id' => '117',
            'question' => '163',
            'layout' => '0',
            'answerdisplay' => 'guessit',
            'generalfeedback' => [
                'text' => 'Well-guessed!',
                'format' => FORMAT_HTML,
            ],
            'answers' => $answers,
        ];

        return $fromform;
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public static function get_guessit_question_form_data_pizza() {
        $answerwords = ['P', 'I', 'Z', 'Z', 'A'];
        $answers = [];
        $id = 1;
        foreach ($answerwords as $key => $answer) {
            $id++;
            $answers[$key] = (object) array(
                'question' => '999',
                'answer' => $answer,
                'fraction' => '1',
                'feedback' => 'Feedback text',
                'feedbackformat' => '1',
                'id' => $id,
            );
        }

        $fromform = (object) [
            'idnumber' => '1',
            'category' => '2',
            'contextid' => '1',
            'parent' => '0',
            'name' => 'Generic wordle Question',
            'questiontext' => [
                'text' => 'Guess this Italian dish',
                'format' => FORMAT_HTML,
            ],
            'guessitgaps' => 'PIZZA',
            'wordle' => '1',
            'gapsizedisplay' => 'gapsizegrow',
            'nbtriesbeforehelp' => '0',
            'nbmaxtrieswordle' => '0',
            'removespecificfeedback' => '0',
            'qtype' => 'guessit',
            'length' => '1',
            'stamp' => 'tjh238.vledev.open.ac.uk+100708154547+JrHygiz',
            'version' => 'tjh238.vledev.open.ac.uk+100708154548+a3zh8vz',
            'hidden' => '0',
            'timecreated' => '1278603949',
            'timemodified' => '1278603949',
            'createdby' => '3',
            'modifiedby' => '3',
            'defaultmark' => '1.0000000',
            'maxmark' => '1.00000',
            'id' => '999',
            'question' => '999',
            'layout' => '0',
            'answerdisplay' => 'guessit',
            'generalfeedback' => [
                'text' => 'Enjoy your pizza!',
                'format' => FORMAT_HTML,
            ],
            'answers' => $answers,
        ];

        return $fromform;
    }

}
