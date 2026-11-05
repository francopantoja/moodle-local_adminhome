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
 * Plugin settings, in three tabs: general switch, who sees the custom home and what they see.
 *
 * @package     local_adminhome
 * @copyright   2026 by IDEF https://idef21.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new theme_boost_admin_settingspage_tabs('local_adminhome', get_string('pluginname', 'local_adminhome'));
    $ADMIN->add('localplugins', $settings);

    // The tabs and their choices are only built when the settings page itself is shown, not on every admin page.
    if ($ADMIN->fulltree) {
        // Tab 1: switch for the whole plugin logic; when off, nobody is redirected.
        $page = new admin_settingpage('local_adminhome_general', get_string('tab_general', 'local_adminhome'));
        $page->add(new admin_setting_configcheckbox(
            'local_adminhome/enabled',
            get_string('enabled', 'local_adminhome'),
            get_string('enabled_desc', 'local_adminhome'),
            1
        ));
        $settings->add_tab($page);

        // Tab 2: who sees the custom home.
        $page = new admin_settingpage('local_adminhome_who', get_string('whoheading', 'local_adminhome'));
        $page->add(new admin_setting_configcheckbox(
            'local_adminhome/siteadmins',
            get_string('siteadmins', 'local_adminhome'),
            get_string('siteadmins_desc', 'local_adminhome'),
            1
        ));
        $roles = [];
        foreach (role_fix_names(get_all_roles(), null, ROLENAME_ORIGINAL) as $role) {
            if ($role->archetype === 'guest' || $role->archetype === 'frontpage') {
                continue;
            }
            $roles[$role->id] = $role->localname;
        }
        $page->add(new admin_setting_configmultiselect(
            'local_adminhome/roles',
            get_string('roles', 'local_adminhome'),
            get_string('roles_desc', 'local_adminhome'),
            [],
            $roles
        ));
        $page->add(new admin_setting_configcheckbox(
            'local_adminhome/showfrontpage',
            get_string('showfrontpage', 'local_adminhome'),
            get_string('showfrontpage_desc', 'local_adminhome'),
            0
        ));
        $settings->add_tab($page);

        // Tab 3: what the custom home shows. One switch per site summary card (shown to site admins only).
        $page = new admin_settingpage('local_adminhome_what', get_string('whatheading', 'local_adminhome'));
        foreach (['users', 'courses', 'today', 'never'] as $card) {
            $page->add(new admin_setting_configcheckbox(
                'local_adminhome/showcard_' . $card,
                get_string('card_setting', 'local_adminhome', get_string('card_' . $card, 'local_adminhome')),
                get_string('card_' . $card . '_desc', 'local_adminhome'),
                1
            ));
        }
        $page->add(new admin_setting_configmultiselect(
            'local_adminhome/categories',
            get_string('categories', 'local_adminhome'),
            get_string('categories_desc', 'local_adminhome'),
            [],
            core_course_category::make_categories_list()
        ));
        $page->add(new admin_setting_configselect(
            'local_adminhome/expandcategories',
            get_string('expandcategories', 'local_adminhome'),
            get_string('expandcategories_desc', 'local_adminhome'),
            'top',
            [
                'top' => get_string('expandcategories_top', 'local_adminhome'),
                'all' => get_string('expandcategories_all', 'local_adminhome'),
            ]
        ));
        $settings->add_tab($page);

        // Tab 4: quick links for site admins, empty by default. A link is only shown when it has both text and URL.
        $page = new admin_settingpage('local_adminhome_links', get_string('linksheading', 'local_adminhome'));
        $page->add(new admin_setting_heading(
            'local_adminhome/linksinfo',
            '',
            get_string('linksinfo', 'local_adminhome')
        ));
        for ($i = 1; $i <= 3; $i++) {
            $page->add(new admin_setting_configtext(
                'local_adminhome/link' . $i . 'text',
                get_string('link_text', 'local_adminhome', $i),
                get_string('link_text_desc', 'local_adminhome'),
                '',
                PARAM_TEXT
            ));
            // PARAM_URL accepts full addresses and addresses inside the site, like /user/index.php.
            $page->add(new admin_setting_configtext(
                'local_adminhome/link' . $i . 'url',
                get_string('link_url', 'local_adminhome', $i),
                get_string('link_url_desc', 'local_adminhome'),
                '',
                PARAM_URL
            ));
        }
        $settings->add_tab($page);
    }
}
