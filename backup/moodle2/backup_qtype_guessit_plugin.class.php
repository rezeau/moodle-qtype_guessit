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
 * guessit question type backup
 *
 * @package qtype_guessit
 * @subpackage guessit
 * @copyright  2025 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_qtype_guessit_plugin extends backup_qtype_plugin {

    /**
     * returns the name of the plugin/question type
     *
     * @return string
     */
    protected static function qtype_name() {
        return 'guessit';
    }
    /**
     * Returns the qtype information to attach to question element
     */
    protected function define_question_plugin_structure() {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'guessit');

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        /* This qtype uses standard question_answers, add them here
         to the tree before any other information that will use them */
         $this->add_question_question_answers($pluginwrapper);

        // Now create the qtype own structures.
        $guessit = new backup_nested_element('guessit', ['id'], [
            'guessitgaps', 'nbtriesbeforehelp',
            'nbmaxtrieswordle', 'wordle']);

        // Now the own qtype tree.
        $pluginwrapper->add_child($guessit);

        // Set source to populate the data.
        $guessit->set_source_table('question_guessit',
                ['question' => backup::VAR_PARENTID]);

        // Don't need to annotate ids nor files.
        return $plugin;
    }

}
