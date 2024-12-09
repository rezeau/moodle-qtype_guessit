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
 * The language strings for component 'qtype_guessit', language 'en'
 *
 * @copyright &copy; 2012 Marcus Green
 * @author marcusavgreen@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package qtype_guessit
 */
defined('MOODLE_INTERNAL') || die();
$string['casesensitive'] = 'Case Sensitive';
$string['casesensitive_help'] = 'When this is checked, if the correct answer is CAT, cat will be flagged as a wrong answer';

$string['correct'] = 'Feedback for correct.';
$string['editquestiontext'] = 'Edit question text';
$string['incorrect'] = 'Feedback for incorrect.';

$string['pluginnameediting'] = 'Editing Guess It.';
$string['pluginnameadding'] = 'Adding a Guess It Question.';

$string['guessit'] = 'guessit.';

$string['displayguessit'] = 'guessit';

$string['pluginname'] = 'Guess It';
$string['pluginname_help'] = 'Place the words to be found within square brackets e.g. [The] [cat] [sat] [on] [the] [mat].';

$string['pluginname_link'] = 'question/type/guessit';
$string['pluginnamesummary'] = 'A Guess It style question.';
$string['questionsmissing'] = 'You have not included any words to be found in your question text';

$string['pleaseenterananswer'] = 'Please enter an answer.';
$string['fixedgapsize'] = 'Fixed Gap Size';
$string['fixedgapsize_help'] = 'When attempting the question all gaps will be set to the same size as the largest gap. This removes gap size as a clue to the correct answer.';
$string['fixedgapsizeset_text'] = 'Sets the size of every gap to that of the biggest gap';
$string['moreoptions'] = 'More Options.';
$string['blank'] = 'blank';
$string['or'] = 'or';

$string['gap_singular'] = 'word';
$string['gap_plural'] = 'words';
$string['yougotnrightcount'] = 'You found {$a->num} {$a->gaporgaps} out of {$a->outof}.';
$string['correctanswer'] = 'Well done!';

/* Used in the settings */
$string['questiontext'] = "Question text";
$string['questiontext_help'] = "Put delimiters around the words that will become the text";

$string['privacy:null_reason'] = 'The guessit question type does not effect or store any data itself.';
