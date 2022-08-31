<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_greetings
 * @category    string
 * @copyright   JcSomeShots <juancarlo.castillo20@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/** Accessible forms. Create a personal form submit. */
class local_greetings_accessibility_message_form extends moodleform {

    /**
     * Define the form.
     */
    public function definition_accessible() {
        $mform    = $this->_form; // Don't forget the underscore!

        // To create a textarea.
        $mform->addElement('textarea', 'messageaccessible', get_string('yourmessageaccessible', 'local_greetings'));
        $mform->setType('messageaccessible', PARAM_TEXT);

        // Add a submit button to the form with the following code.
        $submit = get_string('submit');
        $mform->addElement('submit', 'submitmessage', $submit);
    }

}
