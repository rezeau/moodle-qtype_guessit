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
 * The editing form code for this question type.
 * @package qtype_guessit
 * @subpackage guessit
 * @copyright  2025 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');

/**
 * Editing form for the guessit question type
 * @copyright  2025 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * guessit editing form definition.
 *
 * See http://docs.moodle.org/en/Development:lib/formslib.php for information
 * about the Moodle forms library, which is based on the HTML Quickform PEAR library.
 */
class qtype_guessit_edit_form extends question_edit_form {

    /**
     * Doesn't seem to be used
     * @var string
     */
    public $answer;

    /**
     * Add guessit specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        global $CFG, $OUTPUT, $SESSION;
        $mform->removeelement('questiontext');
        $mform->addElement('editor', 'questiontext', get_string('instructions', 'qtype_guessit'), ['rows' => 10],
                $this->editoroptions);

        $mform->setType('questiontext', PARAM_RAW);
        $mform->addHelpButton('questiontext', 'instructions', 'qtype_guessit');

        $mform->removeelement('generalfeedback');

        // Default mark will be set to 1 * number of fields.
        $mform->removeelement('defaultmark');

        /* Guess one word only 'a la Wordle' instead of a phrase/set of words. */
        $mform->addElement('advcheckbox', 'wordle', get_string('wordle', 'qtype_guessit'));
        $mform->addHelpButton('wordle', 'wordle', 'qtype_guessit');

        $mform->addElement('text', 'guessitgaps', get_string('guessitgaps', 'qtype_guessit'), 'maxlength="254" size="50"');
        $mform->setDefault('guessitgaps', '');
        $mform->addRule('guessitgaps', get_string('wordssmissing', 'qtype_guessit'), 'required', null, 'client');
        $mform->setType('guessitgaps', PARAM_TEXT);
        $mform->addHelpButton('guessitgaps', 'guessitgaps', 'qtype_guessit');
        // Prepare number of tries option.
        $nbtriesbeforehelp = [
            0 => get_string('never', 'qtype_guessit'),
            6 => '6',
            8 => '8',
            10 => '10',
            12 => '12',
            14 => '14',
        ];
        // Select how many prevtries before help is available.
        $mform->addElement('select', 'nbtriesbeforehelp',
                get_string('nbtriesbeforehelp', 'qtype_guessit'), $nbtriesbeforehelp);
        // Hide the field 'nbtriesbeforehelp' if 'wordle' is selected.
        $mform->hideIf('nbtriesbeforehelp', 'wordle', 'checked');
        $mform->addHelpButton('nbtriesbeforehelp', 'nbtriesbeforehelp', 'qtype_guessit');
        $mform->setDefault('nbtriesbeforehelp', 10);

        $nbmaxtrieswordle = [
            6 => '6',
            8 => '8',
            10 => '10',
            12 => '12',
            14 => '14',
        ];

        // Maximum number of tries to guess the word (Wordle option).
        $mform->addElement('select', 'nbmaxtrieswordle',
                get_string('nbmaxtrieswordle', 'qtype_guessit'), $nbmaxtrieswordle);
        // Hide the field 'nbmaxtrieswordle' if 'wordle' is NOT selected.
        $mform->hideIf('nbmaxtrieswordle', 'wordle', 'not checked');
        $mform->addHelpButton('nbmaxtrieswordle', 'nbmaxtrieswordle', 'qtype_guessit');
        $mform->setDefault('nbmaxtrieswordle', 10);

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question')
                , ['rows' => 10], $this->editoroptions);

        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');

    }

    /**
     * Perform any preprocessing needed on the data passed in
     * before it is used to initialise the form.
     * @param object $question the data being passed to the form.
     * @return object $question the modified data.
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        if (!empty($question->options)) {
            $question->answerdisplay = 'guessit';
        }
        return $question;
    }

    /**
     * Check that if wordle option is selected
     * then the word entered in guessitgaps is in capital letters.
     * @param array $fromform
     * @param array $files
     * @return boolean
     */
    public function validation($fromform, $files) {
        $errors = [];
        $wordle = $fromform['wordle'];
        $guessitgaps = $fromform['guessitgaps'];
        if ($wordle) {
            if (preg_match('/[^A-Z]/', $guessitgaps) ) {
                $errors['guessitgaps'] = get_string('wordlecapitalsonly', 'qtype_guessit');
            };
            if (strlen($guessitgaps) > 8) {
                $errors['guessitgaps'] = get_string('wordletoolong', 'qtype_guessit');
            }
        }
        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }
    /**
     * Name of this question type
     * @return string
     */
    public function qtype() {
        return 'guessit';
    }

}
