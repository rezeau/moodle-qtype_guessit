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
 * JavaScript code for the guessit question type.
 *
 * @copyright  2025 Joseph Rézeau
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module qtype_guessit/wordlenavigation
 *
 * This script controls the navigation in the Wordle game.
 */

/**
 * Initialize the input gaps functionalities.
 */
export function init() {
    /* eslint-disable no-unused-vars */
/* eslint-disable no-console */

    document.querySelectorAll('[id^="question-"]').forEach(question => {
        const gaps = question.querySelectorAll('input[type="text"][name*="p"][class*="wordlegap"]');
        const correctGaps = document.querySelectorAll('input.correct');
        const finished = (correctGaps.length == gaps.length);
        const checkButton = question.querySelector('button[type="submit"].submit');
        if (finished) {
                correctGaps.forEach((input) => {
                input.readOnly = true; // Make the input readonly.
                input.style.cursor = "not-allowed"; // Set the cursor style.
            });
        } else {

        gaps.forEach((element, index) => {
            // Empty the gap when clicked
            element.addEventListener("click", () => {
                element.value = ''; // Empty the gap on click
                // And remove all colour classes
                element.classList.remove('correct', 'partiallycorrect', 'incorrect');
            });

            // Listen for keydown to capture the key press and prevent more than one character
            element.addEventListener("keydown", (event) => {
                // Allow only letters (A-Z, a-z)
                if (!/^[a-zA-Z]$/.test(event.key)) {
                    event.preventDefault();
                }
            });

            element.addEventListener("keyup", (event) => {
                // Enable navigation with backtab to previous gap in order to modify it
                if (event.key === 'Tab') {
                    if (event.shiftKey) {
                        // Prevent default behaviour of pressed keys
                        event.preventDefault();
                        let prevIndex = index - 1;
                    } else {
                        // Prevent default behaviour of pressed keys
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
                                // Set caret at the end of the gap contents (value)
                                nextGap.setSelectionRange(length, length);
                                if (gaps[nextIndex].classList.contains('incorrect')) {
                                    gaps[nextIndex].value = '';
                                    gaps[nextIndex].classList.remove('incorrect');
                                }
                            } else if (checkButton) {
                                // If it's the last gap in the question, move focus to the "Check" button
                                checkButton.focus();
                            }
                        }
                    }
                }
            });

            element.addEventListener("input", () => {
                // Ensure only one character is allowed
                if (element.value.length > 1) {
                    element.value = element.value.charAt(0); // Keep only the first character
                }
                // Convert lowercase to uppercase characters
                element.value = element.value.toUpperCase();
                // Automatically move to the next input if a letter is typed
                if (/^[a-zA-Z]$/.test(element.value)) {
                    let nextIndex = index + 1;
                    if (nextIndex < gaps.length) {
                        let nextGap = gaps[nextIndex];
                        // Clear the next gap if it's already filled
                            if (nextGap.value.trim() !== '') {
                                nextGap.value = '';
                            }
                            // Move focus to the next input field
                            nextGap.focus();
                            // And remove all colour classes
                            nextGap.classList.remove('correct', 'partiallycorrect', 'incorrect');
                    } else if (checkButton) {
                        // If it's the last gap, move focus to the "Check" button
                        checkButton.focus();
                    }
                }
            });
        });
        }
    });
}