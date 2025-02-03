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
 * This script controls the navigation in the Wordler game.
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
            });
            // Listen for keydown to capture the key press and prevent more than one character
           element.addEventListener("keydown", (event) => {
    // Block Backspace, Delete, Tab, and Arrow keys
    const forbiddenKeys = ["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight", "ArrowUp", "ArrowDown"];
    if (forbiddenKeys.includes(event.key)) {
        event.preventDefault(); // Stop default behavior
        return;
    }

    // Allow only letters (A-Z, a-z)
    if (!/^[a-zA-Z]$/.test(event.key)) {
        event.preventDefault(); // Block any non-letter character
    }
});

element.addEventListener("keyup", (event) => {
    // Move to the next input gap after typing a valid letter
    if (/^[a-zA-Z]$/.test(event.key)) {
        let nextIndex = index + 1;
        if (nextIndex < gaps.length) {
            // Clear the next gap if it's already filled
            if (gaps[nextIndex].value.trim() !== '') {
                gaps[nextIndex].value = '';
            }
            // Move focus to the next input field
            gaps[nextIndex].focus();
        } else if (checkButton) {
            // If it's the last gap, move focus to the "Check" button
            checkButton.focus();
        }
    }
});

            element.addEventListener("input", function() {
                    element.value = element.value.toUpperCase();
            });
        });
    });
}