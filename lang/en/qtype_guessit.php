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
 * @package qtype_guessit
 * @subpackage guessit
 * @copyright  2024 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2012 Marcus Green <marcusavgreen@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
$string['blank'] = 'blank';
$string['casesensitive'] = 'Case Sensitive';
$string['casesensitive_help'] = 'When this is checked, if the correct answer is CAT, cat will be flagged as a wrong answer';
$string['correct'] = 'Feedback for correct.';
$string['correctanswer'] = 'Well done!';
$string['displayguessit'] = 'guessit';
$string['editquestiontext'] = 'Edit question text';
$string['gap_plural'] = 'words';
$string['gap_singular'] = 'word';
$string['gapsize_display'] = 'Select Gap Size to display';
$string['gapsize_display_help'] = 'Select Gap Size to display when attempting the question:

"Dynamic Gap Size" = each gap will grow to accomodate longish words if needed.

"Proportional Gap Size" = each gap will be set to the same size as the gapped out word.

"Fixed Gap Size" = all gaps will be set to the same size as the largest gap.';
$string['gapsize_fixed'] = 'Fixed Gap Size';
$string['gapsize_grow'] = 'Dynamic Gap Size';
$string['gapsize_matchword'] = 'Proportional Gap Size';
$string['guessit'] = 'guessit.';
$string['incorrect'] = 'Feedback for incorrect.';
$string['moreoptions'] = 'More Options.';
$string['nbpreventriesbeforehelp'] = 'How many tries before giving help';
$string['nbpreventriesbeforehelp_help'] = 'Help for How many tries before giving help';
$string['or'] = 'or';
$string['pleaseenterananswer'] = 'Please enter an answer in ALL the gaps.';
$string['pluginname'] = 'Guess It';
$string['pluginname_help'] = 'Place the words to be found within square brackets e.g. [The] [cat] [sat] [on] [the] [mat].';
$string['pluginname_link'] = 'question/type/guessit';
$string['pluginnameadding'] = 'Adding a Guess It Question.';
$string['pluginnameediting'] = 'Editing Guess It.';
$string['pluginnamesummary'] = 'A Guess It style question.';
$string['privacy:null_reason'] = 'The guessit question type does not effect or store any data itself.';
$string['questionsmissing'] = 'You have not included any words to be found in your question text';
$string['questiontext'] = "Question text";
$string['questiontext_help'] = "Put square brackets [...] around the words to be guessed.";
$string['yougotnrightcount'] = 'You found {$a->num} {$a->gaporgaps} out of {$a->outof}.';
