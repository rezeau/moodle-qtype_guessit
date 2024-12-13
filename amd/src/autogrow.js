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
 * @module qtype_guessit/autogrow
 *
 * This script makes input fields with a class of "auto-grow-input" automatically resize
 * as the user types.
 */
define(['jquery'], function() {

    /**
     * Initialize the auto-grow input functionality.
     */
    function init() {
        document.querySelectorAll('input[class*="auto-grow-input"]').forEach(function(e) {
            e.addEventListener("input", function() {
                e.style.width = "auto"; // Reset width
                e.style.width = (e.scrollWidth + 1) + "px"; // Set the width to the content size plus 1 px for adjustment.
            });
            e.addEventListener("keydown", (event) => {
                if (event.key === " ") {
                    event.preventDefault(); // Prevent space from being entered
                }
            });
            // Adjust the input width on page load (for pre-filled values)
            e.dispatchEvent(new Event('input'));
        });
    }

    // Expose the init function so it can be called from PHP
    return {
        init: init
    };
});
