<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$mainAdminId = 2;

if ($USER->id != $mainAdminId) {
    print_error('accessdenied', 'admin');
}

admin_externalpage_setup('toolscormtrackeditor');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_scormtrackeditor'));

require_once($CFG->dirroot.'/admin/tool/scormtrackeditor/classes/form/scormtrack_editor_form.php');

$mform = new scormtrack_editor_form();

// Check for confirmation and parameters
$confirmed = optional_param('confirmed', 0, PARAM_INT);
$scoid = optional_param('scoid', null, PARAM_INT);
$usernames = optional_param('usernames', '', PARAM_TEXT);

if ($confirmed && $scoid !== null && $usernames !== '') {
    // Process the form after confirmation
    $list_usernames = explode(',', $usernames);
    process_form_submission($scoid, $list_usernames);
} else {
    if ($mform->is_cancelled()) {
        // Handle form cancel operation
    } else if ($fromform = $mform->get_data()) {
        global $DB;

        $scoid = trim($fromform->scoid);
        $usernames = explode(',', trim($fromform->usernames));
        $list_usernames = array_map('trim', $usernames);

        // Show confirmation step
        $scormNameSQL = "SELECT name FROM {scorm} WHERE id = (SELECT scorm FROM {scorm_scoes} WHERE id = :scoid)";
        $scoTitleSQL = "SELECT title FROM {scorm_scoes} WHERE id = :scoid";
        $params = array('scoid' => $scoid);
        $scormName = $DB->get_field_sql($scormNameSQL, $params);
        $scoTitle = $DB->get_field_sql($scoTitleSQL, $params);

        $confirmationMessage = "This will permanently reset completion tracking for " . $scoTitle . " in " . $scormName . " for users: <br>[" . implode(', ', $list_usernames) . "]";
        echo $OUTPUT->confirm($confirmationMessage, new moodle_url($PAGE->url, array('confirmed' => 1, 'scoid' => $scoid, 'usernames' => implode(',', $list_usernames))), $PAGE->url);
    } else {
        // Display the form
        $mform->display();
    }
}

echo $OUTPUT->footer();

function process_form_submission($scoid, $list_usernames) {
    global $DB, $OUTPUT;

    $validUserIds = [];
    $invalidUsernames = [];

    foreach ($list_usernames as $username) {
        $userid = $DB->get_field('user', 'id', ['username' => $username]);
        if ($userid) {
            $validUserIds[] = $userid;
        } else {
            $invalidUsernames[] = $username;
        }
    }

    if (!empty($validUserIds)) {
        list($usersql, $userparams) = $DB->get_in_or_equal($validUserIds, SQL_PARAMS_NAMED, 'param1000');
        $updatesql = "UPDATE {scorm_scoes_track}
                      SET value = ''
                      WHERE (element = 'cmi.core.exit' OR element = 'cmi.suspend_data' OR element = 'cmi.core.lesson_status')
                      AND scoid = :scoid
                      AND userid $usersql";

        $params = array('scoid' => $scoid) + $userparams;
        $DB->execute($updatesql, $params);

        echo $OUTPUT->notification(get_string('updatesuccess', 'tool_scormtrackeditor'), 'notifysuccess');
    }

    if (!empty($invalidUsernames)) {
        echo $OUTPUT->notification(get_string('invalidusernames', 'tool_scormtrackeditor') . implode(', ', $invalidUsernames), 'notifyproblem');
    } elseif (empty($validUserIds)) {
        echo $OUTPUT->notification(get_string('novaildusers', 'tool_scormtrackeditor'), 'notifyproblem');
    }
}

