<?php

if ($hassiteconfig) {
    $ADMIN->add('tools', new admin_externalpage('toolscormtrackeditor', get_string('pluginname', 'tool_scormtrackeditor'), "$CFG->wwwroot/$CFG->admin/tool/scormtrackeditor/index.php"));
}

