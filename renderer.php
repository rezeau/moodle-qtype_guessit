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
 * Generates the output for guessit questions
 *
 * @package qtype_guessit
 * @subpackage guessit
 * @copyright  2024 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2019 Marcus Green <marcusavgreen@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_guessit_renderer extends qtype_renderer {

    /**
     * responses that would be correct if submitted
     * @var array
     */

    public $correctresponses = [];

    /**
     * all the options that controls how a question is displayed
     * more about the question engine than this specific question type
     *
     * @var all the options that controls how a question is displayed
     */
    public $displayoptions;

    /**
     * Generate the display of the formulation part of the question shown at runtime
     * in a quiz.  This is the area that contains the question text with gaps.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $this->page->requires->js_call_amd('qtype_guessit/gapsnavigation', 'init');
        $this->displayoptions = $options;
        $question = $qa->get_question();
        $output = "";
        $answeroptions = '';
        $questiontext = '';
        // Check that all gaps have been filled in.
        $complete = $this->check_complete_answer($qa);
        $markedgaps = $question->get_markedgaps($qa, $options);
        foreach ($question->textfragments as $place => $fragment) {
            if ($place > 0) {
                $questiontext .= '<div class="input-wrapper">';
                $questiontext .= $this->embedded_element($qa, $place, $options, $markedgaps);
                $questiontext .= '</div>';
            }
            // Format the non entry field parts of the question text.
            // This will also ensure images get displayed.
            $questiontext .= $question->format_text($fragment, $question->questiontextformat,
                $qa, 'question', 'questiontext', $question->id);

        }
            // For guessit rendering.
            $output .= $questiontext;

        if ($qa->get_state() == question_state::$invalid || !$complete) {
            $output .= html_writer::nonempty_tag('div', $question->get_validation_error(['answer' => $output]),
             ['class' => 'validationerror']);
        }
        $output = html_writer::tag('div', $output, ['class' => 'qtext']);
        return $output;
    }

    /**
     * Construct the gaps, e.g. textentry and set the state accordingly
     *
     * @param question_attempt $qa
     * @param number $place
     * @param question_display_options $options
     * @param array  $markedgaps
     * @return string
     */
    public function embedded_element(question_attempt $qa, $place, question_display_options $options, $markedgaps) {
        /* fraction is the mark associated with this field, always 1 or 0 for this question type */
        /** @var \qtype_guessit_question $question */
        $question = $qa->get_question();
        $casesensitive = $question->casesensitive;
        $fieldname = $question->field($place);
        $studentanswer = $qa->get_last_qt_var($fieldname) ?? '';
        $studentanswer = htmlspecialchars_decode($studentanswer);
        $rightanswer = $question->get_right_choice_for($place);
        if (!$question->casesensitive == 1) {
            $studentanswer = core_text::strtolower($studentanswer, 'UTF-8');
            $rightanswer = core_text::strtolower($rightanswer, 'UTF-8');
        }
        $size = 0;
        if ($question->gapsizedisplay === 'gapsizematchword') {
            $size = $question->get_size($rightanswer);
        } else if ($question->gapsizedisplay === 'gapsizefixed') {
            $size = $question->maxgapsize;
        } else if ($question->gapsizedisplay === 'gapsizegrow') {
            $size = 6;
        }
        /* $options->correctness is really about it being ready to mark, */
        $inputclass = "";
        if ((($options->correctness) || ($options->numpartscorrect)) && isset($markedgaps['p' . $place])) {
            $gap = $markedgaps['p' . $place];
            $fraction = $gap['fraction'];
            $response = $qa->get_last_qt_data();
            if (empty($studentanswer)) {
                $inputclass = '';
            } else if ($fraction == 1) {
                array_push($this->correctresponses, $response[$fieldname]);
                if (($response[$fieldname] != '')) {
                    $inputclass = $this->get_input_class($markedgaps, $qa, $fraction, $fieldname);
                }
            } else if ($fraction == 0) {
                if (preg_match('/^' . preg_quote($studentanswer[0], '/') . '/i', $rightanswer)) {
                    $inputclass = 'partiallycorrect';
                } else {
                    $inputclass = $this->feedback_class($fraction);
                }
            }
        }

        $qprefix = $qa->get_qt_field_name('');
        $inputname = $qprefix . 'p' . $place;

        $inputattributes = [
            'type' => "text",
            'name' => $inputname,
            'value' => $studentanswer,
            'id' => $inputname,
            'size' => $size,
        ];
        /* When previewing after a quiz is complete */
        // Papijo use this readonly state to not display feedback ???
        if ($options->readonly) {
            $readonly = ['disabled' => 'true'];
            $inputattributes = array_merge($inputattributes, $readonly);
        }
        // Only use autogrowinput if gapsizedisplay is set to gapsizegrow.
        $autogrowinput = '';
        if ($question->gapsizedisplay === 'gapsizegrow') {
            $autogrowinput = ' auto-grow-input ';
        }
        $inputattributes['class'] = 'typetext guessit '. $autogrowinput. $inputclass;
        if ($fraction == 1) {
            $size = $question->get_size($rightanswer);
            $inputattributes['size'] = $size;
        }
        $inputattributes['spellcheck'] = 'false';
        $markupcode = "";
        if ($studentanswer !== $rightanswer) {
            $markupcode = $this->get_markup_string ($studentanswer, $rightanswer);
        }
        return html_writer::empty_tag('input', $inputattributes) . '<span class="markup">'.$markupcode.'</span>';
    }

    /**
     * Get feedback for correct or incorrect response
     *
     * @param array|null $settings
     * @param bool   $correctness
     * @return string
     */
    protected function get_feedback($settings, bool $correctness): string {
        if ($settings == null) {
            return "";
        }
        if (!$this->displayoptions->correctness) {
            return "";
        }
        $stripexcptions = "<hr><a><b><i><u><strike><font>";
        if ($correctness) {
            return strip_tags($settings->correctfeedback, $stripexcptions);
        } else {
            return strip_tags($settings->incorrectfeedback, $stripexcptions);
        }
    }

    /**
     * Does what it sayx
     * @param array $markedgaps
     * @param question_attempt $qa
     * @param number $fraction either 0 or 1 for correct or incorrect
     * @param string $fieldname p1, p2, p3 etc
     * @return string
     */
    public function get_input_class(array $markedgaps, question_attempt $qa, $fraction, $fieldname) {
        $inputclass = $this->feedback_class($fraction);
        return $inputclass;
    }

    /**
     * Get feedback/hint information
     *
     * @param question_attempt $qa
     * @return string
     */
    public function specific_feedback(question_attempt $qa) {
        // Check that all gaps have been filled in.
        $complete = $this->check_complete_answer($qa);
        if (!$complete) {
            return get_string('pleaseenterananswer', 'qtype_guessit');;
        }
        $question = $qa->get_question();
        $casesensitive = $question->casesensitive;
        // Get $rightanswer.
        $rightanswer = '';
        foreach ($question->answers as $answer) {
            $rightanswer .= $answer->answer . ',';
        }
        $rightanswer = rtrim($rightanswer, ',');

        // Get $studentanswer.
        $studentanswer = '';
        $i = 1;
        foreach ($qa->get_reverse_step_iterator() as $step) {
            // If help button has been clicked, do not add current response to list.
            if (!$step->has_behaviour_var('helpme')) {
                $response = $step->get_qt_data();
                if (!empty($response)) {
                    $studentanswer .= implode(',', $response).',';
                }
                $i++;
            }
        }
        $studentanswer = rtrim($studentanswer, ',');
        if (!$question->casesensitive == 1) {
            $studentanswer = core_text::strtolower($studentanswer, 'UTF-8');
            $rightanswer = core_text::strtolower($rightanswer, 'UTF-8');
        }
        $prevtries = $qa->get_last_behaviour_var('_try', 0);
        return $this->format_specific_feedback ($prevtries, $rightanswer, $studentanswer);
    }

    /**
     * overriding base class method purely to return a string
     * yougotnrightcount instead of default yougotnright
     *
     * @param question_attempt $qa
     * @return string
     */
    protected function num_parts_correct(question_attempt $qa) {
        $complete = $this->check_complete_answer($qa);
        $a = new stdClass();
        list($a->num, $a->outof) = $qa->get_question()->get_num_parts_right(
            $qa->get_last_qt_data()
        );
        if (is_null($a->outof)) {
            return '';
        } else {
            if ($a->num > 1) {
                $a->gaporgaps = get_string('gap_plural', 'qtype_guessit');
            } else {
                $a->gaporgaps = get_string('gap_singular', 'qtype_guessit');
            }
            return get_string('yougotnrightcount', 'qtype_guessit', $a);
        }
    }

    /**
     * Construct the markup string
     *
     * @param string $studentanswer
     * @param string $answer
     * @return string
     *
     */
    public function get_markup_string($studentanswer, $answer) {
        $cleananswer = $answer;
        // Check if answer has only ASCII characters.
        $hasonlyascii = preg_match('/^[\x00-\x7F]*$/', $answer);
        if (!$hasonlyascii) {
            $cleananswer = $this->removeDiacritics($answer);
        }

        // Check if student answer has only ASCII characters.
        $cleanstudentanswer = $studentanswer;
        $hasonlyascii = preg_match('/^[\x00-\x7F]*$/', $studentanswer);
        if (!$hasonlyascii) {
            $cleanstudentanswer = $this->removeDiacritics($studentanswer);
        }

        // Initialize variables.
        $markup = '';
        $eq = '=';
        $lw = '<';
        $gt = '>';
        $i = 0;
        // List of punctuation or special characters to "give" to the user.
        $punctuation = "';:,.-?¿!¡ßœ";

        // Get the minimum length of answer and student answer.
        $minlen = min(strlen($answer), strlen($studentanswer));
        // Loop through each character up to the minimum length.
        for ($i = 0; $i < $minlen; $i++) {
            // This is needed for non-ascii characters.
            $answerletter = mb_substr($answer, $i, 1, 'UTF-8'); // Extract 1 character at index $i.
            if (!empty($cleananswer) && is_string($cleananswer) && $i < mb_strlen($cleananswer)) {
                $cleananswerletter = mb_strtolower(mb_substr($cleananswer, $i, 1));
            } else {
                $cleananswerletter = ''; // Default or fallback value.
            }
            $studentletter = mb_substr($studentanswer, $i, 1, 'UTF-8'); // Extract 1 character at index $i.
            if (!empty($cleanstudentanswer) && is_string($cleanstudentanswer) && $i < mb_strlen($cleanstudentanswer)) {
                $cleanstudentletter = mb_strtolower(mb_substr($cleanstudentanswer, $i, 1));
            } else {
                $cleanstudentletter = ''; // Default or fallback value.
            }
            // Logic to generate the markup.
            if ($studentletter === $answerletter) {
                $markup .= $eq; // Exact match.
            } else if ($cleanstudentletter === $cleananswerletter) {
                $markup .= $answerletter;
                break;
            } else if ($cleanstudentletter === $cleananswerletter || strpos($punctuation, $cleananswer[$i]) !== false) {
                $markup .= $answerletter;
                break;
            } else if ($cleanstudentletter < $cleananswerletter) {
                $markup .= $gt; // Student letter is "less than" the answer letter.
                break;
            } else {
                $markup .= $lw; // Student letter is "greater than" the answer letter.
                break;
            }
        }
          // Return the generated markup for debugging or further use.
        return $markup;
    }

    /**
     * Removes diacritics from a string.
     * @param string $text
     * @return string $text
     */
    public function removediacritics($text) {
        // IMPORTANT: this js file must be encoded in UTF-8 no BOM(65001)
        // If it's not, then use the unicode codes at
        // https://web.archive.org/web/20120918093154/http://lehelk.com/2011/05/06/script-to-remove-diacritics/ .
        $defaultdiacriticsremovalmap = [
            ['base' => 'a', 'letters' => '/[àáâãäåæ]/u'],
            ['base' => 'c', 'letters' => '/[ç]/u'],
            ['base' => 'e', 'letters' => '/[éèêë]/u'],
            ['base' => 'i', 'letters' => '/[ìíîï]/u'],
            ['base' => 'n', 'letters' => '/[ñ]/u'],
            ['base' => 'o', 'letters' => '/[òóôõöø]/u'],
            ['base' => 'u', 'letters' => '/[ùúûü]/u'],
            ['base' => 'y', 'letters' => '/[ýÿ]/u'],
        ];

        foreach ($defaultdiacriticsremovalmap as $change) {
            $text = preg_replace($change['letters'], $change['base'], $text);
        }
        return $text;
    }

    /**
     * Format rightanswer and studentanswer nicely for specific feedback disply.
     * @param number $prevtries
     * @param string $rightanswer
     * @param string $studentanswer
     * @return string $formattedfeedback
     */
    private function format_specific_feedback($prevtries, $rightanswer, $studentanswer) {
        $arrayrightanswer = explode(',', $rightanswer);
        $arraystudentanswer = explode(',', $studentanswer);
        $lengthrightanswer = count($arrayrightanswer);
        $studentanswers = array_chunk($arraystudentanswer, $lengthrightanswer);
        $triescounter = 0;
        foreach ($studentanswers as $outerindex => $subarray) {
            $formattedfeedback .= '<b>' . ($prevtries - $triescounter) . '</b>&nbsp;';
            // Loop through the inner array.
            foreach ($subarray as $innerindex => $value) {
                $colorclass = '';
                $studentanswer = $value;
                $rightanswer = $arrayrightanswer[$innerindex];
                $markupcode = $this->get_markup_string ($studentanswer, $rightanswer);
                if ($studentanswer) {
                    if ($studentanswer === $rightanswer) {
                        $colorclass = 'correct';
                        $markupcode = '';
                    } else if (preg_match('/^' . preg_quote($studentanswer[0], '/') . '/i', $rightanswer)) {
                        $colorclass = 'partiallycorrect';
                    } else {
                        $colorclass = 'incorrect';
                    }
                } else {
                    $studentanswer = '&nbsp;';
                }
                $formattedfeedback .= '<div class="specific-feedback input-wrapper '.$colorclass.'">'.
                    $studentanswer. '<span class="feedback-markup">'.$markupcode. '</span></div>';
            }
            $triescounter ++;
            $formattedfeedback .= '<hr/>';
        }
        return $formattedfeedback;
    }

    /**
     * Determines if all gaps in the answer have been filled.
     *
     * @param question_attempt $qa The question attempt object.
     * @return bool True if all gaps are filled, false otherwise.
     */
    protected function check_complete_answer(question_attempt $qa) {
        // Check that all gaps have been filled in.
        $currentresponses = $qa->get_last_qt_data();
        $notcomplete = false;
        foreach ($currentresponses as $currentresponse) {
            if ($currentresponse === '') {
                return false;
            }
        }
        return true;
    }

}
