<?php

require_once("$CFG->libdir/formslib.php");

class scormtrack_editor_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        // Add a section header
        $mform->addElement('header', 'resetprogress', get_string('resetprogress', 'tool_scormtrackeditor'));

        // Existing form elements
        $mform->addElement('text', 'scoid', get_string('scoid', 'tool_scormtrackeditor'));
        $mform->setType('scoid', PARAM_INT);

        $mform->addElement('textarea', 'usernames', get_string('usernames', 'tool_scormtrackeditor'));
        $mform->setType('usernames', PARAM_TEXT);

	$mform->addElement('submit', 'resetbutton', get_string('reset', 'tool_scormtrackeditor'));
    }
}

