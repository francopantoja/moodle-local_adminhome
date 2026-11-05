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
 * English language strings for local_adminhome.
 *
 * @package     local_adminhome
 * @copyright   2026 by IDEF https://idef21.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['card_courses'] = 'Courses';
$string['card_courses_desc'] = 'Visible courses.';
$string['card_never'] = 'Never accessed';
$string['card_never_desc'] = 'Accounts that can log in but have never done it.';
$string['card_setting'] = 'Card: {$a}';
$string['card_today'] = 'Online today';
$string['card_today_desc'] = 'Users who have accessed the site since midnight.';
$string['card_users'] = 'Users';
$string['card_users_desc'] = 'Accounts that can log in: not deleted and not suspended.';
$string['categories'] = 'Categories to show';
$string['categories_desc'] = 'Course categories shown on the custom home, each one with its subcategories and courses. If none is selected, all the categories are shown.';
$string['enabled'] = 'Enable Eazy Home';
$string['enabled_desc'] = 'When enabled, the chosen users are taken to the custom home when they open the site frontpage. When disabled, the plugin does nothing and everybody sees the normal frontpage.';
$string['expandcategories'] = 'Subcategories when the page opens';
$string['expandcategories_all'] = 'Expanded: show all the categories';
$string['expandcategories_desc'] = 'Main categories always start expanded. Users can expand or collapse any category with a click.';
$string['expandcategories_top'] = 'Collapsed: show only the main categories';
$string['link_text'] = 'Link {$a}: text';
$string['link_text_desc'] = 'Text of the box, for example "Enrol users". Leave it empty to hide this link.';
$string['link_url'] = 'Link {$a}: URL';
$string['link_url_desc'] = 'Page the box opens: a full address (https://...) or a page of this site (/user/index.php).';
$string['linksheading'] = '🔗 Quick links';
$string['linksinfo'] = 'Up to three shortcuts to the pages you use the most, shown as boxes on the custom home to site administrators only. A link is only shown when it has both text and URL.';
$string['nocourses'] = 'No courses found.';
$string['pluginname'] = 'Eazy Home';
$string['privacy:metadata'] = 'The Eazy Home plugin does not store any personal data.';
$string['roles'] = 'Roles';
$string['roles_desc'] = 'Users with any of these roles, in any context, see the custom home. Choose "Authenticated user" to show it to every logged-in user.';
$string['showfrontpage'] = 'Button to the normal frontpage';
$string['showfrontpage_desc'] = 'Show the button "View normal frontpage" to the users with the roles above, so they can still go to the site frontpage. Site administrators always see it.';
$string['siteadmins'] = 'Site administrators';
$string['siteadmins_desc'] = 'Site administrators always see the custom home, whatever their roles.';
$string['tab_general'] = '⚙️ General';
$string['viewfrontpage'] = 'View normal frontpage';
$string['welcome'] = 'Welcome, {$a}!';
$string['whatheading'] = '📂 What they see';
$string['whoheading'] = '👥 Who sees it';
