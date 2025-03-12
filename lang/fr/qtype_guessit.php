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
$string['gap_plural'] = 'mots';
$string['gap_singular'] = 'mot';
$string['gethelp'] = 'Aide';
$string['guessit'] = 'devinette.';
$string['guessitgaps'] = 'Mot(s) à deviner';
$string['guessitgaps_help'] = 'Entrez ici les mots à deviner (ou un seul mot si l\'option Wordle est sélectionnée).';
$string['instructions'] = 'Consignes';
$string['instructions_help'] = 'Utilisez ce champ pour expliquer à l\'étudiant le fonctionnement du jeu de devinette ou un indice pour trouver le ou les mots. Ce champ est optionnel.';
$string['letter_plural'] = 'lettres correctement placées';
$string['letter_singular'] = 'lettre correctement placée';
$string['misplacedletter_plural'] = 'lettres mal placées';
$string['misplacedletter_singular'] = 'lettre mal placée';
$string['moretries'] = 'L\'aide sera disponible après encore {$a} tentatives.';
$string['moretry'] = 'L\'aide sera disponible après encore 1 tentative.';
$string['nbmaxtrieswordle'] = 'Nombre maximum de tentatives';
$string['nbmaxtrieswordle_help'] = 'Nombre maximum de tentatives pour deviner le mot. Lorsque le nombre maximum de tentatives a été atteint, le mot à deviner s\'affiche et la tentative de question est terminée.';
$string['nbtriesbeforehelp'] = 'Combien de tentatives avant d\'obtenir de l\'aide ?';
$string['nbtriesbeforehelp_help'] = 'Sélectionnez le nombre de tentatives nécessaires avant que l\'option d\'aide ne devienne disponible. La mention « Jamais » signifie que l\'aide ne sera jamais disponible.';
$string['nbtriesleft_plural'] = 'Il reste encore {$a} tentatives';
$string['nbtriesleft_singular'] = 'Il reste encore 1 tentative';
$string['never'] = 'Jamais';
$string['pleaseenterananswer'] = 'Veuillez entrer un mot dans TOUS les blancs.';
$string['pluginname'] = 'Devinette';
$string['pluginname_help'] = 'Demander à l\'étudiant de deviner une courte phrase ou un mot unique.';
$string['pluginname_link'] = 'question/type/guessit';
$string['pluginnameadding'] = 'Ajout d\'une question de type Devinette.';
$string['pluginnameediting'] = 'Édition d\'une question de type Devinette.';
$string['pluginnamesummary'] = 'Une question de type Devinette. Le joueur doit deviner une courte phrase ou un mot unique (à la Wordle).';
$string['privacy:null_reason'] = 'Le type de question Devinette ne stocke aucune donnée.';
$string['wordfoundintries'] = 'Mot trouvé en {$a} tentatives : ';
$string['wordfoundintry'] = 'Mot trouvé en 1 tentative : ';
$string['wordle'] = 'Option Wordle : Deviner un mot unique';
$string['wordle_help'] = 'Cochez cette option si vous voulez que le joueur doive deviner un seul mot au lieu d\'une phrase. Utilisez uniquement des LETTRES MAJUSCULES (A-Z) et pas d\'accents.';
$string['wordlecapitalsonly'] = 'ERREUR ! Dans l\'option Wordle, vous devez saisir un seul mot formé uniquement de LETTRES MAJUSCULES (A-Z) et sans accents.';
$string['wordletoolong'] = 'ERREUR ! Trop long. Dans l\'option Wordle les mots sont limités à 8 caractères.';
$string['wordnotfound'] = 'Mot non deviné en {$a} tentatives : ';
$string['wordsfoundintries'] = 'Mots devinés en {$a} tentatives : ';
$string['wordsfoundintry'] = 'Tous les mots ont été trouvés en une seule tentative ! Bien joué !';
$string['wordssmissing'] = 'Vous n\'avez pas inclus de mot(s) à deviner dans votre jeu de devinette !';
$string['yougotnlettersrightcount'] = 'Vous avez {$a->num} {$a->letterorletters} et {$a->nbmisplacedletters} {$a->misplacedletterorletters}.';
$string['yougotnrightcount'] = 'Vous avez trouvé {$a->num} {$a->gaporgaps} sur {$a->outof}.';
