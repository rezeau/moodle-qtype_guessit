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
 * This script makes OK input fields with a class of "auto-grow-input" automatically resize
 * as the user types.
 */

/**
 * Initialize the input gaps functionalities.
 */
export function init() {

    // Make correctly filled in gaps readonly
    const correctGaps = document.querySelectorAll('input.correct');
    correctGaps.forEach((input) => {
      input.readOnly  = true; // Make the input readonly.
      input.style.cursor = "not-allowed"; // Set the cursor style.
    });

    // Get all the gaps in this question
    const allGaps = document.querySelectorAll('input[class*="guessit"]');
    allGaps.forEach(function (element){
         // Prevent space from being entered
        element.addEventListener("keydown", (event) => {
            if (event.key === ' ') {
                event.preventDefault();
            }
        });
        if (element.classList.contains('auto-grow-input')) {
            // Enable the input fields auto-grow feature (if set in the question options)
            element.addEventListener("input", function () {
                element.style.width = "auto"; // Reset width
                // Set the width to the content size plus 1 px for adjustment.
                element.style.width = (element.scrollWidth + 1) + "px";
            });
            // Trigger the event on page load to adjust the input width for pre-filled values
            element.dispatchEvent(new Event('input'));
        }
    });

}