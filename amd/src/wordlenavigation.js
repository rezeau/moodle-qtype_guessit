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
    document.querySelectorAll('[id^="question-"]').forEach(question => {
        const gaps = question.querySelectorAll('input[type="text"][name*="p"]');
        const checkButton = question.querySelector('button[type="submit"].submit');

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
                if (event.key === 'Tab' && event.shiftKey) {
                    // Prevent default behaviour of pressed keys
                    event.preventDefault();
                    let prevIndex = index - 1;
                    if (prevIndex !== -1) {
                        // Move to the next non-"correct" gap
                        var prevGap = gaps[prevIndex];
                        prevGap.focus();
                        gaps[prevIndex].value = '';
                        gaps[prevIndex].classList.remove('correct', 'partiallycorrect', 'incorrect');
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
    });
}