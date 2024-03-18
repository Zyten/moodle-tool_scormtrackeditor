<?php

namespace tool_scormtrackeditor\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for tool_scormtrackeditor implementing null_provider.
 *
 * @copyright   2023 Ruban Selvarajah <sruban707@hotmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class provider implements \core_privacy\local\metadata\null_provider {

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }
}

