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
 * Used when restoring a backup of a course that contains guessit questions
 *
 * @package    qtype_guessit
 * @subpackage backup-moodle2
 * @copyright  2025 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Information to restore a backup of a guessit question
 *
 * Also used if you click the duplicate quiz button in a course.
 *
 * @copyright  2025 Joseph Rézeau <moodle@rezeau.org>
 * @copyright  based on GapFill by 2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_guessit_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {

        $paths = [];

        // This qtype uses question_answers, add them.
        $this->add_question_question_answers($paths);

        // Add own qtype stuff.
        $elename = 'guessit';
        // We use get_recommended_name() so this works.
        $elepath = $this->get_pathfor('/guessit');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

     /**
      * Process the qtype/guessit element
      *
      * @param array $data
      */
    public function process_guessit($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;

        // If the question has been created by restore, we need to create its question_guessit too.
        if ($questioncreated) {
            // Adjust value to link back to the questions table.
            $data->question = $newquestionid;
            // Insert record.
            $newitemid = $DB->insert_record('question_guessit', $data);
            // Create mapping (needed for decoding links).
            $this->set_mapping('question_guessit', $oldid, $newitemid);
        }
    }

    #[\Override]
    public static function convert_backup_to_questiondata(array $backupdata): \stdClass {
        $questiondata = parent::convert_backup_to_questiondata($backupdata);
        $questiondata->options->answers = array_map(
            fn($answer) => (object) $answer,
            $backupdata['plugin_qtype_guessit_question']['answers']['answer'] ?? [],
        );
        return $questiondata;
    }

    /**
     * Return a list of paths to fields to be removed from questiondata before creating an identity hash.
     * We have to remove the id property from all answers.
     *
     * @return array
     */
    protected function define_excluded_identity_hash_fields(): array {
        return [
            '/options/settings/itemid',
            '/options/settings/correctfeedback',
            '/options/settings/incorrectfeedback',
            '/options/question',
            '/options/itemsettings',
        ];
    }

}
