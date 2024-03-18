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

/**
 * Class exposing the api for tool_scormtrackeditor.
 *
 * @package     tool_scormtrackeditor
 * @copyright   2023 Ruban Selvarajah <sruban707@hotmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_scormtrackeditor;

/**
 * Process the SCORM Track Editor form
 *
 * @package     tool_scormtrackeditor
 * @copyright   2023 Ruban Selvarajah <sruban707@hotmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /**
     * Clears SCORM track data for a list of users.
     *
     * @param int   $scoid          The SCORM ID for which to clear track data.
     * @param array $usernames      An array of usernames to process.
     * @return void
     */
    public static function clear_scorm_track_data_for_users($scoid, $usernames) {
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
}

