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
 * @module qtype_guessit/gapsnavigation
 *
 * This script controls navigation in the GuessIt game.
 */

/**
 * Initialize the input gaps functionalities.
 */
export function init() {
    /* eslint-disable no-unused-vars */
    /* eslint-disable no-unused-vars */
    /* eslint-disable no-console */

    // Make correctly filled in gaps readonly
    const correctGaps = document.querySelectorAll('input.correct');
    correctGaps.forEach((input) => {
      input.readOnly  = true; // Make the input readonly.
      input.style.cursor = "not-allowed"; // Set the cursor style.
    });
    document.querySelectorAll('[id^="question-"]').forEach(question => {
        const gaps = question.querySelectorAll('input[type="text"][name*="p"]');
        const checkButton = question.querySelector('button[type="submit"].submit');

        gaps.forEach((element, index) => {
            // Prevent space from being entered and conditionally move focus
            element.addEventListener("keydown", (event) => {
                if (event.key === ' ' || (event.key === 'Tab' && !event.shiftKey)) {
                    event.preventDefault();
                    // Only move to the next gap if the current one is not empty
                    if (element.value.trim() !== '') {
                        let nextIndex = index + 1;
                        // Skip over any gaps with class "correct"
                        while (nextIndex < gaps.length && gaps[nextIndex].classList.contains('correct')) {
                            nextIndex++;
                        }
                        if (nextIndex < gaps.length) {
                            // Move to the next non-"correct" gap
                            var nextGap = gaps[nextIndex];
                            nextGap.focus();
                            var length = nextGap.value.length;
                            nextGap.setSelectionRange(length, length);
                            if (gaps[nextIndex].classList.contains('incorrect') ) {
                                gaps[nextIndex].value = '';
                                gaps[nextIndex].classList.remove('incorrect');
                            }
                        } else if (checkButton) {
                            // If it's the last gap in the question, move focus to the "Check" button
                            checkButton.focus();
                        }
                    }
                }
            });
            element.addEventListener("click", () => {
                if (element.classList.contains('incorrect')) {
                    element.value = ''; // Empty the incorrect gap value on click
                    element.classList.remove('incorrect'); // And remove the incorrect class
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
    });

}