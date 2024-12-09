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
 * @package    qtype_guessit
 * @copyright  2019 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generates the output for guessit questions
 *
 * @copyright  2019 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_guessit_renderer extends qtype_with_combined_feedback_renderer {

    /**
     * responses that would be correct if submitted
     * @var array
     */
    public $correctresponses = array();
    /**
     * correct and distractor answers
     *
     * @var array
     */
    public $allanswers = array();
    /**
     * Used to store the per-gap settings, e.g. feedback
     * @var array
     */
    public $itemsettings = [];
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
        
        //print_r($qa);
        
        $currentanswer = $qa->get_last_qt_var($fieldname) ?? '';
        $currentanswer = htmlspecialchars_decode($currentanswer);
        
        $this->displayoptions = $options;
        $question = $qa->get_question();
        
        $seranswers = $qa->get_step(0)->get_qt_var('_allanswers');
        $this->allanswers = unserialize($seranswers);
        $output = "";
        $answeroptions = '';        
        $questiontext = '';
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

        if ($qa->get_state() == question_state::$invalid) {
            $output .= html_writer::nonempty_tag('div', $question->get_validation_error(array('answer' => $output)),
             ['class' => 'validationerror']);
        }
        $output = html_writer::tag('div', $output, ['class' => 'qtext']);
        return $output;
    }

    /**
     * Set divs that are inspected by the mobile app
     * for settings
     *
     * @param qtype_guessit_question $question
     * @param  string $questiontext
     * @return string
     */
    public function app_connect(qtype_guessit_question $question, string $questiontext) : string {
        return $questiontext;
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
        $fieldname = $question->field($place);

        $currentanswer = $qa->get_last_qt_var($fieldname) ?? '';
        $currentanswer = htmlspecialchars_decode($currentanswer);
        $rightanswer = $question->get_right_choice_for($place);
        $itemsettings = $this->get_itemsettings($rightanswer);
        if ($question->fixedgapsize == 1) {
            /* set all gaps to the size of the  biggest gap
             */
            $size = $question->maxgapsize;
        } else {
            /* otherwise set the size of an individual gap which might
             * be less than the string width if it is in the form
             * "[cat|dog|elephant] the width should be 8 and not 14
             */
            $size = $question->get_size($rightanswer);
        }

        /* $options->correctness is really about it being ready to mark, */
        $aftergaptext = "";
        $inputclass = "";
        if ((($options->correctness) || ($options->numpartscorrect)) && isset($markedgaps['p' . $place])) {
            $gap = $markedgaps['p' . $place];
            $fraction = $gap['fraction'];
            $response = $qa->get_last_qt_data();

            /* fraction is always either 1 or 0 for correct or incorrect response */
            if ($fraction == 1) {
                array_push($this->correctresponses, $response[$fieldname]);
                /* if the gap contains !! or  the response is (a correct) non blank */
                if (!preg_match($question->blankregex, $rightanswer) || ($response[$fieldname] != '')) {
                    $aftergaptext = $this->get_aftergap_text($qa, $fraction, $itemsettings);
                    /* sets the field background to green or yellow if fraction is 1 */
                    $inputclass = $this->get_input_class($markedgaps, $qa, $fraction, $fieldname);
                }
            } else if ($fraction == 0) {
                $aftergaptext = $this->get_aftergap_text($qa, $fraction, $itemsettings, $rightanswer);
                $inputclass = $this->feedback_class($fraction);
            }
        }

        $qprefix = $qa->get_qt_field_name('');
        $inputname = $qprefix . 'p' . $place;

        $inputattributes = array(
            'type' => "text",
            'name' => $inputname,
            'value' => $currentanswer,
            'id' => $inputname,
            'size' => $size
        );
        /* When previewing after a quiz is complete */
        if ($options->readonly) {
            $readonly = array('disabled' => 'true');
            $inputattributes = array_merge($inputattributes, $readonly);
    }
        /* it is a typetext (guessit) question */
        $inputattributes['class'] = 'typetext guessit ' . $inputclass;
        $inputattributes['spellcheck'] = 'false';        
        $markupcode = "";
        if ($currentanswer !== $rightanswer) {
            $markupcode = $this->get_markup_string ($currentanswer, $rightanswer);
        } 
        // TODO calculate here the contents of the $markupcode string.
        
        return html_writer::empty_tag('input', $inputattributes) . '<span class="markup">'.$markupcode.'</span>';
    }

    /**
     * What appears after a gap once it is marked, e.g. a tick a cross or feedback
     * on the answer
     *
     * @param question_attempt $qa
     * @param number $fraction
     * @param array $itemsettings
     * @param string $rightanswer
     * @return string
     */
    public function get_aftergap_text(question_attempt $qa, $fraction, $itemsettings, $rightanswer = "") {
        /* If the display options are set to not display the right answer
        then don't display the aftergap text either */
        if (!$this->displayoptions->rightanswer) {
            //return false;
        }
        //$aftergaptext = '<span class="h5p-guessit-markup">====></span></div>';
        //return $aftergaptext;
        if (($fraction == 0) && ($rightanswer != "") && ($rightanswer != ".+")) {
            /* replace | operator with the word or */
            $rightanswerdisplay = preg_replace("/\|/", " ".get_string("or", "qtype_guessit")." ", $rightanswer);
            /* replace !! with the 'blank' */
            $rightanswerdisplay = preg_replace("/\!!/", get_string("blank", "qtype_guessit"), $rightanswerdisplay);
            $question = $qa->get_question();
            $delim = qtype_guessit::get_delimit_array($question->delimitchars);
            /* set background to red and image to cross if fraction is 0 (an incorrect response
             * was given */
            $aftergaptext = $this->feedback_image($fraction);
            $aftergaptext .= "<span class='aftergapfeedback' title='" .
            get_string("correctanswer", "qtype_guessit") . "'>" . $delim["l"] .
                $rightanswerdisplay . $delim["r"] . "</span>";
            $aftergaptext .= " <span class='gapfeedbackincorrect' title='feedback' >"
            . $this->get_feedback($itemsettings, false) . "</span>";
        } else {
            $aftergaptext = $this->feedback_image($fraction);
            $aftergaptext .= " <span class='gapfeedbackcorrect' title='feedback' >" .
            $this->get_feedback($itemsettings, true) . "</span>";
        }
        return $aftergaptext;
    }
    /**
     * Get feedback for correct or incorrect response
     *
     * @param array|null $settings
     * @param bool   $correctness
     * @return string
     */
    protected function get_feedback($settings, bool $correctness) :string {
        if ($settings == null) {
            return "";
        }
        if (!$this->displayoptions->correctness) {
            return "";
        }
        /*The atto editor tends to inject various tags that will not look good
         * in feedback (e.g. <p> or <br/> so this strips all but the strip exceptions out)
         */
        $stripexcptions = "<hr><a><b><i><u><strike><font>";
        if ($correctness) {
            return strip_tags($settings->correctfeedback, $stripexcptions);
        } else {
            return strip_tags($settings->incorrectfeedback, $stripexcptions);
        }
    }
    /**
     * Get the item settings for this gap based on the gap text
     * If you have duplicate gaps it will not distinguish between them
     *
     * @param string $rightanswer
     * @return array
     */
    protected function get_itemsettings(string $rightanswer) {
        foreach ($this->itemsettings as $set) {
            if ($set->gaptext == $rightanswer) {
                return $set;
            }
        }
    }

    /**
     * set the feedback class to green unless noduplicates is set
     * then check if this is a duplicated value and if it is set the background
     * to yellow.
     *
     * @param array $markedgaps
     * @param question_attempt $qa
     * @param number $fraction either 0 or 1 for correct or incorrect
     * @param string $fieldname p1, p2, p3 etc
     * @return string
     */
    public function get_input_class(array $markedgaps, question_attempt $qa, $fraction, $fieldname) {
        $response = $qa->get_last_qt_data();
        $question = $qa->get_question();

        $inputclass = $this->feedback_class($fraction);
        foreach ($markedgaps as $gap) {
            if ($response[$fieldname] == $gap['value']) {
                if ($gap['duplicate'] == 'true') {
                    if ($question->noduplicates == 1) {
                        $inputclass = ' correctduplicate';
                    }
                }
            }
        }
        return $inputclass;
    }

    /**
     * Get feedback/hint information
     *
     * @param question_attempt $qa
     * @return string
     */
    public function specific_feedback(question_attempt $qa) {
        return "too much cooks<br>===";
        return $this->combined_feedback($qa) . $this->get_duplicate_feedback($qa);
    }

    /**
     * if noduplicates is set check if any responses
     * are duplicate values
     *
     * @param question_attempt $qa
     * @return string
     *
     */
    public function get_duplicate_feedback(question_attempt $qa) {
        $question = $qa->get_question();
        if ($question->noduplicates == 0) {
            return;
        }
        $arrunique = array_unique($this->correctresponses);
        if (count($arrunique) != count($this->correctresponses)) {
            return get_string('duplicatepartialcredit', 'qtype_guessit');
        }
    }

    /**
     * overriding base class method purely to return a string
     * yougotnrightcount instead of default yougotnright
     *
     * @param question_attempt $qa
     * @return string
     */
    protected function num_parts_correct(question_attempt $qa) {
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
     * construct the markup string
     *
     * @param question_attempt $qa
     * @return string
     *
     */
    public function get_markup_string ($studentAnswer, $answer) {
        $cleanAnswer = $answer;
         // Check if answer has only ASCII characters
        $hasOnlyAscii = preg_match('/^[\x00-\x7F]*$/', $answer);
        if (!$hasOnlyAscii) {
            $cleanAnswer = $this->removeDiacritics($answer);
        }

        // Check if student answer has only ASCII characters
        $cleanStudentAnswer = $studentAnswer;
        $hasOnlyAscii = preg_match('/^[\x00-\x7F]*$/', $studentAnswer);
        if (!$hasOnlyAscii) {
            $cleanStudentAnswer = $this->removeDiacritics($studentAnswer);
        }

        // Initialize variables
        $markup = ''; 
        $eq = '='; 
        $lw = '<'; 
        $gt = '>'; 
        $i = 0;
        // 1️⃣ List of punctuation or special characters to "give" to the user
        $punctuation = "';:,.-?¿!¡ßœ";

        // 2️⃣ Get the minimum length of answer and student answer
        $minLen = min(strlen($answer), strlen($studentAnswer));

        // 3️⃣ Initialize markup string
        $markup = ''; 
        $eq = '='; 
        $lw = '<'; 
        $gt = '>'; 

        // 4️⃣ Loop through each character up to the minimum length
        for ($i = 0; $i < $minLen; $i++) {
            // This is needed for non-ascii characters.
            $answerLetter = mb_substr($answer, $i, 1, 'UTF-8'); // Extract 1 character at index $i
            $cleanAnswerLetter = mb_strtolower($cleanAnswer[$i]); // Lowercase for multibyte support
            $studentLetter = mb_substr($studentAnswer, $i, 1, 'UTF-8'); // Extract 1 character at index $i
            $cleanStudentLetter = mb_strtolower($cleanStudentAnswer[$i]); // Lowercase for multibyte support

            // 5️⃣ Logic to generate the markup
            if ($studentLetter === $answerLetter) {
                $markup .= $eq; // Exact match
            } 
            elseif ($cleanStudentLetter === $cleanAnswerLetter) {
                $markup .= $answerLetter;
                break;
            } 
            elseif ($cleanStudentLetter === $cleanAnswerLetter || strpos($punctuation, $cleanAnswer[$i]) !== false) {
                $markup .= $answerLetter;
                break;
            } 
            elseif ($cleanStudentLetter < $cleanAnswerLetter) {
                $markup .= $gt; // Student letter is "less than" the answer letter
                break;
            } 
            else {
                $markup .= $lw; // Student letter is "greater than" the answer letter
                break;
            }
        }
          // 6️⃣ Return the generated markup for debugging or further use
        return $markup;
  }

  /**
     * Removes diacritics from a string.
     */
    public function removeDiacritics($text) {
        // IMPORTANT: this js file must be encoded in UTF-8 no BOM(65001)
        // If it's not, then use the unicode codes at https://web.archive.org/web/20120918093154/http://lehelk.com/2011/05/06/script-to-remove-diacritics/
        $defaultDiacriticsRemovalMap = [
            ['base' => 'a', 'letters' => '/[àáâãäåæ]/u'],
            ['base' => 'c', 'letters' => '/[ç]/u'],
            ['base' => 'e', 'letters' => '/[éèêë]/u'],
            ['base' => 'i', 'letters' => '/[ìíîï]/u'],
            ['base' => 'n', 'letters' => '/[ñ]/u'],
            ['base' => 'o', 'letters' => '/[òóôõöø]/u'],
            ['base' => 'u', 'letters' => '/[ùúûü]/u'],
            ['base' => 'y', 'letters' => '/[ýÿ]/u'],
        ];

        foreach ($defaultDiacriticsRemovalMap as $change) {
            $text = preg_replace($change['letters'], $change['base'], $text);
        }
        return $text;
    }
}