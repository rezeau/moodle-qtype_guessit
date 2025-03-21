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
 * @copyright  2025 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2012 Marcus Green <marcusavgreen@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
$string['editquestiontext'] = 'Edit question text';
$string['gap_plural'] = 'words';
$string['gap_singular'] = 'word';
$string['guessit'] = 'guessit.';
$string['guessitgaps'] = 'Guessit word(s)';
$string['guessitgaps_help'] = 'Enter here the words to be guessed (or one word if wordle option selected)';
$string['instructions'] = 'Instructions';
$string['instructions_help'] = 'Use this field to explain to the student how the Guessit game works or provide a hint to help guess the word(s). This field is optional';
$string['letter_plural'] = 'correctly placed letters';
$string['letter_singular'] = 'correctly placed letter';
$string['misplacedletter_plural'] = 'misplaced letters';
$string['misplacedletter_singular'] = 'misplaced letter';
$string['moretries'] = 'Help will be available after {$a} more tries!';
$string['moretry'] = 'Help will be available after 1 more try!';
$string['nbmaxtrieswordle'] = 'Maximum number of tries';
$string['nbmaxtrieswordle_help'] = 'Maximum number of tries allowed to guess the word. When the maximum number of tries has been reached, the word to be guessed is displayed and the question attempt is ended.';
$string['nbtriesbeforehelp'] = 'How many tries before giving help';
$string['nbtriesbeforehelp_help'] = 'Select the number of tries needed before the Help option becomes available. Never means that Help will never be available.';
$string['nbtriesleft_plural'] = '{$a} tries left';
$string['nbtriesleft_singular'] = '1 try left';
$string['never'] = 'Never';
$string['pleaseenterananswer'] = 'Please enter an answer in ALL the gaps.';
$string['pluginname'] = 'Guess It';
$string['pluginname_help'] = 'Ask the student to guess a short sentence or a unique word.';
$string['pluginname_link'] = 'question/type/guessit';
$string['pluginnameadding'] = 'Adding a Guess It Question.';
$string['pluginnameediting'] = 'Editing Guess It.';
$string['pluginnamesummary'] = 'A question type which is more like a game where the player has to guess a short sentence or a unique word (a la Wordle).';
$string['privacy:null_reason'] = 'The guessit question type does not effect or store any data itself.';
$string['wordfoundintries'] = 'Word found in {$a} tries: ';
$string['wordfoundintry'] = 'Word found in 1 try: ';
$string['wordle'] = 'Wordle: Guess a word';
$string['wordle_help'] = 'Check this option if you want the player to have to guess SINGLE words instead of sentences. Use only UPPERCASE LETTERS (A-Z) and no accents and no more than 8 letters.';
$string['wordlecapitalsonly'] = 'ERROR! In the Wordle option, You must type a single word and only use UPPERCASE LETTERS (A-Z) and no accents';
$string['wordletoolong'] = 'Too long! ERROR! In the Wordle option, words are limited to 8 characters.';
$string['wordsfoundintries'] = 'All words found in {$a} tries.';
$string['wordsfoundintry'] = 'All words found in 1 try only! Well-done!';
$string['wordssmissing'] = 'You have not included any word(s) to be found in your game of GuessIt!';
$string['yougotnlettersrightcount'] = 'You\'ve got {$a->num} {$a->letterorletters} and {$a->nbmisplacedletters} {$a->misplacedletterorletters}.';
$string['yougotnrightcount'] = 'You found {$a->num} {$a->gaporgaps} out of {$a->outof}.';
