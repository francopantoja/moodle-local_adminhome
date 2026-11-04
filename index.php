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
 * Ad-hoc admin home view.
 *
 * @package     local_adminhome
 * @copyright   2026 by IDEF https://idef21.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_adminhome\hook_callbacks;

require_login();

// Only the users chosen in the settings; the rest go to the normal frontpage.
if (!hook_callbacks::is_enabled() || isguestuser() || !hook_callbacks::is_target_user()) {
    redirect(new moodle_url('/', ['noadminhome' => 1]));
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/adminhome/index.php'));
$PAGE->set_pagelayout('standard');
// The plugin is transparent for the user: the page is titled as the site, not as the plugin.
$PAGE->set_title(format_string($SITE->fullname));
$PAGE->set_heading(format_string($SITE->fullname));

echo $OUTPUT->header();

$main = new \local_adminhome\output\main();
echo $OUTPUT->render_from_template('local_adminhome/main', $main->export_for_template($OUTPUT));

echo $OUTPUT->footer();
