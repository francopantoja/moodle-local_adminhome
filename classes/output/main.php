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
 * Renderable/templatable for the ad-hoc admin home.
 *
 * @package     local_adminhome
 * @copyright   2026 by IDEF https://idef21.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_adminhome\output;

use core_course_category;
use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Main view model for the custom home: welcome, site summary cards and courses grouped by category.
 */
class main implements renderable, templatable {
    /**
     * Export the data the Mustache template needs.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $categories = [];

        foreach (self::get_selected_categories() as $category) {
            // Each chosen category with its subcategories, collected recursively.
            $groups = [];
            self::collect_groups($category, 0, $groups);

            // Keep only top categories that have at least one course (anywhere in the tree).
            $hascourses = false;
            foreach ($groups as $group) {
                if (!empty($group['courses'])) {
                    $hascourses = true;
                    break;
                }
            }
            if ($hascourses) {
                $categories[] = ['groups' => $groups];
            }
        }

        // Site summary cards: only for site admins, the rest of the users see just their courses.
        $cards = is_siteadmin() ? self::get_cards() : [];

        global $USER;

        return [
            'iconurl'       => $output->image_url('icon', 'local_adminhome')->out(false),
            'welcome'       => get_string('welcome', 'local_adminhome', $USER->firstname),
            'cards'         => $cards,
            'hascards'      => !empty($cards),
            'showfrontpage' => \local_adminhome\hook_callbacks::can_see_frontpage(),
            'frontpageurl'  => (new moodle_url('/', ['noadminhome' => 1]))->out(false),
            'categories'    => $categories,
            'hascategories' => !empty($categories),
        ];
    }

    /**
     * Site summary cards, each one with icon, label, value and description.
     *
     * @return array
     */
    private static function get_cards(): array {
        global $CFG, $DB;

        $cards = [];

        // Users: real accounts that can log in (not deleted, not suspended, not the guest).
        if (self::card_enabled('users')) {
            $users = $DB->count_records_select(
                'user',
                'deleted = 0 AND suspended = 0 AND id <> :guestid',
                ['guestid' => $CFG->siteguest]
            );
            $cards[] = [
                'icon' => '👥',
                'label' => get_string('card_users', 'local_adminhome'),
                'value' => self::number($users),
                'desc' => get_string('card_users_desc', 'local_adminhome'),
            ];
        }

        // Courses: visible courses, without the site course.
        if (self::card_enabled('courses')) {
            $courses = $DB->count_records_select('course', 'visible = 1 AND id <> :siteid', ['siteid' => SITEID]);
            $cards[] = [
                'icon' => '📚',
                'label' => get_string('card_courses', 'local_adminhome'),
                'value' => self::number($courses),
                'desc' => get_string('card_courses_desc', 'local_adminhome'),
            ];
        }

        // Online today: users whose last access to the site is after midnight (user timezone).
        if (self::card_enabled('today')) {
            $today = $DB->count_records_select(
                'user',
                'deleted = 0 AND suspended = 0 AND id <> :guestid AND lastaccess >= :midnight',
                ['guestid' => $CFG->siteguest, 'midnight' => usergetmidnight(time())]
            );
            $cards[] = [
                'icon' => '🟢',
                'label' => get_string('card_today', 'local_adminhome'),
                'value' => self::number($today),
                'desc' => get_string('card_today_desc', 'local_adminhome'),
            ];
        }

        // Never accessed: accounts that can log in but have never done it.
        if (self::card_enabled('never')) {
            $never = $DB->count_records_select(
                'user',
                'deleted = 0 AND suspended = 0 AND id <> :guestid AND lastaccess = 0',
                ['guestid' => $CFG->siteguest]
            );
            $cards[] = [
                'icon' => '😴',
                'label' => get_string('card_never', 'local_adminhome'),
                'value' => self::number($never),
                'desc' => get_string('card_never_desc', 'local_adminhome'),
            ];
        }

        return $cards;
    }

    /**
     * Whether a summary card is switched on in the settings; a setting never saved counts as on.
     *
     * @param string $card Card name, as in the setting showcard_<name>.
     * @return bool
     */
    private static function card_enabled(string $card): bool {
        return \local_adminhome\hook_callbacks::setting_on('showcard_' . $card);
    }

    /**
     * Format a whole number with the thousands separator of the current language.
     *
     * @param int $value
     * @return string
     */
    private static function number(int $value): string {
        return number_format($value, 0, '', get_string('thousandssep', 'langconfig'));
    }

    /**
     * Categories chosen in the settings that the user can see, or the top ones when none is chosen.
     *
     * A category is skipped when one of its parents is chosen too, because it is already shown inside it.
     *
     * @return core_course_category[]
     */
    private static function get_selected_categories(): array {
        $setting = get_config('local_adminhome', 'categories');
        if (empty($setting)) {
            return core_course_category::top()->get_children();
        }

        $ids = array_map('intval', explode(',', $setting));
        $categories = [];
        foreach ($ids as $id) {
            // Returns null for missing categories and for hidden ones the user cannot see.
            $category = core_course_category::get($id, IGNORE_MISSING);
            if (!$category || array_intersect($category->get_parents(), $ids)) {
                continue;
            }
            $categories[] = $category;
        }
        return $categories;
    }

    /**
     * Recursively collect a category and its descendants as flat groups.
     *
     * @param core_course_category $category
     * @param int $depth
     * @param array $groups passed by reference, appended in tree order.
     */
    private static function collect_groups(core_course_category $category, int $depth, array &$groups): void {
        $courses = [];
        foreach ($category->get_courses() as $course) {
            $courses[] = [
                'name' => format_string($course->get_formatted_name()),
                'url'  => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
            ];
        }

        $groups[] = [
            'name'       => format_string($category->get_formatted_name()),
            'issub'      => $depth > 0,
            // Left indentation in px, 24 per level.
            'indent'     => $depth * 24,
            'courses'    => $courses,
            'hascourses' => !empty($courses),
        ];

        foreach ($category->get_children() as $child) {
            self::collect_groups($child, $depth + 1, $groups);
        }
    }
}
