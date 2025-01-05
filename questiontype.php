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
 * @copyright  2024 Joseph Rézeau <moodle@rezeau.org>
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
 * @copyright  2018 Marcus Green
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
        return ['question_guessit', 'casesensitive', 'gapsizedisplay', 'nbtriesbeforehelp', 'removespecificfeedback', 'wordle'];
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
        // Remove html comments as they can contain delimiters, e.g. <!--[if !supportLists] .
        $question->questiontext = preg_replace('/<!--(.|\s)*?-->/', '', $question->questiontext);

        foreach ($questiondata->options->answers as $a) {
            if (strstr($a->fraction, '1') == false) {
                /* if this is a wronganswer/distractor strip any
                 * backslashes, this allows escaped backslashes to
                 * be used i.e. \, and not displayed in the draggable
                 * area
                 */
                $a->answer = stripslashes($a->answer);
            }
            /* answer in this context means correct answers, i.e. where
             * fraction contains a 1 */
            if (strpos($a->fraction, '1') !== false) {
                $question->answers[$a->id] = new question_answer($a->id, $a->answer, $a->fraction,
                        $a->feedback, $a->feedbackformat);
                $question->gapcount++;
                if (!$forceplaintextanswers) {
                    $question->answers[$a->id]->answerformat = $a->answerformat;
                }
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
            /* find the width of the biggest gap */
            $len = $question->get_size($choicedata->answer);
            if ($len > $question->maxgapsize) {
                $question->maxgapsize = $len;
            }

            /* fraction contains a 1 */
            if (strpos($choicedata->fraction, '1') !== false) {
                $question->places[$counter] = $choicedata->answer;
                $counter++;
            }
        }

        /* Will put empty places '' where there is no text content.
         * l for left delimiter r for right delimiter
         */
        $l = substr('[]', 0, 1);
        $r = substr('[]', 1, 1);

        $nongapregex = '/\\' . $l . '.*?\\' . $r . '/';
        $nongaptext = preg_split($nongapregex, $question->questiontext, -1, PREG_SPLIT_DELIM_CAPTURE);
        $i = 0;
        while (!empty($nongaptext)) {
            $question->textfragments[$i] = array_shift($nongaptext);
            $i++;
        }
    }

    /**
     * Sets the default mark as 1* the number of gaps
     * Does not allow setting any other value per space/field at the moment
     * @param stdClass $question
     * @param \stdClass $form
     * @return object
     */
    public function save_question($question, $form) {
        $gaps = $this->get_gaps('[]', $form->questiontext['text']);
        /* count the number of gaps
         * this is used to set the maximum
         * value for the whole question. Value for
         * each gap can be only 0 or 1
         */
        $form->defaultmark = count($gaps);
        return parent::save_question($question, $form);
    }

    /**
     * chop the delimit string into a two element array
     * this might be better done on initialisation
     *
     * @param string $delimitchars
     * @return array
     */
    public static function get_delimit_array($delimitchars) {
        $delimitarray = [];
        $delimitarray["l"] = substr($delimitchars, 0, 1);
        $delimitarray["r"] = substr($delimitchars, 1, 1);
        return $delimitarray;
    }

    /**
     * it really does need to be static
     *
     * @param string $delimitchars
     * @param string $questiontext
     * @return array
     */
    public static function get_gaps($delimitchars, $questiontext) {
        /* l for left delimiter r for right delimiter
         * defaults to []
         * e.g. l=[ and r=] where question is
         * The [cat] sat on the [mat]
         */
        $delim = self::get_delimit_array($delimitchars);
        $fieldregex = '/.*?\\' . $delim["l"] . '(.*?)\\' . $delim["r"] . '/';
        $matches = [];
        preg_match_all($fieldregex, $questiontext, $matches);
        return $matches[1];
    }

    /**
     * Save the answers and optionsassociated with this question.
     * @param stdClass $question
     * @return boolean to indicate success or failure.
     **/
    public function save_question_options($question) {
        /* Save the extra data to your database tables from the
          $question object, which has all the post data from editquestion.html */

        // Remove html comments as they can contain delimiters, e.g. <!--[if !supportLists] .
        $question->questiontext = preg_replace('/<!--(.|\s)*?-->/', '', $question->questiontext);

        $gaps = $this->get_gaps('[]', $question->questiontext);
        /* answerwords are the text within gaps */
        $answerfields = $this->get_answer_fields($gaps, $question);
        global $DB;

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
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->casesensitive = '';
            $options->gapsizedisplay = '';
            $options->nbtriesbeforehelp = '';
            $options->removespecificfeedback = '';
            $options->wordle = '';
            $options->id = $DB->insert_record('question_guessit', $options);
        }

        $options->casesensitive = $question->casesensitive;
        $options->gapsizedisplay = $question->gapsizedisplay;
        $options->nbtriesbeforehelp = $question->nbtriesbeforehelp;
        $options->removespecificfeedback = $question->removespecificfeedback;
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
                $answer->correctfeedback = '';
                $answer->partiallycorrectfeedback = '';
                $answer->incorrectfeedback = '';
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
     * This is used to update the question_answers table. Answerwords has
     * been pulled from within the delimitchars e.g. the cat within [cat]
     * Wronganswers (distractors) has been pulled from a comma delimited edit
     * form field
     *
     * @param array $answerwords
     * @param stdClass $question
     * @return  array
     */
    public function get_answer_fields(array $answerwords, $question) {
        /* this code runs both on saving from a form and from importing and needs
         * improving as it mixes pulling information from the question object which
         * comes from the import and from $question->wronganswers field which
         * comes from the question_editing form.
         */
        $answerfields = [];
        /* this next block runs when importing from xml */
        if (property_exists($question, 'answer')) {
            foreach ($question->answer as $key => $value) {
                if ($question->fraction[$key] == 0) {
                    $answerfields[$key]['value'] = $question->answer[$key];
                    $answerfields[$key]['fraction'] = 0;
                } else {
                    $answerfields[$key]['value'] = $question->answer[$key];
                    $answerfields[$key]['fraction'] = 1;
                }
            }
        }

        /* the rest of this function runs when saving from edit form */
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
        $pluginmanager = core_plugin_manager::instance();
        $guessitinfo = $pluginmanager->get_plugin_info('qtype_guessit');
        /*convert json into an object */

        $output = parent::export_to_xml($question, $format);
        $output .= '    <casesensitive>' . $question->options->casesensitive .
                "</casesensitive>\n";
        $output .= '    <gapsizedisplay>' . $question->options->gapsizedisplay .
                "</gapsizedisplay>\n";
        $output .= '    <nbtriesbeforehelp>' . $question->options->nbtriesbeforehelp .
                "</nbtriesbeforehelp>\n";
        $output .= '    <removespecificfeedback>' . $question->options->removespecificfeedback .
                "</removespecificfeedback>\n";
        $output .= '    <wordle>' . $question->options->wordle .
                "</wordle>\n";
        $output .= '    <!-- guessit release:'
                . $guessitinfo->release . ' version:' . $guessitinfo->versiondisk . ' Moodle version:'
                . $CFG->version . ' release:' . $CFG->release
                . " -->\n";
        return $output;
    }

}
