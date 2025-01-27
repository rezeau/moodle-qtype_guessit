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
 * @module qtype_guessit/wordlenavigation
 *
 * This script controls navigation in the Wordle game.
 */

/**
 * Initialize the input gaps functionalities.
 */
export function init() {

    // Make correctly filled in gaps readonly
    const correctGaps = document.querySelectorAll('input.correct');
    correctGaps.forEach((input) => {
      input.readOnly = true; // Make the input readonly.
      input.style.cursor = "not-allowed"; // Set the cursor style.
    });

    // Reset incorrect and partiallycorrect letters upon retry.
    /*
    const incorrectGaps = document.querySelectorAll('input.incorrect, input.partiallycorrect');
    incorrectGaps.forEach((element) => {
      element.classList.remove('incorrect');
      element.classList.remove('partiallycorrect');
    });
*/
    document.querySelectorAll('[id^="question-"]').forEach(question => {
        const gaps = question.querySelectorAll('input[type="text"][name*="p"]');
        const checkButton = question.querySelector('button[type="submit"].submit');

        gaps.forEach((element, index) => {
            // Empty the gap when clicked, if it's not correct
            element.addEventListener("click", () => {
                if (!element.classList.contains('correct')) {
                    element.value = ''; // Empty the gap on click
                }
            });
            // Listen for keydown to capture the key press and prevent more than one character
            element.addEventListener("keydown", (event) => {
                // Allow only ASCII alphabet letters
                if ((event.which >= 65 && event.which <= 90) || (event.which >= 97 && event.which <= 122)) {
                    if (element.value.length >= 1) {
                        event.preventDefault(); // Prevent entering more than one character
                    }
                } else {
                    event.preventDefault(); // Prevent any other characters
                }
            });
            element.addEventListener("keyup", (event) => {
                // After the letter has been typed, move to the next input
                // But do not move if tab has been pressed
                if ((event.which >= 65 && event.which <= 90)
                        || (event.which >= 97 && event.which <= 122)
                        && event.which !== 9 // Tab key
                        && (element.value.length !== 0)) {
                    let nextIndex = index + 1;

                    // Skip over any gaps with class "correct"
                    while (nextIndex < gaps.length && gaps[nextIndex].classList.contains('correct')) {
                        nextIndex++;
                    }

                    if (nextIndex < gaps.length) {
                        // Check if the next non-"correct" gap is not empty, then empty it
                        if (gaps[nextIndex].value.trim() !== '') {
                            gaps[nextIndex].value = ''; // Empty the gap
                        }
                        // Move to the next non-"correct" gap
                        gaps[nextIndex].focus();
                    } else if (checkButton) {
                        // If it's the last gap in the question, move focus to the "Check" button
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