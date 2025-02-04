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
 * @copyright  2025 Joseph Rézeau <moodle@rezeau.org>
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
     * Used in wordle option: stores the number of nbmisplacedletters
     * @var int
     */
    public $nbmisplacedletters = 0;

    /**
     * Generate the display of the formulation part of the question shown at runtime
     * in a quiz.  This is the area that contains the question text with gaps.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $question = $qa->get_question();
        $questiontext = $question->questiontext;
        $answers = $question->answers;
        $nbanswers = count($answers);
        $wordle = $question->wordle;
        $trieslefttxt = '';
        $letterstates = '';
        foreach ($question->answers as $answer) {
            $rightanswer = $answer->answer;
            array_push($this->correctresponses, $rightanswer);
        }
        if ($wordle) {
            $nbmaxtrieswordle = $question->nbmaxtrieswordle;
            // Display nb tries left when starting a new wordle.
            $studentresponse = $qa->get_last_qt_data();
            // This ksort is needed for my online site; maybe because of different PHP version?
            ksort($studentresponse);
            $studentletters = '';
            $rightletters = implode('', $this->correctresponses);
            foreach ($studentresponse as $answer) {
                if ($answer == '') {
                    $answer = '?';
                }
                $studentletters .= $answer;
            }
            if ($studentletters !== '') {
                $letterstates = $this->get_wordle_letter_states($rightletters, $studentletters);
                $this->nbmisplacedletters = substr_count($letterstates, '1');
            }
        }
        $count = 1;
        foreach ($question->answers as $answer) {
            $questiontext .= '<div class="input-wrapper">';
                $questiontext .= $this->embedded_element($qa, $count, $options, $letterstates);
                $questiontext .= '</div>' . ' ';
            $count++;
        }
        if ($wordle) {
            $this->page->requires->js_call_amd('qtype_guessit/wordlenavigation', 'init');
        } else {
            $this->page->requires->js_call_amd('qtype_guessit/gapsnavigation', 'init');
        }
        $output = "";
        // This is needed to display images or media etc.
        $output = $question->format_text($questiontext, $question->questiontextformat,
                $qa, 'question', 'questiontext', $question->id);
        $output = html_writer::tag('div', $output, ['class' => 'qtext']);
        $output .= $trieslefttxt;
        return $output;
    }


    /**
     * Get feedback. We over-ride the default question feedback display to arrange its elements as we want.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function feedback(question_attempt $qa, question_display_options $options): string {
        $result = '';
        // Display Help messages if exist.
        // Try to find the last graded step.
        $gradedstep = $this->get_graded_step($qa);
        $helptext = '';
        if ($gradedstep) {
            if ($gradedstep->has_behaviour_var('helpme') ) {
                $helptext = $this->get_extra_help($qa);
            }
            if ($helptext != '') {
                $result .= '<div class="que guessit giveword numpartscorrect">' . $helptext . '</div>';
            }
        }

        if ($options->numpartscorrect) {
            $result .= html_writer::nonempty_tag('div', $this->num_parts_correct($qa),
                    ['class' => 'numpartscorrect']);
        }

        if ($options->feedback) {
            $result .= html_writer::nonempty_tag('div', $this->specific_feedback($qa),
                    ['class' => 'specificfeedback']);
        }

        if ($options->generalfeedback) {
            $result .= html_writer::nonempty_tag('div', $this->general_feedback($qa),
                    ['class' => 'generalfeedback']);
        }
        return $result;
    }

    /**
     * Construct the gaps, e.g. textentry and set the state accordingly
     *
     * @param question_attempt $qa
     * @param number $place
     * @param question_display_options $options
     * @param string $letterstates
     * @return string
     */
    public function embedded_element(question_attempt $qa, $place, question_display_options $options, $letterstates) {
        /* fraction is the mark associated with this field, always 1 or 0 for this question type */
        /** @var \qtype_guessit_question $question */
        $question = $qa->get_question();
        $wordle = $question->wordle;
        $fieldname = $question->field($place);
        $studentanswer = $qa->get_last_qt_var($fieldname) ?? '';
        $studentanswer = htmlspecialchars_decode($studentanswer);
        $rightanswer = $question->get_right_choice_for($place);
        // Set size of gaps.
        if ($wordle) {
            $size = 1;
        } else if ($question->gapsizedisplay === 'gapsizematchword') {
            $size = $question->get_size($rightanswer);
        } else if ($question->gapsizedisplay === 'gapsizefixed') {
            $size = $question->maxgapsize;
        } else if ($question->gapsizedisplay === 'gapsizegrow') {
            $size = 6;
        }
        /* $options->correctness is really about it being ready to mark, */
        if (empty($studentanswer)) {
                $inputclass = '';
        } else {
            if (!$wordle) {
                if (empty($studentanswer)) {
                    $inputclass = '';
                } else if ($studentanswer === $rightanswer) {
                        $inputclass = 'correct';
                } else if (preg_match('/^' . preg_quote($studentanswer[0], '/') . '/i', $rightanswer, $matches)) {
                    $inputclass = 'partiallycorrect';
                    $newanswer = '';
                    $foundcasedifference = false; // Flag to stop at the first case mismatch.
                    // Loop through each character and compare.
                    for ($i = 0; $i < min(strlen($rightanswer), strlen($studentanswer)); $i++) {
                        // Check if letters are the same (case-insensitive).
                        if (strtolower($rightanswer[$i]) === strtolower($studentanswer[$i])) {
                            if ($rightanswer[$i] === $studentanswer[$i]) {
                                // Append to $newanswer if both letter and case match.
                                $newanswer .= $rightanswer[$i];
                            } else if (!$foundcasedifference) {
                                // If case is different, append the first mismatched letter and stop.
                                $newanswer = $rightanswer[$i];
                                $foundcasedifference = true;
                                break;
                            }
                        } else {
                            // Stop when letters are different.
                            break;
                        }
                    }
                    $studentanswer = $newanswer;
                } else {
                    $inputclass = 'incorrect';
                }
            } else {
                $index = (int)substr($fieldname, 1) - 1;
                $letterstate = $letterstates[$index];
                switch ($letterstate) {
                    case 2:
                        $inputclass = 'correct';
                        break;
                    case 1:
                        $inputclass = 'partiallycorrect';
                        break;
                    case 0:
                        $inputclass = 'incorrect';
                        break;
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
        // If wordle and maxtries reached, disable all input gaps.
        $prevtries = $qa->get_last_behaviour_var('_try', 0);
        $gradedstep = $this->get_graded_step($qa);
        if ($wordle && $prevtries !== 0) {
            $prevtries = $qa->get_last_behaviour_var('_try', 0);
            if ($gradedstep->has_behaviour_var('_maxtriesreached', 1) ) {
                $inputattributes['disabled'] = 'disabled';
            }
        }

        // Only use autogrowinput if gapsizedisplay is set to gapsizegrow.
        $autogrowinput = '';
        if ($question->gapsizedisplay === 'gapsizegrow') {
            $autogrowinput = ' auto-grow-input ';
        }
        $inputattributes['class'] = 'typetext guessit '. $autogrowinput. $inputclass;
        if ($studentanswer === $rightanswer) {
            $size = $question->get_size($rightanswer);
            $inputattributes['size'] = $size;
        }
        $inputattributes['spellcheck'] = 'false';
        $markupcode = "";
        if ($studentanswer !== $rightanswer && !$wordle) {
            $markupcode = $this->get_markup_string ($studentanswer, $rightanswer);
        }
        return html_writer::empty_tag('input', $inputattributes) . '<span class="markup">'.$markupcode.'</span>';
    }

    /**
     * Get feedback information
     *
     * @param question_attempt $qa
     * @return string
     */
    public function specific_feedback(question_attempt $qa) {
        $question = $qa->get_question();
        // Check that all gaps have been filled in.
        $complete = $this->check_complete_answer($qa);
        $formattedfeedback = '';
        if (!$complete) {
            $formattedfeedback .= '<span class="giveword">'. get_string('pleaseenterananswer', 'qtype_guessit') . '</span><hr />';
        }
        // No need to use specific feedback for the wordle option.
        $wordle = $question->wordle;
        $removespecificfeedback = $question->removespecificfeedback;
        $nbcorrect = $qa->get_question()->get_num_parts_right(
            $qa->get_last_qt_data()
        );
        if (($nbcorrect[0] === $nbcorrect[1]) && $removespecificfeedback == 1) {
            return '';
        }
        // Go through all student responses.
        $allresponses = $this->get_all_responses($qa);
        $nbtries = count($allresponses);
        if ($wordle) {
            $rightletters = implode('', $this->correctresponses);
            $letterstates = [];
        }
        for ($i = 0; $i < $nbtries; $i++) {
            $studentanswers = array_values($allresponses[$i]);
            // Format the feedback to display.
            $formattedfeedback .= '<b>' . ($nbtries - $i)  . '- </b>';
            if (!$wordle) {
                $markupcode = '';
                for ($index = 0; $index < count($this->correctresponses); $index++) {
                    $studentanswer = $studentanswers[$index];
                    $rightanswer = $this->correctresponses[$index];
                    $markupcode = $this->get_markup_string ($studentanswer, $rightanswer);
                    if ($studentanswer === $rightanswer) {
                        $colorclass = 'correct';
                        $markupcode = '';
                    } else if (preg_match('/^' . preg_quote($studentanswer[0], '/') . '/i',
                        $this->correctresponses[$index])) {
                            $colorclass = 'partiallycorrect';
                    } else {
                        $colorclass = 'incorrect';
                    }
                    $formattedfeedback .= '<div class="specific-feedback input-wrapper '.$colorclass.'">'.
                    $studentanswer. '<span class="feedback-markup">'.$markupcode. '</span></div>';
                }
            } else {
                $studentletters = '';
                foreach ($studentanswers as $answer) {
                    if ($answer == '') {
                        $answer = '?';
                    }
                    $studentletters .= $answer;
                }
                $letterstates[$i] = $this->get_wordle_letter_states($rightletters, $studentletters);
                for ($index = 0; $index < strlen($rightletters); $index++) {
                    $letterstate = $letterstates[$i];
                    switch ($letterstate[$index]) {
                        case 2:
                            $colorclass = 'correct';
                            break;
                        case 1:
                            $colorclass = 'partiallycorrect';
                            break;
                        case 0:
                            $colorclass = 'incorrect';
                            break;
                    }
                    if ($studentletters[$index] == '') {
                        $studentletters[$index] = '-';
                        $colorclass = '';
                    }
                    $formattedfeedback .= '<div class="specific-feedback input-wrapper '.$colorclass.'">'.
                        $studentletters[$index]. '</div>';
                }
            }
            $formattedfeedback  .= '<hr />';
        }
        return $formattedfeedback;
    }

    /**
     * overriding base class method purely to return a string
     * yougotnrightcount instead of default yougotnright
     *
     * @param question_attempt $qa
     * @return string
     */
    protected function num_parts_correct(question_attempt $qa) {
        // Check that all gaps have been filled in.
        $complete = $this->check_complete_answer($qa);
        if (!$complete) {
            return;
        }
        $question = $qa->get_question();
        $wordle = $question->wordle;
        $removespecificfeedback = $question->removespecificfeedback;
        $formattxt = '<span class="que guessit giveword numpartscorrect">';
        $nbcorrect = $qa->get_question()->get_num_parts_right(
                $qa->get_last_qt_data()
            );
        $prevtries = $qa->get_last_behaviour_var('_try', 0);
        if (($nbcorrect[0] === $nbcorrect[1]) && $removespecificfeedback == 1) {
            return '';
        }
        if ($nbcorrect[0] === $nbcorrect[1]) {
            $wordsfoundtxt = [
                "wordfoundintry" => get_string('wordfoundintry', 'qtype_guessit'),
                "wordfoundintries" => get_string('wordfoundintries', 'qtype_guessit', $prevtries),
                "wordsfoundintry" => get_string('wordsfoundintry', 'qtype_guessit'),
                "wordsfoundintries" => get_string('wordsfoundintries', 'qtype_guessit', $prevtries),
            ];

            if ($wordle) {
                if ($removespecificfeedback) {
                    return '';
                }
                $rightletters = implode('', $this->correctresponses);
                if ($prevtries > 1) {
                    return $formattxt . $wordsfoundtxt['wordfoundintries']. $rightletters. '</span>';
                } else {
                    return $formattxt . $wordsfoundtxt['wordfoundintry'].$rightletters. '</span>';
                }
            } else {
                if ($prevtries > 1) {
                    return $formattxt . $wordsfoundtxt['wordsfoundintries']. '</span>';
                } else {
                    return $formattxt . $wordsfoundtxt['wordsfoundintry']. '</span>';
                }
            }
        }
        $a = new stdClass();
        list($a->num, $a->outof) = $qa->get_question()->get_num_parts_right(
                $qa->get_last_qt_data()
            );
        if (!$wordle) {
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
        } else {
            $nbmaxtrieswordle = $question->nbmaxtrieswordle;
            $triesleft = $nbmaxtrieswordle - $prevtries;
            if ($a->num == 0 || $a->num > 1) {
                $a->letterorletters = get_string('letter_plural', 'qtype_guessit');
            } else {
                $a->letterorletters = get_string('letter_singular', 'qtype_guessit');
            }
            $a->nbmisplacedletters = $this->nbmisplacedletters;
            if ($this->nbmisplacedletters == 0 || $this->nbmisplacedletters > 1) {
                $a->misplacedletterorletters = get_string('misplacedletter_plural', 'qtype_guessit');
            } else {
                $a->misplacedletterorletters = get_string('misplacedletter_singular', 'qtype_guessit');
            }
            $trieslefttxt = '';
            if ($triesleft > 0) {
                $trieslefttxt = '<div class="que guessit giveword numpartscorrect">';
                if ($triesleft > 1 ) {
                    $trieslefttxt .= get_string('nbtriesleft_plural', 'qtype_guessit', $triesleft);
                } else {
                    $trieslefttxt .= get_string('nbtriesleft_singular', 'qtype_guessit');
                }
            }
            return get_string('yougotnlettersrightcount', 'qtype_guessit', $a) . $trieslefttxt;
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
            if (($studentletter === $answerletter)) {
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

    /**
     * Used in wordle option: stores correct (2) partiallycorrect (1) and incorrect (0)
     * values for each letter in student response.
     * @param string $rightletters
     * @param string $studentletters
     * @return string $marking
     */
    public function get_wordle_letter_states($rightletters, $studentletters) {
        $originalarray = str_split($rightletters);
        $responsearray = str_split($studentletters);
        $marking = "";

        // Array to keep track of used characters in the rightletters.
        $used = array_fill(0, strlen($rightletters), false);

        // First pass: check for exact matches.
        for ($i = 0; $i < strlen($rightletters); $i++) {
            if ($originalarray[$i] === $responsearray[$i]) {
                $marking .= "2";
                $used[$i] = true; // Mark this character as used.
            } else {
                $marking .= "0"; // Placeholder, will update in the second pass.
            }
        }
        // Second pass: check for characters in the wrong position.
        for ($i = 0; $i < strlen($studentletters); $i++) {
            if ($marking[$i] === "0") { // Only consider characters not already matched.
                $found = false;
                for ($j = 0; $j < strlen($rightletters); $j++) {
                    if (!$used[$j] && $responsearray[$i] === $originalarray[$j]) {
                        $marking[$i] = "1"; // Character present in original but wrong position.
                        $used[$j] = true; // Mark this character as used.
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $marking[$i] = "0"; // If not found, keep as "0".
                }
            }
        }

        return $marking;
    }

    /**
     * Retrieves all responses for a given question attempt.
     *
     * @param question_attempt $qa The question attempt object.
     * @return array An array of responses, each with 'name' and 'value', ordered by sequence.
     * @throws dml_exception If a database error occurs.
     */
    public function get_all_responses(question_attempt $qa) {
        $responses = [];
        $prevtries = $qa->get_last_behaviour_var('_try', 0);
        foreach ($qa->get_reverse_step_iterator() as $step) {
            if ($step->has_behaviour_var('submit') && $step->get_state() != question_state::$invalid) {
                $responses[] = $step->get_qt_data();
            }
        }
        // This is needed, don't know why.
        // Sort each inner array by its keys.
        foreach ($responses as &$innerarray) {
            ksort($innerarray);
        }
        return $responses;
    }

    /**
     * Get graded step.
     * @param question_attempt $qa a question attempt.
     */
    protected function get_graded_step(question_attempt $qa) {
        foreach ($qa->get_reverse_step_iterator() as $step) {
            if ($step->has_behaviour_var('_try')) {
                return $step;
            }
        }
    }

    /**
     * Provides extra help if requested based on the number of tries.
     *
     * @param question_attempt $qa the question attempt to display.
     * @return string The extra help content or a message indicating remaining tries.
     */
    public function get_extra_help(question_attempt $qa) {
        // Try to find the last graded step.
        $question = $qa->get_question();
        $nbtriesbeforehelp = $question->nbtriesbeforehelp;;
        $prevtries = $qa->get_last_behaviour_var('_try', 0);
        $output = '';
        $gradedstep = $this->get_graded_step($qa);
        $prevstep = $qa->get_last_step_with_behaviour_var('_try');
        $prevresponse = $prevstep->get_qt_data();
        if ($prevtries >= $nbtriesbeforehelp) {
            if (is_null($gradedstep) || !$gradedstep->has_behaviour_var('helpme')) {
                return '';
            }
            $answersarray = $question->answers;
            $answerlist = '';
            $counter = 1; // Start counter from 0.
            $nbanswers = count($answersarray);
            foreach ($answersarray as $key => $rightansweer) {
                if ($rightansweer->answer !== $prevresponse['p' . $counter] ) {
                    $answerlist .= '<b>' . $rightansweer->answer . '</b> ';
                    break;
                } else {
                    $answerlist .= $rightansweer->answer . ' ';
                }
                $counter++;
            }
            // Trim any extra whitespace at the end.
            $answerlist = trim($answerlist);
            $output .= '<span class="que guessit giveword">' . $answerlist . '</span>';
            return $output;
        }
        $triesleft = $nbtriesbeforehelp - $prevtries;
        if ($triesleft > 1) {
            return get_string('moretries', 'qtype_guessit', $triesleft);
        } else {
            return get_string('moretry', 'qtype_guessit', $triesleft);
        }
    }

}
