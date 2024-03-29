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

namespace tool_scormtrackeditor\form;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");

class scormtrack_editor_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'resetprogress', get_string('resetprogress', 'tool_scormtrackeditor'));

        $mform->addElement('text', 'scoid', get_string('scoid', 'tool_scormtrackeditor'));
        $mform->setType('scoid', PARAM_INT);

        $mform->addElement('textarea', 'usernames', get_string('usernames', 'tool_scormtrackeditor'));
        $mform->setType('usernames', PARAM_TEXT);

        $mform->addElement('submit', 'resetbutton', get_string('reset', 'tool_scormtrackeditor'));
    }
}

