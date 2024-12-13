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
 * JavaScript code for the gapfill question type.
 *
 * @copyright  2024 Joseph Rézeau
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module qtype_guessit/keyevents
 *
 * Upon pressing Space bar: move to the next gap OR to the Check button after the last gap.
 */
define(['jquery'], function() {

    /**
     * Override the space bar behaviour
     */
function init() {
        const inputs = document.querySelectorAll('input[class*="auto-grow-input"]');
        const checkButton = document.querySelector('button[type="submit"], button.check-button');
        // Adjust this selector as needed to target the "Check" button

        inputs.forEach(function(e, index) {
            e.addEventListener("keydown", (event) => {
                if (event.key === " ") {
                    event.preventDefault(); // Prevent space from being entered

                    if (index < inputs.length - 1) {
                        // Move focus to the next input field
                        inputs[index + 1].focus();
                    } else if (checkButton) {
                        // If it's the last input, move to the "Check" button
                        checkButton.focus();
                    }
                }
            });
        });
    }

    // Expose the init function so it can be called from PHP
    return {
        init: init
    };
});
