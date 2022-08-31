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

require_once('../../config.php');
require_once($CFG->dirroot. '/local/greetings/lib.php');
require_once($CFG->dirroot. '/local/greetings/message_form.php');
// Rrequire_once($CFG->dirroot. '/local/greetings/message_accessible_form.php');.


$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/greetings/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading(get_string('pluginname', 'local_greetings'));


require_login();

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

// Add capability check in your plugin, allow to post a message.
$allowpost = has_capability('local/greetings:postmessages', $context);

// Add capability in your plugin, to delete their own post.
$deletepost = has_capability('local/greetings:deleteownmessage', $context);

// Add capability in your plugin, to delete any post.
$deleteanypost = has_capability('local/greetings:deleteanymessages', $context);

// The real action to delete a message.
$action = optional_param('action', '', PARAM_TEXT);

if ($action == 'del') {
    // Adding sesskey protection.
    require_sesskey();

    $id = required_param('id', PARAM_TEXT);

    // Only proceed with deleting the message if the user has be required permission.
    if ($deleteanypost || $deletepost ) {
        $params = array('id' => $id);

        // Users without permission should only delete their own post.
        // In the original code o referent code there is putting in a negative way !$deleteanypost. But I can run it like this.
        if($deleteanypost) {
            $params += ['userid' => $USER->id];
        }

        // TO DO: Confirm before deleting.
        $DB->delete_records('local_greetings_messages', $params);

        redirect($PAGE->url);
    }
}


// Invoke a personal form.
$messageform = new local_greetings_message_form();

// Invoke a personal accessible form.
// A $messageformaccessible = new local_greetings_accessible_message_form();.


if ($data = $messageform->get_data()) {
    require_capability('local/greetings:postmessages', $context);

    $message = required_param('message', PARAM_TEXT);

    if (!empty($message)) {
        $record = new stdClass;
        $record->message = $message;
        $record->timecreated = time();
        $record->userid = $USER->id;

        $DB->insert_record('local_greetings_messages', $record);
    }
}

echo $OUTPUT->header();

// Say hello to a user.
if (isloggedin()) {
    echo local_greetings_get_greeting($USER);
} else {
    echo get_string('greetinguser', 'local_greetings');
}

// Check if the user has the relevant permission.
if ($allowpost) {
    $messageform->display();
}


if (has_capability('local/greetings:viewmessages', $context)) {

    $userfields = \core_user\fields::for_name()->with_identity($context);
    $userfieldssql = $userfields->get_sql('u');


    // To check which user make a post.
    $sql = "SELECT m.id, m.message, m.timecreated, m.userid {$userfieldssql->selects}
            FROM {local_greetings_messages} m
        LEFT JOIN {user} u ON u.id = m.userid
        ORDER BY timecreated DESC";

    $messages = $DB->get_records_sql($sql);


    echo $OUTPUT->box_start('card-columns');

    foreach ($messages as $m) {
        echo html_writer::start_tag('div', array('class' => 'card'));
        echo html_writer::start_tag('div', array('class' => 'card-body'));
        echo html_writer::tag('p', format_text($m->message, FORMAT_PLAIN), array('class' => 'card-text'));
        echo html_writer::tag('p', get_string('postedby', 'local_greetings', $m->firstname), array('class' => 'card-text'));
        echo html_writer::start_tag('p', array('class' => 'card-text'));
        echo html_writer::tag('small', userdate($m->timecreated), array('class' => 'text-muted'));
        echo html_writer::end_tag('p');

        if ( $deleteanypost  || ($deletepost && $m->userid == $USER->id)) {
            echo html_writer::start_tag('p', array('class' => 'card-footer text-center'));
            echo html_writer::link(
                new moodle_url(
                    '/local/greetings/index.php',
                    array('action' => 'del', 'id' => $m->id , 'sesskey' => sesskey())
                ),
                // The Output line is commented cause that show icon and text in not accessebility way.
                // O$OUTPUT->pix_icon('t/delete', '') . get_string('delete').
                
                $OUTPUT->pix_icon('t/delete', ''), array('role' => 'button', 'aria-label' => get_string('delete'), 'title' => get_string('delete'))
            );

            // Who to include an Icon?
            // echo html_writer::link(
            //     new moodle_url(
            //         '/local/greetings/index.php',
            //         array('action' => 'update', 'id' => $m->id , 'sesskey' => sesskey())
            //     ),
            //     // Create a update icon.
            //     // $OUTPUT->pix_icon('t/refresh', '') . get_string('update').

            //     $OUTPUT->pix_icon('', ''), array('role' => 'button', 'aria-label' => get_string('update'), 'title' => get_string('update'))
            // );
            echo html_writer::end_tag('p');
        }

        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
    }

    echo $OUTPUT->box_end();
}


echo $OUTPUT->footer();

