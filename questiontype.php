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
 * The question type class for the guessit question type.
 *
 * @package qtype_guessit
 * @subpackage guessit
 * @copyright  2025 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2018 Marcus Green <marcusavgreen@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');

/**
 *
 * The guessit question class
 *
 * Load from database, and initialise class
 * A "fill in the gaps" cloze style question type
 * @package    qtype_guessit
 * @copyright  2025 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2018 Marcus Green <marcusavgreen@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_guessit extends question_type {

    /**
     * Whether the quiz statistics report can analyse
     * all the student responses. See questiontypebase for more
     *
     * @return bool
     */
    public function can_analyse_responses() {
          return false;
    }

    /**
     * data used by export_to_xml (among other things possibly
     * @return array
     */
    public function extra_question_fields() {
        return ['question_guessit', 'guessitgaps', 'nbtriesbeforehelp', 'nbmaxtrieswordle', 'wordle'];
    }

    /**
     * Called during question editing
     *
     * @param stdClass $question
     */
    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('question_guessit', ['question' => $question->id], '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    /**
     * called when previewing or at runtime in a quiz
     *
     * @param question_definition $question
     * @param stdClass $questiondata
     * @param boolean $forceplaintextanswers
     */
    protected function initialise_question_answers(question_definition $question, $questiondata, $forceplaintextanswers = true) {
        $question->answers = [];
        if (empty($questiondata->options->answers)) {
            return;
        }

        foreach ($questiondata->options->answers as $a) {
            /* answer in this context means correct answers, i.e. where
             * fraction contains a 1 */
            if (strpos($a->fraction, '1') !== false) {
                $question->answers[$a->id] = new question_answer($a->id, $a->answer, $a->fraction,
                        $a->feedback, $a->feedbackformat);
                $question->gapcount++;
            }
        }
    }

    /**
     * Called when previewing a question or when displayed in a quiz
     *  (not from within the editing form)
     *
     * @param question_definition $question
     * @param stdClass $questiondata
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata, true);
        $question->places = [];
        $counter = 1;
        $question->maxgapsize = 0;
        foreach ($questiondata->options->answers as $choicedata) {
            /* fraction contains a 1 */
            if (strpos($choicedata->fraction, '1') !== false) {
                $question->places[$counter] = $choicedata->answer;
                $counter++;
            }
        }
    }

    /**
     * Saves the question with gap-based default marks.
     *
     * Calculates the number of gaps from the form data and sets the
     * default mark accordingly. Delegates the saving process to the
     * parent `save_question` method.
     *
     * @param stdClass $question The question data to save.
     * @param stdClass $form     The form data containing user input.
     *
     * @return mixed The result from the parent `save_question` method.
     */
    public function save_question($question, $form) {
        $gaps = $this->get_gaps($form->guessitgaps, $form->wordle);
        /* count the number of gaps
         * this is used to set the maximum
         * value for the whole question. Value for
         * each gap can be only 0 or 1
         */
        $form->defaultmark = count($gaps);
        return parent::save_question($question, $form);
    }

    /**
     * it really does need to be static
     *
     * @param string $guessitgaps
     * @param int $wordle
     * @return array
     */
    public static function get_gaps($guessitgaps, $wordle) {
        // Convert the string into an array.
        if ($wordle) {
            $gaps = str_split($guessitgaps);
        } else {
            $gaps = explode(' ', $guessitgaps);
        }
        return $gaps;
    }

    /**
     * Save the answers and options associated with this question.
     * @param stdClass $question
     * @return boolean to indicate success or failure.
     **/
    public function save_question_options($question) {
        /* Save the extra data to your database tables from the
          $question object, which has all the post data from editquestion.html */
        global $DB;
        $gaps = $this->get_gaps($question->guessitgaps, $question->wordle);
        $answerfields = $this->get_answer_fields($gaps, $question);
        $context = $question->context;
        // Fetch old answer ids so that we can reuse them.
        $this->update_question_answers($question, $answerfields);

        $options = $DB->get_record('question_guessit', ['question' => $question->id]);
        $this->update_question_guessit($question, $options, $context);

        return true;
    }


    /**
     * Writes to the database, runs from question editing form
     *
     * @param stdClass $question
     * @param stdClass $options
     * @param context_course_object $context
     */
    public function update_question_guessit($question, $options, $context) {
        global $DB;
        $options = $DB->get_record('question_guessit', ['question' => $question->id]);
        if (!$options) {
            $options = new stdClass();
            $options->question = $question->id;
            $options->guessitgaps = '';
            $options->nbtriesbeforehelp = '';
            $options->nbmaxtrieswordle = '';
            $options->wordle = '';
            $options->id = $DB->insert_record('question_guessit', $options);
        }

        $options->guessitgaps = $question->guessitgaps;
        $options->nbtriesbeforehelp = $question->nbtriesbeforehelp;
        $options->nbmaxtrieswordle = $question->nbmaxtrieswordle;
        $options->wordle = $question->wordle;

        $DB->update_record('question_guessit', $options);
    }

    /**
     * Write to the database during editing
     *
     * @param stdClass $question
     * @param array $answerfields
     */
    public function update_question_answers($question, array $answerfields) {
        global $DB;
        $oldanswers = $DB->get_records('question_answers', ['question' => $question->id], 'id ASC');
        // Insert all the new answers.
        foreach ($answerfields as $field) {
            // Save the true answer - update an existing answer if possible.
            if ($answer = array_shift($oldanswers)) {
                $answer->question = $question->id;
                $answer->answer = $field['value'];
                $answer->feedback = '';
                $answer->fraction = $field['fraction'];
                $DB->update_record('question_answers', $answer);
            } else {
                // Insert a blank record.
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = $field['value'];
                $answer->feedback = '';
                $answer->fraction = $field['fraction'];
                $answer->id = $DB->insert_record('question_answers', $answer);
            }
        }
        // Delete old answer records.
        foreach ($oldanswers as $oa) {
            $DB->delete_records('question_answers', ['id' => $oa->id]);
        }
    }

    /**
     * Set up all the answer fields with respective fraction (mark values)
     * This is used to update the question_answers table.
     *
     * @param array $answerwords
     * @param stdClass $question
     * @return  array
     */
    public function get_answer_fields(array $answerwords, $question) {
        /* This code runs both on saving from a form and from importing. */
        if (!property_exists($question, 'answer')) {
            foreach ($answerwords as $key => $value) {
                $answerfields[$key]['value'] = $value;
                $answerfields[$key]['fraction'] = 1;
            }
        }
        return $answerfields;
    }

    /**
     * The name of the key column in the foreign table (might have been questionid instead)
     * @return string
     */
    public function questionid_column_name() {
        return 'question';
    }

    /**
     * Create a question from reading in a file in Moodle xml format
     *
     * @param array $data
     * @param stdClass $question (might be an array)
     * @param qformat_xml $format
     * @param stdClass $extra
     * @return boolean
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'guessit') {
            return false;
        }
        /* There are no answers to import as they will be constructed later on */
        $data['#']['answer'] = [];
        $question = parent::import_from_xml($data, $question, $format, null);
        $question->isimport = true;
        return $question;
    }

    /**
     * Export question to the Moodle XML format
     *
     * @param object $question
     * @param qformat_xml $format
     * @param object $extra
     * @return string
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        global $CFG;
        /* No need to export the answers as they will be constructed upon import */
        $question->options->answers = [];
        $pluginmanager = core_plugin_manager::instance();
        $guessitinfo = $pluginmanager->get_plugin_info('qtype_guessit');
        $output = parent::export_to_xml($question, $format);
        $output .= '    <!-- guessit release:'
                . $guessitinfo->release . ' version:' . $guessitinfo->versiondisk . ' Moodle version:'
                . $CFG->version . ' release:' . $CFG->release
                . " -->\n";
        return $output;
    }

}
