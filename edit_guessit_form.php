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
 * @copyright  2024 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on work by 2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');

/**
 * Editing form for the guessit question type
 * @copyright  2024 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on work by 2017 Marcus Green
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
        $mform = $this->form_setup($mform);

        $mform->addElement('html', '<div id="questiontext" >');
        $mform->addElement('editor', 'questiontext', get_string('questiontext', 'question'), ['rows' => 10],
                $this->editoroptions);
        $mform->addElement('html', '</div>');

        $mform->setType('questiontext', PARAM_RAW);
        $mform->addHelpButton('questiontext', 'questiontext', 'qtype_guessit');

        $mform->removeelement('generalfeedback');

        // Default mark will be set to 1 * number of fields.
        $mform->removeelement('defaultmark');

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question')
                , ['rows' => 10], $this->editoroptions);

        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');

        $config = get_config('qtype_guessit');
        $mform = $this->get_options($mform, $config);

    }

    /**
     * Add the (mainly) checkboxes for customising how a question
     * works/displays
     *
     * @param MoodleQuickform $mform
     * @param \stdClass $config
     * @return MoodleQuickform
     */
    protected function get_options(MoodleQuickform $mform, $config) {
        $mform->addElement('header', 'feedbackheader', get_string('options', 'question'));

        $gapsizedisplaytypes = ["gapsizematchword" => get_string('gapsize_matchword', 'qtype_guessit'),
            "gapsizefixed" => get_string('gapsize_fixed', 'qtype_guessit'),
            "gapsizegrow" => get_string('gapsize_grow', 'qtype_guessit')];
        $mform->addElement('select', 'gapsizedisplay', get_string('gapsize_display', 'qtype_guessit'), $gapsizedisplaytypes);
        $mform->addHelpButton('gapsizedisplay', 'gapsize_display', 'qtype_guessit');

        /* Makes marking case sensitive so Cat is not the same as cat */
        $mform->addElement('advcheckbox', 'casesensitive', get_string('casesensitive', 'qtype_guessit'));
         $mform->addHelpButton('casesensitive', 'casesensitive', 'qtype_guessit');
         $mform->setDefault('casesensitive', $config->casesensitive);

    }
    /**
     * Setup form elements that are very unlikely to change
     *
     * @param MoodleQuickForm $mform
     * @return MoodleQuickForm
     */
    protected function form_setup(MoodleQuickForm $mform): MoodleQuickForm {
        $mform->removeelement('questiontext');
        return $mform;
    }

    /**
     * Perform any preprocessing needed on the data passed in
     * before it is used to initialise the form.
     * @param object $question the data being passed to the form.
     * @return object $question the modified data.
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_combined_feedback($question);
        /* populates the hints and adds clearincorrect and and shownumcorrect (true,true) */
        $question = $this->data_preprocessing_hints($question, true, true);

        if (!empty($question->options)) {
            $question->answerdisplay = 'guessit';
        }
        return $question;
    }
    /**
     * Check the question text is valid, specifically that
     * it contains at lease one gap (text surrounded by delimiters
     * as in [cat]
     *
     * @param array $fromform
     * @param array $data
     * @return boolean
     */
    public function validation($fromform, $data) {
        $errors = [];
        /* don't save the form if there are no fields defined */
        $gaps = qtype_guessit::get_gaps('[]', $fromform['questiontext']['text']);
        if (count($gaps) == 0) {
            $errors['questiontext'] = get_string('questionsmissing', 'qtype_guessit');
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
