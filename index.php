<?php
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

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$mainadminid = 2;

if ($USER->id != $mainadminid) {
    throw new moodle_exception('accessdenied', 'admin');
}

admin_externalpage_setup('toolscormtrackeditor');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_scormtrackeditor'));

require_once($CFG->dirroot.'/admin/tool/scormtrackeditor/classes/form/scormtrack_editor_form.php');

$mform = new scormtrack_editor_form();

$confirmed = optional_param('confirmed', 0, PARAM_INT);
$scoid = optional_param('scoid', null, PARAM_INT);
$usernames = optional_param('usernames', '', PARAM_TEXT);

if ($confirmed && $scoid !== null && $usernames !== '') {
    $usernames = explode(',', $usernames);
    process_form_submission($scoid, $usernames);
} else {
    if ($mform->is_cancelled()) {
        $pluginurl = new moodle_url('/admin/tool/scormtrackeditor/index.php');
        redirect($pluginurl);
    } else if ($fromform = $mform->get_data()) {
        global $DB;

        $scoid = trim($fromform->scoid);
        $usernames = explode(',', trim($fromform->usernames));
        $usernameslist = array_map('trim', $usernames);

        // Show confirmation step.
        $queries = new stdClass();
        $queries->scormname = "SELECT name
                                 FROM {scorm}
                                WHERE id = (
                                      SELECT scorm
                                        FROM {scorm_scoes}
                                       WHERE id = :scoid
                                )";

        $queries->scotitle = "SELECT title
                                FROM {scorm_scoes}
                               WHERE id = :scoid";

        $params = ['scoid' => $scoid];

        $scormname = $DB->get_field_sql($queries->scormname, $params);
        $scotitle = $DB->get_field_sql($queries->scotitle, $params);

        $message = "This will permanently reset completion tracking for " . $scotitle . " in " . $scormname
         . " for users: <br>[" . implode(', ', $usernameslist) . "]";
        echo $OUTPUT->confirm($message, new moodle_url($PAGE->url,
         ['confirmed' => 1, 'scoid' => $scoid, 'usernames' => implode(',', $usernameslist)]), $PAGE->url);
    } else {
        $mform->display();
    }
}

echo $OUTPUT->footer();

function process_form_submission($scoid, $usernames) {
    global $DB, $OUTPUT;

    $validuserids = [];
    $invalidusernames = [];

    foreach ($usernames as $username) {
        $userid = $DB->get_field('user', 'id', ['username' => $username]);
        if ($userid) {
            $validuserids[] = $userid;
        } else {
            $invalidusernames[] = $username;
        }
    }

    if (!empty($validuserids)) {
        list($usersql, $userparams) = $DB->get_in_or_equal($validuserids, SQL_PARAMS_NAMED, 'param1000');
        $updatesql = "UPDATE {scorm_scoes_track}
                         SET value = ''
                       WHERE (element = 'cmi.core.exit'
                             OR element = 'cmi.suspend_data'
                             OR element = 'cmi.core.lesson_status'
                             )
                             AND scoid = :scoid
                             AND userid $usersql";

        $params = ['scoid' => $scoid] + $userparams;
        $DB->execute($updatesql, $params);

        echo $OUTPUT->notification(get_string('updatesuccess', 'tool_scormtrackeditor'), 'notifysuccess');
    }

    if (!empty($invalidusernames)) {
        echo $OUTPUT->notification(get_string('invalidusernames', 'tool_scormtrackeditor') . implode(', ', $invalidusernames),
         'notifyproblem');
    } else if (empty($validuserids)) {
        echo $OUTPUT->notification(get_string('novaildusers', 'tool_scormtrackeditor'),
         'notifyproblem');
    }
}

