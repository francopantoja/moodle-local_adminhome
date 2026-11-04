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
 * Hook callbacks for local_adminhome.
 *
 * @package     local_adminhome
 * @copyright   2026 by IDEF https://idef21.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_adminhome;

use core\hook\output\before_http_headers;

/**
 * Hook callbacks.
 */
class hook_callbacks {

    /**
     * Redirect the chosen users from the site frontpage to the custom home.
     *
     * Runs before HTTP headers are sent, so a clean redirect is possible.
     *
     * @param before_http_headers $hook
     */
    public static function before_http_headers(before_http_headers $hook): void {
        global $PAGE;

        // Only on the site frontpage.
        if (!isset($PAGE) || $PAGE->pagetype !== 'site-index') {
            return;
        }

        // Only when the plugin is enabled.
        if (!get_config('local_adminhome', 'enabled')) {
            return;
        }

        // Only for logged-in users (not guests) chosen in the settings.
        if (!isloggedin() || isguestuser() || !self::is_target_user()) {
            return;
        }

        // Escape hatch: ?noadminhome=1 shows the real frontpage to whoever may see it.
        if (optional_param('noadminhome', 0, PARAM_BOOL) && self::can_see_frontpage()) {
            return;
        }

        redirect(new \moodle_url('/local/adminhome/index.php'));
    }

    /**
     * Whether the current user may go to the normal frontpage: admins always, the rest if the setting allows it.
     *
     * @return bool
     */
    public static function can_see_frontpage(): bool {
        return is_siteadmin() || (bool) get_config('local_adminhome', 'showfrontpage');
    }

    /**
     * Whether the current user must see the custom home, following the plugin settings.
     *
     * @return bool
     */
    public static function is_target_user(): bool {
        global $CFG, $DB, $USER;

        if (get_config('local_adminhome', 'siteadmins') && is_siteadmin()) {
            return true;
        }

        $roles = get_config('local_adminhome', 'roles');
        if (empty($roles)) {
            return false;
        }
        $roleids = array_map('intval', explode(',', $roles));

        // The "Authenticated user" role is not stored in role_assignments: every logged-in user has it.
        if (in_array((int) $CFG->defaultuserroleid, $roleids, true)) {
            return true;
        }

        [$insql, $params] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED);
        $params['userid'] = $USER->id;
        return $DB->record_exists_select('role_assignments', "userid = :userid AND roleid $insql", $params);
    }
}
